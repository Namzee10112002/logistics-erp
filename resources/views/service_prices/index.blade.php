@extends('layouts.app')

@section('title', 'Quản lý Biểu giá Dịch vụ')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Biểu giá Dịch vụ</h4>
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#priceModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM BIỂU GIÁ
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Tên Dịch Vụ</th>
                    <th>Đơn Vị Tính</th>
                    <th>Đơn Giá (VNĐ)</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicePrices as $price)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $price->service_name }}</td>
                        <td>{{ $price->unit }}</td>
                        <td class="fw-bold text-success">{{ number_format($price->unit_price) }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($price) }})"
                                data-bs-toggle="modal" data-bs-target="#priceModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $price->id }}', 'Xóa biểu giá {{ $price->service_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $price->id }}" action="{{ route('service-prices.destroy', $price->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Chưa có dữ liệu biểu giá.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $servicePrices->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Price Modal -->
<div class="modal fade" id="priceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="priceForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Biểu Giá Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tên Dịch Vụ</label>
                            <input type="text" name="service_name" id="service_name" class="form-control bg-light border-0" placeholder="VD: Vận chuyển Cont 20'..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn Vị Tính</label>
                            <input type="text" name="unit" id="unit" class="form-control bg-light border-0" placeholder="Chuyến, Cont, KG..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn Giá (VNĐ)</label>
                            <input type="number" name="unit_price" id="unit_price" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">Lưu Thông Tin</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function prepareAdd() {
        document.getElementById('modalTitle').innerText = 'Thêm Biểu Giá Mới';
        document.getElementById('priceForm').action = "{{ route('service-prices.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('priceForm').reset();
    }

    function prepareEdit(price) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Biểu Giá';
        document.getElementById('priceForm').action = `/service-prices/${price.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('service_name').value = price.service_name;
        document.getElementById('unit').value = price.unit;
        document.getElementById('unit_price').value = price.unit_price;
    }
</script>
@endpush
@endsection
