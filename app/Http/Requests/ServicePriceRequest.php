<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'unit.required' => 'Vui lòng nhập đơn vị tính (vd: Chuyến, Cont, KG...).',
            'unit_price.required' => 'Vui lòng nhập đơn giá.',
            'unit_price.numeric' => 'Đơn giá phải là một con số.',
        ];
    }
}
