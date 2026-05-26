@extends('layouts.app')

@section('title', 'Chỉnh sửa Chi phí Cố định - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-navy">Chỉnh sửa Chi phí Cố định</h4>
                <p class="text-muted small">Cập nhật thông tin chi phí cố định: <strong>{{ $recurringExpense->expense_code }}</strong></p>
            </div>
            <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-4">
        <form action="{{ route('recurring-expenses.update', $recurringExpense) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Tên chi phí</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $recurringExpense->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Nhóm chi phí</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $recurringExpense->category) }}" placeholder="VD: Văn phòng, kho bãi">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Số tiền</label>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount', $recurringExpense->amount) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Chu kỳ</label>
                    <select name="cycle" class="form-select" required>
                        <option value="monthly" {{ old('cycle', $recurringExpense->cycle) === 'monthly' ? 'selected' : '' }}>Hàng tháng</option>
                        <option value="quarterly" {{ old('cycle', $recurringExpense->cycle) === 'quarterly' ? 'selected' : '' }}>Hàng quý</option>
                        <option value="yearly" {{ old('cycle', $recurringExpense->cycle) === 'yearly' ? 'selected' : '' }}>Hàng năm</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Trạng thái</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $recurringExpense->status) === 'active' ? 'selected' : '' }}>Đang sử dụng</option>
                        <option value="inactive" {{ old('status', $recurringExpense->status) === 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Hiệu lực từ</label>
                    <input type="date" name="effective_from" class="form-control" value="{{ old('effective_from', $recurringExpense->effective_from ? \Carbon\Carbon::parse($recurringExpense->effective_from)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">Hiệu lực đến</label>
                    <input type="date" name="effective_to" class="form-control" value="{{ old('effective_to', $recurringExpense->effective_to ? \Carbon\Carbon::parse($recurringExpense->effective_to)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small text-muted">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3">{{ old('note', $recurringExpense->note) }}</textarea>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" class="btn btn-navy px-4">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
