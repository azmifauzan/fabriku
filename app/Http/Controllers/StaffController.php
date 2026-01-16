<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            })
            ->when(request('is_active') !== null, fn ($query) => $query->where('is_active', request('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'filters' => request()->only(['search', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:staff,code,NULL,id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Staff::create($validated);

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil ditambahkan.');
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Staff/Form', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:staff,code,'.$staff->id.',id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $staff->update($validated);

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil diupdate.');
    }

    public function destroy(Staff $staff)
    {
        // Check if any preparation orders use this staff
        if (\App\Models\PreparationOrder::where('prepared_by', $staff->id)->exists()) {
            return back()->with('error', 'Staff tidak bisa dihapus karena masih digunakan oleh order preparation.');
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil dihapus.');
    }
}
