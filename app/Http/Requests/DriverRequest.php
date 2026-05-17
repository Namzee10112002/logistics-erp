<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driverId = $this->route('driver') ? $this->route('driver')->id : null;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number,'.$driverId],
            'status' => ['required', 'in:active,inactive'],
            'start_date' => ['nullable', 'date'],
            'rank' => ['nullable', 'string', 'max:100'],
            'contract_expiry' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên tài xế.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'license_number.required' => 'Vui lòng nhập số bằng lái.',
            'license_number.unique' => 'Số bằng lái này đã tồn tại.',
        ];
    }
}
