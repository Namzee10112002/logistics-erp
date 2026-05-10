<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'province' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:depot,port,warehouse,factory,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_name.required' => 'Vui lòng nhập tên địa điểm.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'province.required' => 'Vui lòng chọn/nhập tỉnh thành.',
            'type.required' => 'Vui lòng chọn loại địa điểm.',
        ];
    }
}
