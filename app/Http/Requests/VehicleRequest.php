<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle') ? $this->route('vehicle')->id : null;

        return [
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number,'.$vehicleId],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'payload' => ['required', 'numeric', 'min:0'],
            'registration_expiry' => ['nullable', 'date'],
            'status' => ['required', 'in:available,busy,maintenance'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.required' => 'Vui lòng nhập biển số xe.',
            'plate_number.unique' => 'Biển số xe này đã tồn tại.',
            'vehicle_type.required' => 'Vui lòng nhập loại xe (vd: 5 tấn, 10 tấn, Container...).',
            'payload.required' => 'Vui lòng nhập tải trọng.',
            'payload.numeric' => 'Tải trọng phải là một con số.',
        ];
    }
}
