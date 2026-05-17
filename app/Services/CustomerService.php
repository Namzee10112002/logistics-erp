<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    /**
     * Get list of customers with pagination and search
     */
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Customer::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tax_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new customer
     */
    public function create(array $data)
    {
        $data['customer_code'] = $this->generateCustomerCode();

        if (empty($data['company_name'])) {
            $data['company_name'] = $data['customer_name'];
        }

        return Customer::create($data);
    }

    /**
     * Update an existing customer
     */
    public function update(Customer $customer, array $data)
    {
        unset($data['customer_code']);

        if (empty($data['company_name']) && ! empty($data['customer_name'])) {
            $data['company_name'] = $data['customer_name'];
        }
        $customer->update($data);

        return $customer;
    }

    /**
     * Delete a customer (Soft Delete)
     */
    public function delete(Customer $customer)
    {
        return $customer->delete();
    }

    private function generateCustomerCode(): string
    {
        $date = now()->format('ym');
        $prefix = "KH-{$date}-";

        $lastCustomer = Customer::withTrashed()
            ->where('customer_code', 'like', "{$prefix}%")
            ->orderBy('customer_code', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastSequence = (int) substr($lastCustomer->customer_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
