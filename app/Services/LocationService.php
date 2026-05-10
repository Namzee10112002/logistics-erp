<?php

namespace App\Services;

use App\Models\Location;

class LocationService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Location::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('location_name', 'like', "%{$search}%")
                    ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        return Location::create($data);
    }

    public function update(Location $location, array $data)
    {
        $location->update($data);

        return $location;
    }

    public function delete(Location $location)
    {
        return $location->delete();
    }
}
