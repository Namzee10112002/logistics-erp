<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'theme_color' => ['required', 'string', 'max:7'],
            'is_dark_mode' => ['boolean'],
            'timezone' => ['required', 'in:Asia/Ho_Chi_Minh,Asia/Bangkok,UTC'],
            'date_format' => ['required', 'in:d/m/Y,Y-m-d,d-m-Y'],
            'two_factor_enabled' => ['boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $profileData = [
            'name' => $request->name,
            'full_name' => $request->name,
            'email' => $request->email,
            'theme_color' => $request->theme_color,
            'is_dark_mode' => $request->boolean('is_dark_mode'),
        ];

        foreach (['timezone', 'date_format', 'two_factor_enabled'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $profileData[$column] = $column === 'two_factor_enabled'
                    ? $request->boolean($column)
                    : $request->input($column);
            }
        }

        $user->update($profileData);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Cập nhật thành công! (Chế độ tối: '.($user->is_dark_mode ? 'Bật' : 'Tắt').')');
    }
}
