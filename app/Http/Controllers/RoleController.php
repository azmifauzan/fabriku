<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Scopes\TenantScope;
use App\Models\Staff;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola role.');
        }

        $systemRoles = Role::withoutGlobalScope(TenantScope::class)
            ->whereNull('tenant_id')
            ->where('is_system_role', true)
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'is_system_role']);

        $customRoles = Role::where('is_system_role', false)
            ->withCount(['permissions', 'staffMembers'])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'is_system_role']);

        return Inertia::render('Roles/Index', [
            'systemRoles' => $systemRoles,
            'customRoles' => $customRoles,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambah role.');
        }

        return Inertia::render('Roles/Form', [
            'permissions' => $this->assignablePermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menambah role.');
        }

        $validated = $request->validated();
        $slug = $this->uniqueSlug(Str::slug($validated['name']), $user->tenant_id);

        $role = Role::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system_role' => false,
        ]);

        $role->permissions()->sync($validated['permission_ids']);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengedit role.');
        }

        $role->load('permissions');

        return Inertia::render('Roles/Form', [
            'role' => $role,
            'permissions' => $this->assignablePermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengedit role.');
        }

        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permission_ids']);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menghapus role.');
        }

        if (Staff::where('role_id', $role->id)->exists() || $role->users()->exists()) {
            return back()->with('error', 'Role tidak bisa dihapus karena masih digunakan.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }

    private function assignablePermissions()
    {
        return Permission::where('module', '!=', 'tenant')
            ->orderBy('module')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'module'])
            ->groupBy('module');
    }

    private function uniqueSlug(string $base, int $tenantId): string
    {
        $slug = $base;
        $suffix = 2;

        while (Role::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
