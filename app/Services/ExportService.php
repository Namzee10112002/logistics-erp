<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class ExportService
{
    public const COMPANY_NAME = 'Công Ty TNHH TMDV GNVT NGUYÊN TÂM';

    private const LOGO_PATH = 'img/company-logo.jpg';

    private const DOCX_CONTENT_WIDTH = 15038;

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     * @param  array<string, mixed>  $options
     */
    public function download(string $format, string $title, string $periodLabel, array $headers, array $rows, array $options = []): StreamedResponse
    {
        $normalizedFormat = $this->normalizeFormat($format);
        $filename = Str::slug(Str::ascii($title)).'-'.now()->format('Ymd-His').'.'.$normalizedFormat;
        $context = $this->exportContext($normalizedFormat, $title, $periodLabel, $rows, $options);

        return match ($normalizedFormat) {
            'csv' => $this->downloadCsv($filename, $context, $headers, $rows),
            'xlsx' => $this->downloadBinary($filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $this->buildXlsx($context, $headers, $rows)),
            'docx' => $this->downloadBinary($filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', $this->buildDocx($context, $headers, $rows)),
            'pdf' => $this->downloadBinary($filename, 'application/pdf', $this->buildPdf($context, $headers, $rows)),
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
     * @param  list<list<string|int|float|null>>  $rows
     * @param  array<string, mixed>  $options
     * @return array<string, string|int>
     */
    private function exportContext(string $format, string $title, string $periodLabel, array $rows, array $options): array
    {
        $companyName = $this->companyName();
        $contactLine = $this->companyContactLine();
        $exporter = auth()->user()?->name ?? 'Hệ thống';
        $content = (string) ($options['content'] ?? $this->contentLine($title, $periodLabel, count($rows)));

        return [
            'format' => $format,
            'title' => $title,
            'title_upper' => Str::upper($title),
            'period' => $periodLabel,
            'content' => $content,
            'company_name' => $companyName,
            'contact_line' => $contactLine,
            'exporter' => $exporter,
            'issued_place_date' => 'Hải Phòng, ngày '.now()->format('d').' tháng '.now()->format('m').' năm '.now()->format('Y'),
            'footer' => 'Nhân viên đang đăng nhập: '.$exporter,
            'row_count' => count($rows),
        ];
    }

    private function companyName(): string
    {
        $configuredName = $this->setting('company.name', self::COMPANY_NAME);

        if (Str::contains(Str::lower($configuredName), 'example logistics')) {
            return self::COMPANY_NAME;
        }

        return $configuredName;
    }

    private function companyContactLine(): string
    {
        $parts = array_filter([
            $this->setting('company.address', 'Hải Phòng'),
            'SĐT: '.$this->setting('company.phone', '0900000000'),
            'Email: '.$this->setting('company.email', 'contact@nguyentam-logistics.example'),
            'Liên hệ: '.$this->setting('company.contact_person', 'Bộ phận điều vận'),
        ]);

        return implode(' | ', $parts);
    }

    private function setting(string $key, string $default): string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            $value = Setting::query()->where('key', $key)->value('value');

            return filled($value) ? (string) $value : $default;
        } catch (Throwable) {
            return $default;
        }
    }

    private function contentLine(string $title, string $periodLabel, int $rowCount): string
    {
        return "Nội dung báo cáo: {$title} - {$periodLabel} - {$rowCount} dòng dữ liệu.";
    }

    /**
     * @param  array<string, string|int>  $context
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function downloadCsv(string $filename, array $context, array $headers, array $rows): StreamedResponse
    {
        return Response::streamDownload(function () use ($context, $headers, $rows): void {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Công ty', $context['company_name']]);
            fputcsv($file, ['Liên hệ', $context['contact_line']]);
            fputcsv($file, ['Báo cáo', $context['title']]);
            fputcsv($file, ['Kỳ dữ liệu', $context['period']]);
            fputcsv($file, ['Người xuất', $context['exporter']]);
            fputcsv($file, ['Số dòng', $context['row_count']]);
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
     * @param  array<string, string|int>  $context
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildXlsx(array $context, array $headers, array $rows): string
    {
        $dataColumnCount = max(1, count($headers));
        $layoutColumnCount = max(6, $dataColumnCount);
        $lastColumn = $this->columnName($layoutColumnCount);
        $tableLastColumn = $this->columnName($dataColumnCount);
        $headerRow = 7;
        $dataStartRow = $headerRow + 1;

        $sheetRows = [
            1 => [$context['company_name']],
            2 => [$context['contact_line']],
            3 => [$context['title_upper']],
            4 => [$context['content']],
            5 => ['Kỳ dữ liệu', $context['period'], 'Người xuất', $context['exporter'], 'Số dòng', $context['row_count']],
            6 => [],
            $headerRow => $headers,
        ];

        foreach ($rows as $index => $row) {
            $sheetRows[$dataStartRow + $index] = $row;
        }

        $sheetData = collect($sheetRows)
            ->sortKeys()
            ->map(function (array $row, int $rowIndex) use ($headerRow, $dataStartRow): string {
                $style = match (true) {
                    $rowIndex === 1 => 1,
                    in_array($rowIndex, [2, 3, 4], true) => 2,
                    $rowIndex === 5 => 5,
                    $rowIndex === $headerRow => 3,
                    $rowIndex >= $dataStartRow => 4,
                    default => 0,
                };

                return $this->xlsxRow($rowIndex, $row, $style);
            })
            ->implode('');

        $mergeCells = [
            "A1:{$lastColumn}1",
            "A2:{$lastColumn}2",
            "A3:{$lastColumn}3",
            "A4:{$lastColumn}4",
        ];

        $mergeXml = '<mergeCells count="'.count($mergeCells).'">'.collect($mergeCells)
            ->map(fn (string $ref): string => '<mergeCell ref="'.$ref.'"/>')
            ->implode('').'</mergeCells>';
        $autoFilterRef = 'A'.$headerRow.':'.$tableLastColumn.max($headerRow, $dataStartRow + count($rows) - 1);
        $columnsXml = collect($this->xlsxColumnWidths($headers, $rows, $layoutColumnCount))
            ->map(fn (float $width, int $index): string => '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.$width.'" customWidth="1"/>')
            ->implode('');
        $createdAt = now()->toAtomString();

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';

        return $this->zip([
            '[Content_Types].xml' => $contentTypes,
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdWorkbook" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rIdCore" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rIdApp" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>',
            'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>'.$this->xml((string) $context['title']).'</dc:title><dc:creator>'.$this->xml((string) $context['exporter']).'</dc:creator><cp:lastModifiedBy>'.$this->xml((string) $context['exporter']).'</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:modified></cp:coreProperties>',
            'docProps/app.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Nguyen Tam Logistics</Application></Properties>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Bao cao" sheetId="1" r:id="rIdSheet1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdSheet1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $this->xlsxStylesXml(),
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'.$columnsXml.'</cols><sheetData>'.$sheetData.'</sheetData><autoFilter ref="'.$autoFilterRef.'"/>'.$mergeXml.'</worksheet>',
        ]);
    }

    /**
     * @param  list<string|int|float|null>  $row
     */
    private function xlsxRow(int $rowIndex, array $row, int $style): string
    {
        $cells = collect($row)
            ->values()
            ->map(fn ($value, int $columnIndex): string => sprintf(
                '<c r="%s%d" s="%d" t="inlineStr"><is><t>%s</t></is></c>',
                $this->columnName($columnIndex + 1),
                $rowIndex,
                $style,
                $this->xml((string) $value)
            ))
            ->implode('');

        return sprintf('<row r="%d">%s</row>', $rowIndex, $cells);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     * @return list<float>
     */
    private function xlsxColumnWidths(array $headers, array $rows, int $layoutColumnCount): array
    {
        $widths = [];

        for ($column = 0; $column < $layoutColumnCount; $column++) {
            $maxLength = Str::length((string) ($headers[$column] ?? ''));

            foreach (array_slice($rows, 0, 60) as $row) {
                $maxLength = max($maxLength, Str::length((string) ($row[$column] ?? '')));
            }

            $widths[] = (float) min(42, max(14, $maxLength + 4));
        }

        return $widths;
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="15"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1A237E"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    /**
     * @param  array<string, string|int>  $context
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildDocx(array $context, array $headers, array $rows): string
    {
        $logoBytes = $this->logoBytes();
        $headerColumnWidths = [11200, self::DOCX_CONTENT_WIDTH - 11200];
        $tableColumnWidths = $this->docxColumnWidths($headers, $rows);
        $leftHeader = $this->docxParagraph((string) $context['company_name'], true, 'left', false, 24);
        $rightHeader = '';

        if ($logoBytes !== null) {
            $rightHeader .= $this->docxParagraph($this->docxImageRun(), false, 'right', true);
        } else {
            $rightHeader .= $this->docxParagraph('Logo: '.asset(self::LOGO_PATH), false, 'right');
        }

        $rightHeader .= $this->docxParagraph((string) $context['company_name'], true, 'right', false, 18);

        $tableRows = $this->docxTableRow($headers, true, $tableColumnWidths);

        foreach ($rows as $row) {
            $tableRows .= $this->docxTableRow($row, false, $tableColumnWidths);
        }

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<w:body>'
            .$this->docxTable(
                $this->docxRawRow([
                    $leftHeader,
                    $rightHeader,
                ], $headerColumnWidths),
                false,
                self::DOCX_CONTENT_WIDTH,
                $headerColumnWidths
            )
            .$this->docxParagraph((string) $context['contact_line'], false, 'center', false, 18, '475569')
            .$this->docxParagraph((string) $context['title_upper'], true, 'center', false, 32)
            .$this->docxParagraph((string) $context['content'], true, 'center')
            .$this->docxTable($tableRows, true, self::DOCX_CONTENT_WIDTH, $tableColumnWidths)
            .$this->docxParagraph((string) $context['issued_place_date'], false, 'right')
            .$this->docxParagraph('Nhân viên xuất', true, 'right')
            .$this->docxParagraph((string) $context['footer'], true, 'center')
            .'<w:sectPr><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="900" w:right="900" w:bottom="900" w:left="900"/></w:sectPr>'
            .'</w:body></w:document>';

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="jpg" ContentType="image/jpeg"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdDocument" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>',
            'word/document.xml' => $document,
            'word/_rels/document.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.($logoBytes !== null ? '<Relationship Id="rIdLogo" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.jpg"/>' : '').'</Relationships>',
        ];

        if ($logoBytes !== null) {
            $files['word/media/image1.jpg'] = $logoBytes;
        }

        return $this->zip($files);
    }

    /**
     * @param  array<string, string|int>  $context
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function buildPdf(array $context, array $headers, array $rows): string
    {
        $logoBytes = $this->logoBytes();
        $logoInfo = $this->logoInfo();
        $rowsPerPage = 18;
        $rowChunks = array_chunk($rows, $rowsPerPage);

        if ($rowChunks === []) {
            $rowChunks = [[]];
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '__PAGES__',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $imageObjectId = null;

        if ($logoBytes !== null && $logoInfo !== null) {
            $imageObjectId = 4;
            $objects[$imageObjectId] = '<< /Type /XObject /Subtype /Image /Width '.$logoInfo['width'].' /Height '.$logoInfo['height'].' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($logoBytes)." >>\nstream\n".$logoBytes."\nendstream";
        }

        $pageReferences = [];

        foreach ($rowChunks as $pageIndex => $chunk) {
            $content = $this->pdfPageContent($context, $headers, $chunk, $pageIndex + 1, count($rowChunks), $imageObjectId !== null);
            $contentObjectId = count($objects) + 1;
            $objects[$contentObjectId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
            $pageObjectId = count($objects) + 1;
            $resources = '<< /Font << /F1 3 0 R >>'.($imageObjectId !== null ? ' /XObject << /ImLogo '.$imageObjectId.' 0 R >>' : '').' >>';
            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources '.$resources.' /Contents '.$contentObjectId.' 0 R >>';
            $pageReferences[] = $pageObjectId.' 0 R';
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageReferences).'] /Count '.count($pageReferences).' >>';

        return $this->pdfDocument($objects);
    }

    /**
     * @param  array<string, string|int>  $context
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function pdfPageContent(array $context, array $headers, array $rows, int $page, int $totalPages, bool $hasLogo): string
    {
        $content = '';

        $content .= "0.10 0.14 0.49 rg\n0 535 842 60 re f\n";
        $content .= "0.03 0.64 0.62 rg\n0 535 842 6 re f\n";
        $content .= $this->pdfText(42, 562, $this->pdfLimit($this->ascii((string) $context['company_name']), 340), 10, '1 1 1');
        $content .= $this->pdfText(42, 546, $this->pdfLimit($this->ascii((string) $context['contact_line']), 380), 7, '0.82 0.92 1');

        if ($hasLogo) {
            $content .= "1 1 1 rg\n773 542 48 48 re f\n";
            $content .= "0.82 0.87 0.92 RG\n773 542 48 48 re S\n";
            $content .= "q\n40 0 0 40 777 546 cm\n/ImLogo Do\nQ\n";
        }

        $content .= "0.94 0.98 0.99 rg\n35 503 772 24 re f\n";
        $content .= "0.78 0.88 0.92 RG\n35 503 772 24 re S\n";
        $content .= $this->pdfCenteredText(421, 512, $this->pdfLimit($this->ascii((string) $context['contact_line']), 560), 8, '0.10 0.14 0.24');
        $content .= $this->pdfCenteredText(421, 480, $this->ascii((string) $context['title_upper']), 16, '0.10 0.14 0.49');
        $content .= "0.03 0.64 0.62 rg\n335 469 172 2 re f\n";
        $content .= $this->pdfCenteredText(421, 452, $this->pdfLimit($this->ascii((string) $context['content']), 500), 10, '0.19 0.22 0.30');

        $tableTop = 424;
        $left = 35;
        $width = 772;
        $rowHeight = 18;
        $columnCount = max(1, count($headers));
        $columnWidth = $width / $columnCount;

        $content .= "0.10 0.14 0.49 rg\n";
        $content .= $this->pdfFilledRectangle($left, $tableTop - $rowHeight, $width, $rowHeight);
        $content .= "0.10 0.14 0.49 RG\n";
        $content .= $this->pdfRectangle($left, $tableTop - $rowHeight, $width, $rowHeight);

        foreach ($headers as $index => $header) {
            $x = $left + ($index * $columnWidth) + 3;
            $content .= $this->pdfText($x, $tableTop - 12, $this->pdfLimit($this->ascii((string) $header), $columnWidth), 7, '1 1 1');

            if ($index > 0) {
                $lineX = $left + ($index * $columnWidth);
                $content .= "0.80 0.90 0.94 RG\n{$lineX} ".($tableTop - $rowHeight).' m '.$lineX.' '.$tableTop." l S\n";
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $y = $tableTop - (($rowIndex + 2) * $rowHeight);
            if ($rowIndex % 2 === 0) {
                $content .= "0.97 0.99 1 rg\n";
                $content .= $this->pdfFilledRectangle($left, $y, $width, $rowHeight);
            }

            $content .= "0.82 0.87 0.92 RG\n";
            $content .= $this->pdfRectangle($left, $y, $width, $rowHeight);

            foreach ($headers as $columnIndex => $header) {
                $x = $left + ($columnIndex * $columnWidth) + 3;
                $content .= $this->pdfText($x, $y + 6, $this->pdfLimit($this->ascii((string) ($row[$columnIndex] ?? '')), $columnWidth), 7, '0.10 0.14 0.24');

                if ($columnIndex > 0) {
                    $lineX = $left + ($columnIndex * $columnWidth);
                    $content .= "0.82 0.87 0.92 RG\n{$lineX} {$y} m {$lineX} ".($y + $rowHeight)." l S\n";
                }
            }
        }

        $content .= $this->pdfRightText(803, 75, $this->ascii((string) $context['issued_place_date']), 10, '0.10 0.14 0.24');
        $content .= $this->pdfRightText(803, 55, 'Nhan vien xuat', 10, '0.10 0.14 0.24');
        $content .= "0.10 0.14 0.49 rg\n0 0 842 38 re f\n";
        $content .= $this->pdfCenteredText(421, 16, $this->ascii((string) $context['footer'])." | Trang {$page}/{$totalPages}", 9, '1 1 1');

        return $content;
    }

    /**
     * @param  array<int, string>  $objects
     */
    private function pdfDocument(array $objects): string
    {
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[$index] = strlen($pdf);
            $pdf .= "{$index} 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";

        for ($index = 1; $index < $size; $index++) {
            $offset = $offsets[$index] ?? 0;
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
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

    private function logoBytes(): ?string
    {
        $path = public_path(self::LOGO_PATH);

        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents === false ? null : $contents;
    }

    /**
     * @return array{width:int,height:int}|null
     */
    private function logoInfo(): ?array
    {
        $path = public_path(self::LOGO_PATH);

        if (! is_file($path)) {
            return null;
        }

        $info = getimagesize($path);

        if ($info === false) {
            return null;
        }

        return [
            'width' => (int) $info[0],
            'height' => (int) $info[1],
        ];
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

    private function docxParagraph(string $content, bool $bold, string $align = 'left', bool $raw = false, int $size = 22, ?string $color = null): string
    {
        $text = $raw ? $content : '<w:r><w:rPr>'.($bold ? '<w:b/>' : '').($color ? '<w:color w:val="'.$color.'"/>' : '').'<w:sz w:val="'.$size.'"/></w:rPr><w:t>'.$this->xml($content).'</w:t></w:r>';

        return '<w:p><w:pPr><w:jc w:val="'.$align.'"/></w:pPr>'.$text.'</w:p>';
    }

    /**
     * @param  list<int>|null  $columnWidths
     */
    private function docxTable(string $rows, bool $bordered, int $width = self::DOCX_CONTENT_WIDTH, ?array $columnWidths = null): string
    {
        $borders = $bordered
            ? '<w:tblBorders><w:top w:val="single" w:sz="6"/><w:left w:val="single" w:sz="6"/><w:bottom w:val="single" w:sz="6"/><w:right w:val="single" w:sz="6"/><w:insideH w:val="single" w:sz="6"/><w:insideV w:val="single" w:sz="6"/></w:tblBorders>'
            : '';
        $grid = $columnWidths === null ? '' : '<w:tblGrid>'.collect($columnWidths)
            ->map(fn (int $columnWidth): string => '<w:gridCol w:w="'.$columnWidth.'"/>')
            ->implode('').'</w:tblGrid>';

        return '<w:tbl><w:tblPr><w:tblW w:w="'.$width.'" w:type="dxa"/><w:tblLayout w:type="fixed"/>'.$borders.'<w:tblCellMar><w:top w:w="90" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:bottom w:w="90" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr>'.$grid.$rows.'</w:tbl>';
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     * @return list<int>
     */
    private function docxColumnWidths(array $headers, array $rows): array
    {
        $columnCount = max(1, count($headers));
        $weights = [];

        for ($column = 0; $column < $columnCount; $column++) {
            $maxLength = Str::length((string) ($headers[$column] ?? ''));

            foreach (array_slice($rows, 0, 40) as $row) {
                $maxLength = max($maxLength, Str::length((string) ($row[$column] ?? '')));
            }

            $weights[] = min(36, max(8, $maxLength));
        }

        $totalWeight = max(1, array_sum($weights));
        $minimumWidth = max(450, min(900, intdiv(self::DOCX_CONTENT_WIDTH, max(1, $columnCount * 3))));
        $remainingWidth = self::DOCX_CONTENT_WIDTH;
        $widths = [];

        foreach ($weights as $index => $weight) {
            if ($index === $columnCount - 1) {
                $widths[] = $remainingWidth;

                break;
            }

            $reservedForRemaining = $minimumWidth * ($columnCount - $index - 1);
            $availableForColumn = max($minimumWidth, $remainingWidth - $reservedForRemaining);
            $proportionalWidth = (int) round(self::DOCX_CONTENT_WIDTH * ($weight / $totalWeight));
            $columnWidth = max($minimumWidth, min($availableForColumn, $proportionalWidth));
            $widths[] = $columnWidth;
            $remainingWidth -= $columnWidth;
        }

        return $widths;
    }

    /**
     * @param  list<string|int|float|null>  $row
     * @param  list<int>|null  $columnWidths
     */
    private function docxTableRow(array $row, bool $bold = false, ?array $columnWidths = null): string
    {
        $columnCount = max(1, count($row), count($columnWidths ?? []));
        $values = collect(array_slice(array_pad(array_values($row), $columnCount, ''), 0, $columnCount));

        return '<w:tr>'.$values
            ->map(function ($cell, int $index) use ($bold, $columnWidths, $columnCount): string {
                $width = (int) ($columnWidths[$index] ?? intdiv(self::DOCX_CONTENT_WIDTH, $columnCount));
                $cellProperties = $this->docxCellProperties($width, $bold ? '1A237E' : null);
                $alignment = $bold ? 'center' : $this->docxCellAlignment($cell);
                $paragraph = $this->docxParagraph((string) $cell, $bold, $alignment, false, $bold ? 20 : 18, $bold ? 'FFFFFF' : null);

                return '<w:tc>'.$cellProperties.$paragraph.'</w:tc>';
            })
            ->implode('').'</w:tr>';
    }

    /**
     * @param  list<string>  $cells
     * @param  list<int>  $columnWidths
     */
    private function docxRawRow(array $cells, array $columnWidths): string
    {
        return '<w:tr>'.collect($cells)
            ->map(fn (string $cell, int $index): string => '<w:tc>'.$this->docxCellProperties((int) $columnWidths[$index]).$cell.'</w:tc>')
            ->implode('').'</w:tr>';
    }

    private function docxCellProperties(int $width, ?string $fill = null): string
    {
        return '<w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/><w:vAlign w:val="center"/>'.($fill ? '<w:shd w:fill="'.$fill.'"/>' : '').'</w:tcPr>';
    }

    private function docxCellAlignment(string|int|float|null $cell): string
    {
        $value = trim((string) $cell);

        if ($value === '' || is_numeric(str_replace([',', '.', ' '], '', $value))) {
            return 'center';
        }

        return Str::length($value) <= 16 ? 'center' : 'left';
    }

    private function docxImageRun(): string
    {
        return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="762000" cy="762000"/><wp:docPr id="1" name="Company Logo"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="1" name="Company Logo"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rIdLogo"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
    }

    private function pdfText(float $x, float $y, string $text, int $size, string $color = '0 0 0'): string
    {
        return "{$color} rg\nBT\n/F1 {$size} Tf\n{$x} {$y} Td\n(".$this->pdf($text).") Tj\nET\n";
    }

    private function pdfRightText(float $rightX, float $y, string $text, int $size, string $color = '0 0 0'): string
    {
        $x = max(25, $rightX - (strlen($text) * $size * 0.48));

        return $this->pdfText($x, $y, $text, $size, $color);
    }

    private function pdfCenteredText(float $centerX, float $y, string $text, int $size, string $color = '0 0 0'): string
    {
        $x = max(25, $centerX - ((strlen($text) * $size * 0.25)));

        return $this->pdfText($x, $y, $text, $size, $color);
    }

    private function pdfRectangle(float $x, float $y, float $width, float $height): string
    {
        return "0.3 w\n{$x} {$y} {$width} {$height} re S\n";
    }

    private function pdfFilledRectangle(float $x, float $y, float $width, float $height): string
    {
        return "{$x} {$y} {$width} {$height} re f\n";
    }

    private function pdfLimit(string $text, float $columnWidth): string
    {
        return Str::limit($text, max(6, (int) floor($columnWidth / 4.3)), '');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function pdf(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private function ascii(string $value): string
    {
        return Str::ascii($value);
    }
}
