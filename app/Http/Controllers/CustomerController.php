<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\ExportService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $customers = $this->customerService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách khách hàng', 'Tất cả dữ liệu đang lọc', [
                'Mã KH', 'Khách hàng', 'Công ty', 'MST', 'Email', 'Người liên hệ', 'SĐT',
            ], $customers->map(fn (Customer $customer): array => [
                $customer->customer_code,
                $customer->customer_name,
                $customer->company_name,
                $customer->tax_code,
                $customer->email,
                $customer->contact_person,
                $customer->phone,
            ])->all());
        }

        $customers = $this->customerService->getAll($request->all());

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request)
    {
        $this->customerService->create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Thêm khách hàng thành công!');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'Cập nhật khách hàng thành công!');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerService->delete($customer);

        return redirect()->route('customers.index')->with('success', 'Xóa khách hàng thành công!');
    }
}
