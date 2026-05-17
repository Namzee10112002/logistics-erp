<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingJobRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Services\ShippingJobService;
use Illuminate\Http\Request;

class ShippingJobController extends Controller
{
    public function __construct(
        protected ShippingJobService $shippingJobService
    ) {}

    public function index(Request $request)
    {
        $shippingJobs = $this->shippingJobService->getAll($request->all());
        $customers = Customer::orderBy('customer_name')->get();

        return view('shipping_jobs.index', compact('shippingJobs', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('customer_name')->get();
        $locations = Location::orderBy('location_name')->get();

        return view('shipping_jobs.create', compact('customers', 'locations'));
    }

    public function store(ShippingJobRequest $request)
    {
        $this->shippingJobService->create($request->validated());

        return redirect()->route('shipping-jobs.index')->with('success', 'Tạo đơn hàng thành công!');
    }

    public function show(ShippingJob $shippingJob)
    {
        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            $driverId = $driver ? $driver->id : 0;
            $hasAccess = $shippingJob->dispatchOrders()->where('driver_id', $driverId)->exists();
            if (! $hasAccess) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
        }

        $shippingJob->load([
            'customer',
            'pickupLocation',
            'deliveryLocation',
            'creator',
            'documents.uploader',
            'expenses.reporter',
            'cashAdvances.requester',
            'debitNote.payments',
            'dispatchOrders.driver',
            'dispatchOrders.vehicle',
            'dispatchOrders.startLocation',
            'dispatchOrders.endLocation',
        ]);

        return view('shipping_jobs.show', compact('shippingJob'));
    }

    public function edit(ShippingJob $shippingJob)
    {
        $customers = Customer::orderBy('customer_name')->get();
        $locations = Location::orderBy('location_name')->get();

        return view('shipping_jobs.edit', compact('shippingJob', 'customers', 'locations'));
    }

    public function update(ShippingJobRequest $request, ShippingJob $shippingJob)
    {
        $this->shippingJobService->update($shippingJob, $request->validated());

        return redirect()->route('shipping-jobs.index')->with('success', 'Cập nhật đơn hàng thành công!');
    }

    public function destroy(ShippingJob $shippingJob)
    {
        $this->shippingJobService->delete($shippingJob);

        return redirect()->route('shipping-jobs.index')->with('success', 'Xóa đơn hàng thành công!');
    }
}
