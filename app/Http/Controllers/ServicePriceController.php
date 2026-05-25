<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServicePriceRequest;
use App\Models\ServicePrice;
use App\Services\ExportService;
use App\Services\ServicePriceService;
use Illuminate\Http\Request;

class ServicePriceController extends Controller
{
    public function __construct(
        protected ServicePriceService $servicePriceService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $servicePrices = $this->servicePriceService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách biểu giá', 'Tất cả dữ liệu đang lọc', [
                'Mã gói', 'Dịch vụ', 'Đơn vị', 'Đơn giá', 'Thuế',
            ], $servicePrices->map(fn (ServicePrice $price): array => [
                $price->package_code,
                $price->service_name,
                $price->unit,
                $price->unit_price,
                $price->is_tax_included ? 'Đã gồm thuế' : 'Chưa gồm thuế',
            ])->all());
        }

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
