<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer') ? $this->route('customer')->id : null;

        return [
            'customer_code' => ['prohibited'],
            'customer_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['required', 'regex:/^\d{10}$/', 'unique:customers,tax_code,'.$customerId],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'regex:/^0\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', Rule::in(array_keys(LogisticsOptions::customerContactRoles()))],
        ];
    }

    /**
     * Custom messages for validation
     */
    public function messages(): array
    {
        return [
            'customer_code.prohibited' => 'Mã khách hàng được hệ thống tự sinh và không được chỉnh sửa.',
            'customer_name.required' => 'Vui lòng nhập tên khách hàng.',
            'tax_code.required' => 'Vui lòng nhập mã số thuế.',
            'tax_code.regex' => 'Mã số thuế phải gồm đúng 10 chữ số.',
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trên hệ thống.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 số và bắt đầu bằng 0.',
        ];
    }
}
