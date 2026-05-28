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

        $activeTab = $request->input('_active_tab', 'tab-hosoca');
        $rules = [];
        $profileData = [];

        if ($activeTab === 'tab-hosoca') {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            ];
            $request->validate($rules);

            $profileData = [
                'name' => $request->name,
                'full_name' => $request->name,
                'email' => $request->email,
            ];
        } elseif ($activeTab === 'tab-baomat') {
            $rules = [
                'theme_color' => ['required', 'string', 'max:7'],
                'is_dark_mode' => ['boolean'],
                'timezone' => ['required', 'in:Asia/Ho_Chi_Minh,Asia/Bangkok,UTC'],
                'date_format' => ['required', 'in:d/m/Y,Y-m-d,d-m-Y'],
                'two_factor_enabled' => ['boolean'],
                'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ];
            $request->validate($rules);

            $profileData = [
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

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }
        }

        if (!empty($profileData)) {
            $user->update($profileData);
        }

        session()->flash('active_tab', $activeTab);
        return back()->with('success', 'Cập nhật thành công! (Chế độ tối: '.($user->is_dark_mode ? 'Bật' : 'Tắt').')');
    }
}
