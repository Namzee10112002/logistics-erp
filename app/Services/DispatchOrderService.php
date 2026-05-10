<?php

namespace App\Services;

use App\Models\DispatchOrder;
use App\Models\ShippingJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DispatchOrderService
{
    public function getAll(array $filters = [])
    {
        $query = DispatchOrder::with(['shippingJob.customer', 'vehicle', 'driver', 'creator'])
            ->latest();

        if (Auth::user()->hasRole('DRIVER')) {
            $driver = Auth::user()->driver;
            $query->where('driver_id', $driver ? $driver->id : 0);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('shippingJob', function ($sub) use ($search) {
                        $sub->where('job_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($sub) use ($search) {
                        $sub->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($search) {
                        $sub->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(10);
    }

    public function create(array $data): DispatchOrder
    {
        return DB::transaction(function () use ($data) {
            $data['order_number'] = $this->generateOrderNumber();
            $data['created_by'] = Auth::id();
            $data['dispatch_status'] = 'dispatched';
            $data['start_time'] = now();

            $dispatchOrder = DispatchOrder::create($data);

            // Create initial tracking log
            $dispatchOrder->trackingLogs()->create([
                'status_update' => 'dispatched',
                'updated_by' => Auth::id(),
            ]);

            // Update Shipping Job status
            $shippingJob = ShippingJob::find($data['shipping_job_id']);
            if ($shippingJob && $shippingJob->status === 'new') {
                $shippingJob->update(['status' => 'dispatched']);
            }

            return $dispatchOrder;
        });
    }

    private function generateOrderNumber(): string
    {
        $date = now()->format('ymd');
        $prefix = "DO-{$date}-";

        $lastOrder = DispatchOrder::withTrashed()
            ->where('order_number', 'like', "{$prefix}%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastSequence = (int) substr($lastOrder->order_number, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
