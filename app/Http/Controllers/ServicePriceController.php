<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServicePriceRequest;
use App\Models\ServicePrice;
use App\Services\ServicePriceService;
use Illuminate\Http\Request;

class ServicePriceController extends Controller
{
    public function __construct(
        protected ServicePriceService $servicePriceService
    ) {}

    public function index(Request $request)
    {
        $servicePrices = $this->servicePriceService->getAll($request->all());

        return view('service_prices.index', compact('servicePrices'));
    }

    public function store(ServicePriceRequest $request)
    {
        $this->servicePriceService->create($request->validated());

        return redirect()->route('service-prices.index')->with('success', 'Thêm biểu giá thành công!');
    }

    public function update(ServicePriceRequest $request, ServicePrice $servicePrice)
    {
        $this->servicePriceService->update($servicePrice, $request->validated());

        return redirect()->route('service-prices.index')->with('success', 'Cập nhật biểu giá thành công!');
    }

    public function destroy($id)
    {
        $servicePrice = ServicePrice::findOrFail($id);
        $this->servicePriceService->delete($servicePrice);

        return redirect()->route('service-prices.index')->with('success', 'Xóa biểu giá thành công!');
    }
}
