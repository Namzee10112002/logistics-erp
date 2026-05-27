@extends('layouts.app')

@section('title', 'Thêm Khách Hàng Mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('customers.index') }}" class="text-navy text-decoration-none small fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h4 class="fw-bold mt-2">Thêm Khách Hàng Mới</h4>
</div>

<div class="card border-0 rounded-4 shadow-sm p-4">
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Tên Khách Hàng <span class="text-danger">*</span></label>
                <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" data-validate="person-name" data-label="Tên khách hàng" required>
                @error('customer_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Tên Công Ty (nếu có)</label>
                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}">
                @error('company_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Mã Số Thuế <span class="text-danger">*</span></label>
                <input type="text" name="tax_code" class="form-control @error('tax_code') is-invalid @enderror" value="{{ old('tax_code') }}" maxlength="10" inputmode="numeric" data-validate="tax-code" data-label="Mã số thuế" required>
                @error('tax_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-12">
                <label class="form-label fw-bold">Địa chỉ Trụ sở <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" required>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Số điện thoại liên hệ</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" maxlength="10" inputmode="numeric" data-validate="phone-vn" data-label="Số điện thoại">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Người liên hệ</label>
                <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}" placeholder="VD: Nguyễn Văn A">
                @error('contact_person')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">LƯU THÔNG TIN</button>
                <a href="{{ route('customers.index') }}" class="btn btn-light px-5 py-2 fw-bold ms-2">HỦY</a>
            </div>
        </div>
    </form>
</div>
@endsection
