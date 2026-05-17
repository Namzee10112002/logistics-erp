<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispatchOrderRequest;
use App\Models\DispatchOrder;
use App\Models\Driver;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\LogisticsNotification;
use App\Services\DispatchOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DispatchOrderController extends Controller
{
    public function __construct(
        protected DispatchOrderService $dispatchOrderService
    ) {}

    public function index(Request $request)
    {
        $dispatchOrders = $this->dispatchOrderService->getAll($request->all());

        // Lấy danh sách các đơn hàng chưa được điều xe (Pending Jobs)
        $pendingJobs = ShippingJob::whereDoesntHave('dispatchOrders')
            ->with(['customer', 'pickupLocation', 'deliveryLocation'])
            ->latest()
            ->get();

        return view('dispatch_orders.index', compact('dispatchOrders', 'pendingJobs'));
    }

    public function create(Request $request)
    {
        $shippingJobId = $request->query('shipping_job_id');
        $shippingJob = null;

        if ($shippingJobId) {
            $shippingJob = ShippingJob::with(['customer', 'pickupLocation', 'deliveryLocation'])->findOrFail($shippingJobId);
        }

        $vehicles = Vehicle::orderBy('plate_number')->get();
        $drivers = Driver::orderBy('full_name')->get();
        $locations = Location::orderBy('location_name')->get();

        return view('dispatch_orders.create', compact('shippingJob', 'vehicles', 'drivers', 'locations'));
    }

    public function store(DispatchOrderRequest $request)
    {
        $this->dispatchOrderService->create($request->validated());

        return redirect()->route('shipping-jobs.show', $request->shipping_job_id)
            ->with('success', 'Lập lệnh điều xe thành công!');
    }

    public function show(DispatchOrder $dispatchOrder)
    {
        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            if (! $driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền truy cập lệnh điều xe này.');
            }
        }

        $dispatchOrder->load([
            'shippingJob.customer',
            'shippingJob.pickupLocation',
            'shippingJob.deliveryLocation',
            'vehicle',
            'driver',
            'startLocation',
            'endLocation',
            'creator',
        ]);

        return view('dispatch_orders.show', compact('dispatchOrder'));
    }

    public function updateStatus(Request $request, DispatchOrder $dispatchOrder)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:dispatched,on_way,completed'],
            'loading_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'current_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'current_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            if (! $driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền cập nhật lệnh điều xe này.');
            }
        }

        $status = $validated['status'] ?? $dispatchOrder->dispatch_status;
        $loadingPercent = $validated['loading_percent'] ?? null;

        if ($loadingPercent === 100) {
            $status = 'completed';
        }

        $updateData = ['dispatch_status' => $status];

        if ($loadingPercent !== null) {
            $updateData['loading_percent'] = $loadingPercent;
        }

        if (array_key_exists('current_latitude', $validated)) {
            $updateData['current_latitude'] = $validated['current_latitude'];
        }

        if (array_key_exists('current_longitude', $validated)) {
            $updateData['current_longitude'] = $validated['current_longitude'];
        }

        if ($status === 'on_way') {
            $updateData['start_time'] = $dispatchOrder->start_time ?? now();
        } elseif ($status === 'completed') {
            $updateData['end_time'] = $dispatchOrder->end_time ?? now();
            $updateData['loading_percent'] = 100;

            // Cập nhật trạng thái Shipping Job nếu cần
            $dispatchOrder->shippingJob->update(['status' => 'completed']);
        }

        $dispatchOrder->update($updateData);

        // Tạo Nhật ký hành trình
        $dispatchOrder->trackingLogs()->create([
            'status_update' => $status,
            'updated_by' => auth()->id(),
        ]);

        // Notify Admins and Dispatchers
        $statusLabel = match ($status) {
            'on_way' => 'Bắt đầu khởi hành',
            'completed' => 'Đã hoàn thành chuyến xe',
            default => $status
        };

        $notifiableUsers = User::whereHas('role', function ($q) {
            $q->whereIn('role_code', ['ADMIN', 'DISPATCH']);
        })->get();

        $driverName = auth()->user()->name;
        Notification::send($notifiableUsers, new LogisticsNotification(
            'Cập nhật hành trình',
            "Tài xế {$driverName} đã cập nhật trạng thái: {$statusLabel} cho lệnh {$dispatchOrder->order_number}",
            'fa-truck-moving',
            route('dispatch-orders.show', $dispatchOrder)
        ));

        return back()->with('success', 'Đã cập nhật trạng thái chuyến đi thành công!');
    }

    public function destroy(DispatchOrder $dispatchOrder)
    {
        $jobId = $dispatchOrder->shipping_job_id;
        $dispatchOrder->delete();

        return redirect()->route('shipping-jobs.show', $jobId)
            ->with('success', 'Xóa lệnh điều xe thành công!');
    }
}
