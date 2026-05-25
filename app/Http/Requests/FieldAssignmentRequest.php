<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldAssignmentRequest extends FormRequest
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
            'field_staff_id' => ['required', 'exists:field_staff,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'assigned_date' => ['required', 'date'],
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*' => ['required', Rule::in(array_keys(LogisticsOptions::fieldAssignmentTasks()))],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
