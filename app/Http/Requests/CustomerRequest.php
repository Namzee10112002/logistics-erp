<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'tax_code' => ['required', 'string', 'max:50', 'unique:customers,tax_code,'.$customerId],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
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
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trên hệ thống.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
        ];
    }
}
