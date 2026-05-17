<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fieldStaff = $this->route('field_staff');
        $fieldStaffId = $fieldStaff ? $fieldStaff->id : null;

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('roles')
                            ->where('role_code', 'FIELD');
                    });
                }),
                Rule::unique('field_staff', 'user_id')->ignore($fieldStaffId),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'certificates' => ['nullable', 'string', 'max:1000'],
            'responsible_location_id' => [
                'required',
                Rule::exists('locations', 'id')->where(fn ($query) => $query->whereIn('type', ['depot', 'warehouse'])),
            ],
            'start_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Tài khoản liên kết phải thuộc vai trò Nhân viên hiện trường.',
            'user_id.unique' => 'Tài khoản này đã được liên kết với một nhân viên hiện trường khác.',
            'full_name.required' => 'Vui lòng nhập họ tên nhân viên hiện trường.',
            'responsible_location_id.required' => 'Vui lòng chọn khu vực phụ trách.',
            'responsible_location_id.exists' => 'Khu vực phụ trách phải là kho hoặc bãi hợp lệ.',
        ];
    }
}
