<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('id')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'fullname' => 'required|string|max:100',
            'password' => ['required', Password::min(6)],
            'role'     => 'required|in:admin,staff',
        ]);

        User::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'เพิ่มพนักงานสำเร็จ');
    }

    public function edit(User $user): View
    {
        return view('admin.users.index', [
            'users'    => User::orderBy('id')->get(),
            'editUser' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'fullname' => 'required|string|max:100',
            'role'     => 'required|in:admin,staff',
            'password' => ['nullable', Password::min(6)],
        ]);

        $data = [
            'fullname' => $request->fullname,
            'role'     => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'แก้ไขข้อมูลพนักงานสำเร็จ');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบพนักงานสำเร็จ');
    }
}
