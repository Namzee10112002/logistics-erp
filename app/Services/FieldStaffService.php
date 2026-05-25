<?php

namespace App\Services;

use App\Models\FieldStaff;

class FieldStaffService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = FieldStaff::with(['user', 'responsibleLocation']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('staff_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('certificates', 'like', "%{$search}%")
                    ->orWhereHas('responsibleLocation', function ($sub) use ($search) {
                        $sub->where('location_name', 'like', "%{$search}%")
                            ->orWhere('province', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['responsible_location_id'])) {
            $query->where('responsible_location_id', $filters['responsible_location_id']);
        }

        foreach (['staff_code', 'full_name', 'phone', 'certificates'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): FieldStaff
    {
        $data['staff_code'] = $this->generateStaffCode();

        return FieldStaff::create($data);
    }

    public function update(FieldStaff $fieldStaff, array $data): FieldStaff
    {
        $fieldStaff->update($data);

        return $fieldStaff;
    }

    public function delete(FieldStaff $fieldStaff): bool
    {
        return $fieldStaff->delete();
    }

    private function generateStaffCode(): string
    {
        $date = now()->format('ym');
        $prefix = "HT-{$date}-";

        $lastStaff = FieldStaff::withTrashed()
            ->where('staff_code', 'like', "{$prefix}%")
            ->orderBy('staff_code', 'desc')
            ->first();

        if ($lastStaff) {
            $lastSequence = (int) substr($lastStaff->staff_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
