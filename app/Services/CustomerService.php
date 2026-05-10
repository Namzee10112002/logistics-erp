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
}
