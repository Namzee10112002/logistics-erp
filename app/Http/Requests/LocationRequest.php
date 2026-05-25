<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'province' => ['required', Rule::in(array_keys(LogisticsOptions::provincesNearHaiPhong()))],
            'type' => ['required', 'in:depot,port,warehouse,factory,other'],
            'status' => ['required', 'in:active,inactive,maintenance,overloaded'],
            'note' => ['nullable', 'string', 'max:1000'],
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
