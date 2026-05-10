@extends('layouts.app')

@section('title', 'Danh mục Địa điểm')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh mục Địa điểm (Kho/Cảng/Bãi)</h4>
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#locationModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM ĐỊA ĐIỂM
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
                    <th class="ps-4">Tên Địa Điểm</th>
                    <th>Loại</th>
                    <th>Địa chỉ</th>
                    <th>Tỉnh thành</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $location)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $location->location_name }}</td>
                        <td>
                            @php
                                $badgeClass = match($location->type) {
                                    'port' => 'bg-primary',
                                    'depot' => 'bg-info text-dark',
                                    'warehouse' => 'bg-success',
                                    'factory' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                                $typeName = match($location->type) {
                                    'port' => 'Cảng',
                                    'depot' => 'Bãi (Depot)',
                                    'warehouse' => 'Kho',
                                    'factory' => 'Nhà máy',
                                    default => 'Khác'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $typeName }}</span>
                        </td>
                        <td class="small">{{ $location->address }}</td>
                        <td>{{ $location->province }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($location) }})"
                                data-bs-toggle="modal" data-bs-target="#locationModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $location->id }}', 'Xóa địa điểm {{ $location->location_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $location->id }}" action="{{ route('locations.destroy', $location->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Chưa có dữ liệu địa điểm.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $locations->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="locationForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Địa Điểm Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Tên Địa Điểm</label>
                            <input type="text" name="location_name" id="location_name" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Loại địa điểm</label>
                            <select name="type" id="type" class="form-select bg-light border-0" required>
                                <option value="port">Cảng (Port)</option>
                                <option value="depot">Bãi (Depot)</option>
                                <option value="warehouse">Kho (Warehouse)</option>
                                <option value="factory">Nhà máy (Factory)</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Địa chỉ chi tiết</label>
                            <input type="text" name="address" id="address" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tỉnh / Thành phố</label>
                            <input type="text" name="province" id="province" class="form-control bg-light border-0" required>
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
        document.getElementById('modalTitle').innerText = 'Thêm Địa Điểm Mới';
        document.getElementById('locationForm').action = "{{ route('locations.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('locationForm').reset();
    }

    function prepareEdit(location) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Địa Điểm';
        document.getElementById('locationForm').action = `/locations/${location.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('location_name').value = location.location_name;
        document.getElementById('type').value = location.type;
        document.getElementById('address').value = location.address;
        document.getElementById('province').value = location.province;
    }
</script>
@endpush
@endsection
