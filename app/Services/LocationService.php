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

        foreach (['location_code', 'location_name', 'address', 'province', 'status'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        $data['location_code'] = $this->generateLocationCode($data['type']);

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

    private function generateLocationCode(string $type): string
    {
        $prefix = match ($type) {
            'port' => 'PORT',
            'depot' => 'DEPOT',
            'warehouse' => 'WH',
            'factory' => 'FACTORY',
            default => 'LOC',
        };

        $lastLocation = Location::withTrashed()
            ->where('location_code', 'like', "{$prefix}-%")
            ->orderBy('location_code', 'desc')
            ->first();

        $lastSequence = $lastLocation ? (int) substr($lastLocation->location_code, -3) : 0;

        return $prefix.'-'.str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
    }
}
