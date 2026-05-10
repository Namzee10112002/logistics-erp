<?php

namespace App\Services;

use App\Models\Driver;

class DriverService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Driver::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        return Driver::create($data);
    }

    public function update(Driver $driver, array $data)
    {
        $driver->update($data);

        return $driver;
    }

    public function delete(Driver $driver)
    {
        return $driver->delete();
    }
}
