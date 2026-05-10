@extends('layouts.app')

@section('title', 'Quản lý Tài xế')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Đội ngũ Tài xế</h4>
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#driverModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM TÀI XẾ
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
                    <th class="ps-4">Họ và Tên</th>
                    <th>Số Điện Thoại</th>
                    <th>Số Bằng Lái</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $driver->full_name }}</td>
                        <td>{{ $driver->phone }}</td>
                        <td><code>{{ $driver->license_number }}</code></td>
                        <td>
                            <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $driver->status === 'active' ? 'Đang làm việc' : 'Nghỉ việc' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($driver) }})"
                                data-bs-toggle="modal" data-bs-target="#driverModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $driver->id }}', 'Xóa tài xế {{ $driver->full_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $driver->id }}" action="{{ route('drivers.destroy', $driver->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Chưa có dữ liệu tài xế.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $drivers->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="driverForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Tài Xế Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Họ và Tên</label>
                            <input type="text" name="full_name" id="full_name" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Số Điện Thoại</label>
                            <input type="text" name="phone" id="phone" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Số Bằng Lái</label>
                            <input type="text" name="license_number" id="license_number" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Trạng Thái</label>
                            <select name="status" id="status" class="form-select bg-light border-0" required>
                                <option value="active">Đang làm việc</option>
                                <option value="inactive">Nghỉ việc</option>
                            </select>
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
        document.getElementById('modalTitle').innerText = 'Thêm Tài Xế Mới';
        document.getElementById('driverForm').action = "{{ route('drivers.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('driverForm').reset();
    }

    function prepareEdit(driver) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Thông Tin Tài Xế';
        document.getElementById('driverForm').action = `/drivers/${driver.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('full_name').value = driver.full_name;
        document.getElementById('phone').value = driver.phone;
        document.getElementById('license_number').value = driver.license_number;
        document.getElementById('status').value = driver.status;
    }
</script>
@endpush
@endsection
