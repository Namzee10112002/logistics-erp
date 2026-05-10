<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispatchOrderRequest;
use App\Models\DispatchOrder;
use App\Models\Driver;
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

        return view('dispatch_orders.create', compact('shippingJob', 'vehicles', 'drivers'));
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
            if (!$driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền truy cập lệnh điều xe này.');
            }
        }

        $dispatchOrder->load(['shippingJob.customer', 'vehicle', 'driver', 'creator']);

        return view('dispatch_orders.show', compact('dispatchOrder'));
    }

    public function updateStatus(Request $request, DispatchOrder $dispatchOrder)
    {
        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            if (!$driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền cập nhật lệnh điều xe này.');
            }
        }

        $status = $request->status;
        $updateData = ['dispatch_status' => $status];

        if ($status === 'on_way') {
            $updateData['start_time'] = now();
        } elseif ($status === 'completed') {
            $updateData['end_time'] = now();

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
