<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExportService
{
    public const COMPANY_NAME = 'Công Ty TNHH TMDV GNVT NGUYÊN TÂM';

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function download(string $format, string $title, string $periodLabel, array $headers, array $rows): StreamedResponse
    {
        $normalizedFormat = $this->normalizeFormat($format);
        $filename = Str::slug(Str::ascii($title)).'-'.now()->format('Ymd-His').'.'.$normalizedFormat;

        return match ($normalizedFormat) {
            'csv' => $this->downloadCsv($filename, $title, $periodLabel, $headers, $rows),
            'xlsx' => $this->downloadBinary($filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $this->buildXlsx($title, $periodLabel, $headers, $rows)),
            'docx' => $this->downloadBinary($filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', $this->buildDocx($title, $periodLabel, $headers, $rows)),
            'pdf' => $this->downloadBinary($filename, 'application/pdf', $this->buildPdf($title, $periodLabel, $headers, $rows)),
        };
    }

    private function normalizeFormat(string $format): string
    {
        return match (strtolower($format)) {
            'excel', 'xls', 'xlsx' => 'xlsx',
            'word', 'doc', 'docx' => 'docx',
            'pdf' => 'pdf',
            default => 'csv',
        };
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function downloadCsv(string $filename, string $title, string $periodLabel, array $headers, array $rows): StreamedResponse
    {
        return Response::streamDownload(function () use ($title, $periodLabel, $headers, $rows): void {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [self::COMPANY_NAME, $title]);
            fputcsv($file, ['CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM']);
            fputcsv($file, ['Độc lập - Tự do - Hạnh phúc']);
            fputcsv($file, ['Kỳ báo cáo', $periodLabel]);
            fputcsv($file, ['Người xuất báo cáo', auth()->user()?->name ?? 'Hệ thống']);
            fputcsv($file, []);
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function downloadBinary(string $filename, string $contentType, string $content): StreamedResponse
    {
        return Response::streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, ['Content-Type' => $contentType]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildXlsx(string $title, string $periodLabel, array $headers, array $rows): string
    {
        $sheetRows = [
            [self::COMPANY_NAME, $title],
            ['CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['Độc lập - Tự do - Hạnh phúc'],
            ['Kỳ báo cáo', $periodLabel],
            ['Người xuất báo cáo', auth()->user()?->name ?? 'Hệ thống'],
            [],
            $headers,
            ...$rows,
        ];

        $sheetData = collect($sheetRows)
            ->values()
            ->map(function (array $row, int $rowIndex): string {
                $cells = collect($row)
                    ->values()
                    ->map(fn ($value, int $columnIndex): string => sprintf(
                        '<c r="%s%d" t="inlineStr"><is><t>%s</t></is></c>',
                        $this->columnName($columnIndex + 1),
                        $rowIndex + 1,
                        $this->xml((string) $value)
                    ))
                    ->implode('');

                return sprintf('<row r="%d">%s</row>', $rowIndex + 1, $cells);
            })
            ->implode('');

        return $this->zip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Bao cao" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetData.'</sheetData></worksheet>',
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildDocx(string $title, string $periodLabel, array $headers, array $rows): string
    {
        $tableRows = $this->docxRow($headers, true);

        foreach ($rows as $row) {
            $tableRows .= $this->docxRow($row);
        }

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'
            .'<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/></w:tblPr>'
            .$this->docxRow(['LOGO / '.self::COMPANY_NAME, Str::upper($title)], true)
            .$this->docxRow(['', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'], true)
            .$this->docxRow(['', 'Độc lập - Tự do - Hạnh phúc'])
            .'</w:tbl>'
            .$this->docxParagraph(Str::upper($title), true)
            .$this->docxParagraph('Kỳ báo cáo: '.$periodLabel, false)
            .'<w:tbl><w:tblPr><w:tblBorders><w:top w:val="single" w:sz="6"/><w:left w:val="single" w:sz="6"/><w:bottom w:val="single" w:sz="6"/><w:right w:val="single" w:sz="6"/><w:insideH w:val="single" w:sz="6"/><w:insideV w:val="single" w:sz="6"/></w:tblBorders></w:tblPr>'.$tableRows.'</w:tbl>'
            .$this->docxParagraph('Người xuất báo cáo: '.(auth()->user()?->name ?? 'Hệ thống'), true)
            .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
            .'</w:body></w:document>';

        return $this->zip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>',
            'word/document.xml' => $document,
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildPdf(string $title, string $periodLabel, array $headers, array $rows): string
    {
        $lines = [
            'LOGO / '.Str::ascii(self::COMPANY_NAME).'                         '.Str::ascii(Str::upper($title)),
            'CONG HOA XA HOI CHU NGHIA VIET NAM',
            'Doc lap - Tu do - Hanh phuc',
            'Ky bao cao: '.Str::ascii($periodLabel),
            'Nguoi xuat bao cao: '.Str::ascii(auth()->user()?->name ?? 'He thong'),
            '',
            implode(' | ', array_map(fn ($header): string => Str::ascii((string) $header), $headers)),
        ];

        foreach (array_slice($rows, 0, 40) as $row) {
            $lines[] = implode(' | ', array_map(fn ($cell): string => Str::limit(Str::ascii((string) $cell), 30, ''), $row));
        }

        $objects = [];
        $content = "BT\n/F1 10 Tf\n50 800 Td\n";

        foreach ($lines as $index => $line) {
            $content .= ($index === 0 ? '' : "0 -16 Td\n").'('.$this->pdf($line).") Tj\n";
        }

        $content .= 'ET';
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    /**
     * @param  array<string, string>  $files
     */
    private function zip(array $files): string
    {
        $path = tempnam(sys_get_temp_dir(), 'export-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();
        $content = file_get_contents($path);
        @unlink($path);

        return (string) $content;
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)).$name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private function docxParagraph(string $text, bool $bold): string
    {
        return '<w:p><w:r>'.($bold ? '<w:rPr><w:b/></w:rPr>' : '').'<w:t>'.$this->xml($text).'</w:t></w:r></w:p>';
    }

    /**
     * @param  list<string|int|float|null>  $row
     */
    private function docxRow(array $row, bool $bold = false): string
    {
        $cells = collect($row)
            ->map(fn ($cell): string => '<w:tc><w:p><w:r>'.($bold ? '<w:rPr><w:b/></w:rPr>' : '').'<w:t>'.$this->xml((string) $cell).'</w:t></w:r></w:p></w:tc>')
            ->implode('');

        return '<w:tr>'.$cells.'</w:tr>';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function pdf(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
