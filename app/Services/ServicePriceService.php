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
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                    ->orWhere('package_code', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_tax_included']) && $filters['is_tax_included'] !== '') {
            $query->where('is_tax_included', (bool) $filters['is_tax_included']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        $data['package_code'] = $data['package_code'] ?? $this->generatePackageCode();
        $data['is_tax_included'] = (bool) ($data['is_tax_included'] ?? false);

        return ServicePrice::create($data);
    }

    public function update(ServicePrice $servicePrice, array $data)
    {
        $data['is_tax_included'] = (bool) ($data['is_tax_included'] ?? false);
        $servicePrice->update($data);

        return $servicePrice;
    }

    public function delete(ServicePrice $servicePrice)
    {
        return $servicePrice->delete();
    }

    private function generatePackageCode(): string
    {
        $prefix = 'GOI-';

        $lastPrice = ServicePrice::withTrashed()
            ->where('package_code', 'like', "{$prefix}%")
            ->orderBy('package_code', 'desc')
            ->first();

        if ($lastPrice) {
            $lastSequence = (int) substr($lastPrice->package_code, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return $prefix.$newSequence;
    }
}
