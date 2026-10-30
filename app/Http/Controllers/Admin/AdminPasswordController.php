<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminPasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Account/Password');
    }

    public function update(UpdateAdminPasswordRequest $request): RedirectResponse
    {
        $request->user('admin')->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
