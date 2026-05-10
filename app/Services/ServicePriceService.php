<?php

namespace App\Services;

use App\Models\ServicePrice;

class ServicePriceService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = ServicePrice::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('service_name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        return ServicePrice::create($data);
    }

    public function update(ServicePrice $servicePrice, array $data)
    {
        $servicePrice->update($data);

        return $servicePrice;
    }

    public function delete(ServicePrice $servicePrice)
    {
        return $servicePrice->delete();
    }
}
