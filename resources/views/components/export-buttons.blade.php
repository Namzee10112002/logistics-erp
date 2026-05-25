<div class="d-flex flex-wrap gap-2">
    @foreach(['xlsx' => 'Excel', 'csv' => 'CSV', 'docx' => 'Word', 'pdf' => 'PDF'] as $format => $label)
        <a href="{{ request()->fullUrlWithQuery(['export' => $format]) }}" class="btn btn-sm btn-outline-navy">
            <i class="fa fa-download me-1"></i> {{ $label }}
        </a>
    @endforeach
</div>
