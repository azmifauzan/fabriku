<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Staff;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\StaffAccountCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Only admin can manage staff
        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola staff.');
        }

        $staff = Staff::query()
            ->with(['role', 'user'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when(request('is_active') !== null, fn ($query) => $query->where('is_active', request('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $maxStaff = (int) SystemSetting::get('max_staff_per_tenant', 5, $user->tenant_id)
            ?: (int) SystemSetting::get('max_staff_per_tenant', 5);
        $currentStaffCount = Staff::count();

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'filters' => request()->only(['search', 'is_active']),
            'maxStaff' => $maxStaff,
            'currentStaffCount' => $currentStaffCount,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambah staff.');
        }

        // Check staff limit
        $maxStaff = (int) SystemSetting::get('max_staff_per_tenant', 5, $user->tenant_id)
            ?: (int) SystemSetting::get('max_staff_per_tenant', 5);
        $currentStaffCount = Staff::count();

        if ($currentStaffCount >= $maxStaff) {
            return redirect()->route('staff.index')
                ->with('error', "Batas maksimal staff ({$maxStaff}) telah tercapai. Hubungi admin untuk menambah kuota.");
        }

        // Get available roles (system roles, excluding tenant_admin)
        $roles = Role::whereNull('tenant_id')
            ->where('is_system_role', true)
            ->where('slug', '!=', 'tenant_admin')
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Staff/Form', [
            'roles' => $roles,
            'maxStaff' => $maxStaff,
            'currentStaffCount' => $currentStaffCount,
        ]);
    }

    public function show(Staff $staff)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat melihat detail staff.');
        }

        $staff->load(['role', 'user']);
        $staff->loadCount('preparationOrders');
        $staff->load(['preparationOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
            'recentOrders' => $staff->preparationOrders,
            'stats' => [
                'total_preparations' => $staff->preparationOrders()->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambah staff.');
        }

        // Check staff limit
        $maxStaff = (int) SystemSetting::get('max_staff_per_tenant', 5, $user->tenant_id)
            ?: (int) SystemSetting::get('max_staff_per_tenant', 5);
        $currentStaffCount = Staff::count();

        if ($currentStaffCount >= $maxStaff) {
            return back()->with('error', "Batas maksimal staff ({$maxStaff}) telah tercapai.");
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:staff,code,NULL,id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        // Generate a random password
        $plainPassword = Str::random(10);

        DB::transaction(function () use ($validated, $user, $role, $plainPassword, &$staff) {
            // Create user account for staff
            $staffUser = User::create([
                'tenant_id' => $user->tenant_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'role' => 'staff',
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            // Assign role to user
            $staffUser->assignRole($role);

            // Create staff record linked to user
            $staff = Staff::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $staffUser->id,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'role_id' => $role->id,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Send email notification with credentials
            $staffUser->notify(new StaffAccountCreatedNotification(
                $plainPassword,
                $user->tenant->name,
                $role->name,
            ));
        });

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil ditambahkan. Email dengan detail login telah dikirim ke '.$validated['email'].'.');
    }

    public function edit(Staff $staff)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengedit staff.');
        }

        $staff->load(['role', 'user']);

        $roles = Role::whereNull('tenant_id')
            ->where('is_system_role', true)
            ->where('slug', '!=', 'tenant_admin')
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Staff/Form', [
            'staff' => $staff,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengedit staff.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:staff,code,'.$staff->id.',id,tenant_id,'.auth()->user()->tenant_id],
            'name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($staff->user_id ?? 'NULL')],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        DB::transaction(function () use ($validated, $staff, $role) {
            $staff->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'role_id' => $role->id,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Update linked user if exists
            if ($staff->user) {
                $staff->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                // Update role on user
                $staff->user->syncRoles([$role->id]);
            }
        });

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil diupdate.');
    }

    public function destroy(Staff $staff)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menghapus staff.');
        }

        // Check if any preparation orders use this staff
        if (\App\Models\PreparationOrder::where('prepared_by', $staff->id)->exists()) {
            return back()->with('error', 'Staff tidak bisa dihapus karena masih digunakan oleh order preparation.');
        }

        DB::transaction(function () use ($staff) {
            // Deactivate linked user
            if ($staff->user) {
                $staff->user->update(['is_active' => false]);
            }

            $staff->delete();
        });

        return redirect()->route('staff.index')
            ->with('success', 'Staff berhasil dihapus.');
    }
}
