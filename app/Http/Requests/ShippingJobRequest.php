<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShippingJobRequest extends FormRequest
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
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'pickup_location_id' => ['required', 'exists:locations,id'],
            'delivery_location_id' => ['required', 'exists:locations,id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'container_type' => ['nullable', 'string', 'max:50'],
            'container_number' => ['nullable', 'string', 'max:50'],
            'customs_declaration_no' => ['nullable', 'string', 'max:50'],
            'expected_date' => ['required', 'date'],
            'status' => ['nullable', 'in:new,processing,dispatched,completed,cancelled'],
        ];
    }
}
