<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    public function index(Request $request)
    {
        $locations = $this->locationService->getAll($request->all());

        return view('locations.index', compact('locations'));
    }

    public function store(LocationRequest $request)
    {
        $this->locationService->create($request->validated());

        return redirect()->route('locations.index')->with('success', 'Thêm địa điểm thành công!');
    }

    public function update(LocationRequest $request, Location $location)
    {
        $this->locationService->update($location, $request->validated());

        return redirect()->route('locations.index')->with('success', 'Cập nhật địa điểm thành công!');
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $this->locationService->delete($location);

        return redirect()->route('locations.index')->with('success', 'Xóa địa điểm thành công!');
    }
}
