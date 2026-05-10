<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $rolesQuery = Role::query();

        if (auth()->user()->hasRole('DISPATCH')) {
            $rolesQuery->whereIn('role_code', ['DRIVER', 'FIELD']);
        }

        $roles = $rolesQuery->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Extra check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            $role = Role::findOrFail($request->role_id);
            if (!in_array($role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn chỉ được phép tạo tài khoản Tài xế hoặc Hiện trường.');
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'username' => strstr($request->email, '@', true),
        ]);

        return redirect()->route('users.index')->with('success', 'Tạo tài khoản nhân viên thành công!');
    }

    public function edit(User $user)
    {
        // Security check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            if (!in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền chỉnh sửa tài khoản này.');
            }
        }

        $rolesQuery = Role::query();
        if (auth()->user()->hasRole('DISPATCH')) {
            $rolesQuery->whereIn('role_code', ['DRIVER', 'FIELD']);
        }
        $roles = $rolesQuery->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Security check for DISPATCH
        if (auth()->user()->hasRole('DISPATCH')) {
            if (!in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền chỉnh sửa tài khoản này.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        // Extra check for DISPATCH role selection
        if (auth()->user()->hasRole('DISPATCH')) {
            $role = Role::findOrFail($request->role_id);
            if (!in_array($role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn chỉ được phép gán vai trò Tài xế hoặc Hiện trường.');
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
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
            if (!in_array($user->role->role_code, ['DRIVER', 'FIELD'])) {
                abort(403, 'Bạn không có quyền xóa tài khoản này.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Đã xóa tài khoản nhân viên.');
    }
}
