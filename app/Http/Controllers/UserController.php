<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\ExportService;
use App\Support\LogisticsOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with('role');

        if (auth()->user()->hasRole('DISPATCH')) {
            $query->whereHas('role', function ($q) {
                $q->whereIn('role_code', ['DRIVER', 'FIELD']);
            });
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        foreach (['employee_code', 'name', 'email', 'position', 'department'] as $field) {
            if (request()->filled($field)) {
                $query->where($field, 'like', '%'.request($field).'%');
            }
        }

        if (request()->filled('role_id')) {
            $query->where('role_id', request('role_id'));
        }

        if (request()->filled('export')) {
            $users = $query->latest()->limit(10000)->get();

            return app(ExportService::class)->download((string) request()->string('export'), 'Danh sách nhân sự', 'Tất cả dữ liệu đang lọc', [
                'Mã nhân sự', 'Họ tên', 'Email', 'Vai trò', 'Chức vụ', 'Bộ phận', 'Ngày sinh', 'Ngày tham gia',
            ], $users->map(fn (User $user): array => [
                $user->employee_code,
                $user->name,
                $user->email,
                $user->role?->role_name,
                $user->position,
                $user->department,
                $user->date_of_birth?->format('d/m/Y'),
                $user->joined_at?->format('d/m/Y'),
            ])->all());
        }

        $users = $query->paginate(10);
        $roles = Role::orderBy('role_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $rolesQuery = Role::query();

        if (auth()->user()->hasRole('DISPATCH')) {
            $rolesQuery->whereIn('role_code', ['DRIVER', 'FIELD']);
        }

        $roles = $rolesQuery->get();
        $positions = LogisticsOptions::positions();
        $departments = LogisticsOptions::departments();

        return view('users.create', compact('roles', 'positions', 'departments'));
    }

    public function store(Request $request)
    {
        $map = LogisticsOptions::departmentPositionMap();
        $department = $request->input('department');
        $validPositions = $map[$department] ?? [];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role_id' => 'required|exists:roles,id',
            'department' => ['required', Rule::in(array_keys($map))],
            'position' => ['required', Rule::in($validPositions)],
            'date_of_birth' => 'required|date|before:today',
            'joined_at' => 'required|date|after_or_equal:'.now()->subYears(10)->toDateString().'|before_or_equal:today',
        ], [
            'department.required' => 'Vui lòng chọn bộ phận / phòng ban.',
            'department.in' => 'Bộ phận không hợp lệ.',
            'position.required' => 'Vui lòng chọn chức vụ.',
            'position.in' => 'Chức vụ không hợp lệ đối với phòng ban đã chọn.',
        ]);

        // Extra check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            $role = Role::findOrFail($request->role_id);
            if (! in_array($role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn chỉ được phép tạo tài khoản Tài xế hoặc Hiện trường.');
            }
        }

        User::create([
            'name' => $request->name,
            'full_name' => $request->name,
            'employee_code' => $this->generateEmployeeCode(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'username' => strstr($request->email, '@', true),
            'status' => 1,
            'position' => $request->position,
            'department' => $request->department,
            'date_of_birth' => $request->date_of_birth,
            'joined_at' => $request->joined_at,
        ]);

        return redirect()->route('users.index')->with('success', 'Tạo tài khoản nhân viên thành công!');
    }

    public function edit(User $user)
    {
        // Security check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            if (! in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền chỉnh sửa tài khoản này.');
            }
        }

        $rolesQuery = Role::query();
        if (auth()->user()->hasRole('DISPATCH')) {
            $rolesQuery->whereIn('role_code', ['DRIVER', 'FIELD']);
        }
        $roles = $rolesQuery->get();
        $positions = LogisticsOptions::positions();
        $departments = LogisticsOptions::departments();

        return view('users.edit', compact('user', 'roles', 'positions', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        // Security check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            if (! in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền chỉnh sửa tài khoản này.');
            }
        }

        $map = LogisticsOptions::departmentPositionMap();
        $department = $request->input('department');
        $validPositions = $map[$department] ?? [];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'department' => ['required', Rule::in(array_keys($map))],
            'position' => ['required', Rule::in($validPositions)],
            'date_of_birth' => 'required|date|before:today',
            'joined_at' => 'required|date|after_or_equal:'.now()->subYears(10)->toDateString().'|before_or_equal:today',
        ], [
            'department.required' => 'Vui lòng chọn bộ phận / phòng ban.',
            'department.in' => 'Bộ phận không hợp lệ.',
            'position.required' => 'Vui lòng chọn chức vụ.',
            'position.in' => 'Chức vụ không hợp lệ đối với phòng ban đã chọn.',
        ]);

        // Extra check for DISPATCH role selection
        if (auth()->user()->hasRole('DISPATCH')) {
            $role = Role::findOrFail($request->role_id);
            if (! in_array($role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn chỉ được phép gán vai trò Tài xế hoặc Hiện trường.');
            }
        }

        $user->update([
            'name' => $request->name,
            'full_name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'position' => $request->position,
            'department' => $request->department,
            'date_of_birth' => $request->date_of_birth,
            'joined_at' => $request->joined_at,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Password::min(8)->mixedCase()->numbers()->symbols()]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('users.index')->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        // Security check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            if (! in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền xóa tài khoản này.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Đã xóa tài khoản nhân viên.');
    }

    private function generateEmployeeCode(): string
    {
        $date = now()->format('ym');
        $prefix = "NV-{$date}-";

        $lastUser = User::withTrashed()
            ->where('employee_code', 'like', "{$prefix}%")
            ->orderBy('employee_code', 'desc')
            ->first();

        if ($lastUser) {
            $lastSequence = (int) substr($lastUser->employee_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
