<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('username');
    }

    public function showRegister()
    {
        $roles = Role::all();

        return view('auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'name' => $request->full_name, // fallback for default name field
            'employee_code' => $this->generateEmployeeCode(),
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'status' => 1,
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
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
