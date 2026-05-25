<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_code' => ['prohibited'],
            'service_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(array_keys(LogisticsOptions::serviceUnits()))],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'is_tax_included' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_name.required' => 'Vui lòng nhập tên dịch vụ.',
            'package_code.prohibited' => 'Mã gói được hệ thống tự sinh và không được chỉnh sửa.',
            'unit.required' => 'Vui lòng nhập đơn vị tính (vd: Chuyến, Cont, KG...).',
            'unit_price.required' => 'Vui lòng nhập đơn giá.',
            'unit_price.numeric' => 'Đơn giá phải là một con số.',
        ];
    }
}
