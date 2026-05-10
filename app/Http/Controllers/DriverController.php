<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverRequest;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(
        protected DriverService $driverService
    ) {}

    public function index(Request $request)
    {
        $drivers = $this->driverService->getAll($request->all());

        return view('drivers.index', compact('drivers'));
    }

    public function store(DriverRequest $request)
    {
        $this->driverService->create($request->validated());

        return redirect()->route('drivers.index')->with('success', 'Thêm tài xế thành công!');
    }

    public function update(DriverRequest $request, Driver $driver)
    {
        $this->driverService->update($driver, $request->validated());

        return redirect()->route('drivers.index')->with('success', 'Cập nhật thông tin tài xế thành công!');
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $this->driverService->delete($driver);

        return redirect()->route('drivers.index')->with('success', 'Xóa tài xế thành công!');
    }
}
