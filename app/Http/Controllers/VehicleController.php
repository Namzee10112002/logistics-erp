<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    public function index(Request $request)
    {
        $vehicles = $this->vehicleService->getAll($request->all());

        return view('vehicles.index', compact('vehicles'));
    }

    public function store(VehicleRequest $request)
    {
        $this->vehicleService->create($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Thêm xe mới thành công!');
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $this->vehicleService->update($vehicle, $request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Cập nhật thông tin xe thành công!');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->vehicleService->delete($vehicle);

        return redirect()->route('vehicles.index')->with('success', 'Xóa xe thành công!');
    }
}
