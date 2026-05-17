<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DispatchOrderRequest extends FormRequest
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
            'shipping_job_id' => ['required', 'exists:shipping_jobs,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'start_location_id' => ['nullable', 'exists:locations,id'],
            'end_location_id' => ['nullable', 'exists:locations,id'],
            'note' => ['nullable', 'string', 'max:500'],
            'fuel_quota' => ['nullable', 'numeric', 'min:0'],
            'toll_quota' => ['nullable', 'numeric', 'min:0'],
            'loading_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'current_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'current_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
