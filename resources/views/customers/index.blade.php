@extends('layouts.app')

@section('title', 'Danh mục Khách hàng')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh mục Khách hàng</h4>
    <a href="{{ route('customers.create') }}" class="btn btn-navy px-4 fw-bold">
        <i class="fa fa-plus me-2"></i> THÊM KHÁCH HÀNG
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('customers.index') }}" method="GET" class="row g-3">
        <div class="col-md-10">
            <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo tên, MST, email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã KH</th>
                    <th>Khách Hàng / Công Ty</th>
                    <th>MST</th>
                    <th>Email</th>
                    <th>Liên Hệ</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $customer->customer_code }}</td>
                        <td>
                            <div class="fw-bold">{{ $customer->customer_name }}</div>
                            <div class="small text-muted">{{ $customer->company_name }}</div>
                        </td>
                        <td><code>{{ $customer->tax_code }}</code></td>
                        <td>{{ $customer->email }}</td>
                        <td>
                            <div class="small">{{ $customer->phone }}</div>
                            <div class="small text-muted">{{ $customer->contact_person }}</div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm text-warning me-2">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $customer->id }}', 'Xóa khách hàng {{ $customer->customer_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Không tìm thấy khách hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="p-4 border-top">
        {{ $customers->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
