<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['tenant', 'roles']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'tenants' => $tenants,
            'filters' => $request->only(['search', 'tenant_id', 'role', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $tenants = Tenant::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $roles = Role::whereNull('tenant_id')
            ->where('is_system_role', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Admin/Users/Create', [
            'tenants' => $tenants,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'tenant_id' => $validated['tenant_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
            'email_verified_at' => now(),
        ]);

        // Assign roles if provided
        if (! empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['tenant', 'roles.permissions']);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $user->load('roles');

        $tenants = Tenant::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $roles = Role::whereNull('tenant_id')
            ->where('is_system_role', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'tenants' => $tenants,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'tenant_id' => $validated['tenant_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        // Sync roles
        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deactivated successfully.');
    }
}
