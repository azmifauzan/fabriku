# Tenant Role Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a tenant admin create, edit, and delete custom roles (with a permission checklist) scoped to their own tenant, and fix the staff-form role dropdown which is currently always empty due to a `TenantScope` conflict.

**Architecture:** New `RoleController` (tenant-scoped, mirrors the existing `StaffController` pattern: `isAdmin()`-gated in each method, no `permission:` middleware) backed by two Form Requests. Global "system" roles (`roles.tenant_id IS NULL`) stay read-only — Eloquent's implicit route-model binding automatically 404s any attempt to edit/update/delete one, because `Role`'s global `TenantScope` filters them out of the query used to resolve the route parameter. `StaffController`'s role query is fixed to explicitly bypass that same scope for the system-role half of its query (it currently ANDs a scope-injected `tenant_id = X` with an explicit `tenant_id IS NULL`, which can never match).

**Tech Stack:** Laravel 12 (Form Requests, Eloquent global scopes, route-model binding), Inertia.js v2 + Vue 3 `<script setup>`, Pest 4.

## Global Constraints

- UI strings, error/flash messages: Bahasa Indonesia (project-wide convention).
- PHP: explicit return types on all new methods; curly braces for all control structures; Form Requests for validation, not inline `$request->validate()`.
- Eloquent: prefer `Model::query()` / fluent builder; eager-load relations used in loops to avoid N+1.
- Every behavior change needs a Pest feature test using specific assertion methods (`assertForbidden()`, `assertNotFound()`), not `assertStatus(403)`.
- Vue: single root element per SFC; `<Link>` / `router.visit()` / `router.delete()`, never raw `<a>`.
- Role CRUD access is gated by `auth()->user()->isAdmin()` inside each controller method — same pattern as `StaffController`, not the `permission:<slug>` route middleware used elsewhere.
- Custom-role `permission_ids` must never include a permission whose `module` is `tenant` (platform-only) — enforced server-side in the Form Request, independent of what the UI renders.
- System roles (`roles.tenant_id IS NULL`) are never mutated by tenant-facing routes; rely on `TenantScope` + implicit route-model binding for that, don't add manual ownership checks that duplicate it.
- No schema changes — `roles`/`permissions`/`role_permissions`/`user_roles` tables already support everything this feature needs.

---

### Task 1: RoleController — index, create, store

**Files:**
- Create: `app/Http/Requests/StoreRoleRequest.php`
- Create: `app/Http/Controllers/RoleController.php`
- Modify: `app/Models/Role.php` (add `staffMembers()` relation, ~line 60 after the `users()` method)
- Modify: `routes/web.php` (add `use` import ~line 31, add resource route ~line 171)
- Test: `tests/Feature/RoleControllerTest.php`

**Interfaces:**
- Consumes: `App\Models\Role` (existing `permissions()`, `users()`, `tenant_id`, `is_system_role`), `App\Models\Permission` (`module` column), `App\Models\Scopes\TenantScope`.
- Produces: route names `roles.index`, `roles.create`, `roles.store`; `RoleController::uniqueSlug(string $base, int $tenantId): string` (private helper, used again by no other task); `Role::staffMembers(): HasMany` — Task 3 (destroy) and this task's `index()` both call it.

- [ ] **Step 1: Add `staffMembers()` relation to `Role`**

Edit `app/Models/Role.php`. Add the import and method:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```

(add alongside the existing `BelongsTo`/`BelongsToMany` imports at the top)

```php
    /**
     * Get the staff members assigned this role
     */
    public function staffMembers(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
```

(add directly after the existing `users()` method, before `hasPermission()`)

- [ ] **Step 2: Write `StoreRoleRequest`**

Create `app/Http/Requests/StoreRoleRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn ($query) => $query->where('module', '!=', 'tenant')),
            ],
        ];
    }
}
```

- [ ] **Step 3: Write the failing tests for index/create/store**

Create `tests/Feature/RoleControllerTest.php`:

```php
<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

function makeRolePermission(string $slug, string $module): Permission
{
    return Permission::create([
        'name' => $slug,
        'slug' => $slug,
        'module' => $module,
        'description' => $slug,
    ]);
}

test('admin can create a custom role with permissions', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $materialView = makeRolePermission('material.view', 'material');
    $salesView = makeRolePermission('sales.view', 'sales');

    $this->actingAs($admin);

    $response = $this->post(route('roles.store'), [
        'name' => 'Kasir Gudang',
        'description' => 'Akses gudang & kasir',
        'permission_ids' => [$materialView->id, $salesView->id],
    ]);

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');

    $this->assertDatabaseHas('roles', [
        'tenant_id' => $tenant->id,
        'name' => 'Kasir Gudang',
        'slug' => 'kasir-gudang',
        'is_system_role' => false,
    ]);

    $role = Role::where('name', 'Kasir Gudang')->first();
    expect($role->permissions()->pluck('permissions.id')->sort()->values()->all())
        ->toBe(collect([$materialView->id, $salesView->id])->sort()->values()->all());
});

test('non-admin cannot access role management', function () {
    $tenant = Tenant::factory()->create();
    $staffUser = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'staff']);
    $permission = makeRolePermission('material.view', 'material');

    $this->actingAs($staffUser);

    $this->get(route('roles.index'))->assertForbidden();
    $this->get(route('roles.create'))->assertForbidden();
    $this->post(route('roles.store'), [
        'name' => 'Test',
        'permission_ids' => [$permission->id],
    ])->assertForbidden();
});

test('store rejects a permission from the tenant module', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $tenantPermission = makeRolePermission('tenant.view', 'tenant');

    $this->actingAs($admin);

    $response = $this->post(route('roles.store'), [
        'name' => 'Sneaky Role',
        'permission_ids' => [$tenantPermission->id],
    ]);

    $response->assertSessionHasErrors(['permission_ids.0']);
    $this->assertDatabaseMissing('roles', ['name' => 'Sneaky Role']);
});

test('duplicate role names across tenants do not collide on slug', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    $adminB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view', 'material');

    $this->actingAs($adminA);
    $this->post(route('roles.store'), ['name' => 'Kasir', 'permission_ids' => [$permission->id]]);

    $this->actingAs($adminB);
    $this->post(route('roles.store'), ['name' => 'Kasir', 'permission_ids' => [$permission->id]]);

    $this->assertDatabaseHas('roles', ['tenant_id' => $tenantA->id, 'name' => 'Kasir', 'slug' => 'kasir']);
    $this->assertDatabaseHas('roles', ['tenant_id' => $tenantB->id, 'name' => 'Kasir', 'slug' => 'kasir']);
});

test('index lists system roles and the tenant custom roles with counts', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view', 'material');

    Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager', 'is_system_role' => true]);

    $this->actingAs($admin);
    $this->post(route('roles.store'), ['name' => 'Kasir', 'permission_ids' => [$permission->id]]);

    $response = $this->get(route('roles.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('Roles/Index')
        ->has('systemRoles', 1)
        ->has('customRoles', 1)
        ->where('customRoles.0.name', 'Kasir')
        ->where('customRoles.0.permissions_count', 1)
    );
});
```

- [ ] **Step 4: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/RoleControllerTest.php`
Expected: FAIL — route `roles.store` / `roles.index` not defined (or class `RoleController` not found).

- [ ] **Step 5: Write `RoleController`**

Create `app/Http/Controllers/RoleController.php`:

```php
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
```

- [ ] **Step 6: Wire the route**

Edit `routes/web.php`. Add the import after `use App\Http\Controllers\ReportController;` (currently line 31):

```php
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesOrderController;
```

Add the resource route right after the existing `Route::resource('staff', StaffController::class);` line (currently line 171):

```php
    // Staff Management (admin-only enforced in controller)
    Route::resource('staff', StaffController::class);

    // Role Management (admin-only enforced in controller)
    Route::resource('roles', RoleController::class)->except(['show']);
```

- [ ] **Step 7: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/RoleControllerTest.php`
Expected: PASS (5 tests)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/StoreRoleRequest.php app/Http/Controllers/RoleController.php app/Models/Role.php routes/web.php tests/Feature/RoleControllerTest.php
git commit -m "feat(role): add tenant role index/create/store"
```

---

### Task 2: RoleController — edit, update

**Files:**
- Create: `app/Http/Requests/UpdateRoleRequest.php`
- Modify: `app/Http/Controllers/RoleController.php` (already has `edit()`/`update()` stubs calling this request from Task 1 — this task just adds the request class and its tests)
- Test: `tests/Feature/RoleControllerTest.php` (append)

**Interfaces:**
- Consumes: `RoleController::edit()`/`update()` from Task 1 (already wired to `UpdateRoleRequest` and route `roles.update`/`roles.edit`).
- Produces: nothing new consumed by later tasks — this closes out validation for the update path.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/RoleControllerTest.php`:

```php
test('admin can edit own custom role', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $permission = makeRolePermission('material.view', 'material');
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->put(route('roles.update', $role), [
        'name' => 'Kasir Senior',
        'description' => 'updated',
        'permission_ids' => [$permission->id],
    ]);

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');
    $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Kasir Senior']);
    expect($role->fresh()->permissions()->pluck('permissions.id')->all())->toBe([$permission->id]);
});

test('editing a system role 404s', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $systemRole = Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager', 'is_system_role' => true]);

    $this->actingAs($admin);

    $this->get(route('roles.edit', $systemRole))->assertNotFound();
});

test('editing another tenant role 404s', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
    $roleB = Role::create(['tenant_id' => $tenantB->id, 'name' => 'Kasir B', 'slug' => 'kasir-b', 'is_system_role' => false]);

    $this->actingAs($adminA);

    $this->get(route('roles.edit', $roleB))->assertNotFound();
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/RoleControllerTest.php`
Expected: FAIL — `Class "App\Http\Requests\UpdateRoleRequest" not found`.

- [ ] **Step 3: Write `UpdateRoleRequest`**

Create `app/Http/Requests/UpdateRoleRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn ($query) => $query->where('module', '!=', 'tenant')),
            ],
        ];
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/RoleControllerTest.php`
Expected: PASS (8 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/UpdateRoleRequest.php tests/Feature/RoleControllerTest.php
git commit -m "feat(role): add tenant role edit/update"
```

---

### Task 3: RoleController — destroy guard

**Files:**
- Modify: none (`destroy()` was already written in Task 1 and is fully correct — this task only adds the tests that prove it)
- Test: `tests/Feature/RoleControllerTest.php` (append)

**Interfaces:**
- Consumes: `RoleController::destroy()` (Task 1), `Role::staffMembers()` (Task 1), `Role::users()` (pre-existing).
- Produces: nothing new for later tasks.

- [ ] **Step 1: Write the tests**

Append to `tests/Feature/RoleControllerTest.php`:

```php
test('deleting an unused custom role succeeds', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->delete(route('roles.destroy', $role));

    $response->assertRedirect(route('roles.index'))->assertSessionHas('success');
    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('deleting a role assigned to staff is blocked', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $role = Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir', 'slug' => 'kasir', 'is_system_role' => false]);
    \App\Models\Staff::factory()->create(['tenant_id' => $tenant->id, 'role_id' => $role->id]);

    $this->actingAs($admin);

    $response = $this->delete(route('roles.destroy', $role));

    $response->assertRedirect()->assertSessionHas('error');
    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

test('deleting a system role 404s', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
    $systemRole = Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager', 'is_system_role' => true]);

    $this->actingAs($admin);

    $this->delete(route('roles.destroy', $systemRole))->assertNotFound();
});
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --compact tests/Feature/RoleControllerTest.php`
Expected: PASS (11 tests) — `destroy()` already exists from Task 1, no implementation step needed here.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/RoleControllerTest.php
git commit -m "test(role): cover role deletion guard and system-role 404s"
```

---

### Task 4: Fix the `StaffController` role-dropdown bug

**Files:**
- Modify: `app/Http/Controllers/StaffController.php:73-78` (in `create()`) and `:191-195` (in `edit()`)
- Test: `tests/Feature/StaffTest.php` (append)

**Interfaces:**
- Consumes: `Role::withoutGlobalScope(TenantScope::class)` pattern (same as `RoleController::index()`, Task 1).
- Produces: `Staff/Form` Inertia prop `roles` now contains both system and tenant-custom roles, sorted by name — no other task depends on this.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/StaffTest.php`:

```php
test('staff create form lists system roles and tenant custom roles', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);

    Role::create(['tenant_id' => null, 'name' => 'Manager', 'slug' => 'manager', 'is_system_role' => true]);
    Role::create(['tenant_id' => null, 'name' => 'Tenant Admin', 'slug' => 'tenant_admin', 'is_system_role' => true]);
    Role::create(['tenant_id' => $tenant->id, 'name' => 'Kasir Gudang', 'slug' => 'kasir-gudang', 'is_system_role' => false]);

    $this->actingAs($admin);

    $response = $this->get(route('staff.create'));

    $response->assertInertia(fn ($page) => $page
        ->component('Staff/Form')
        ->has('roles', 2)
        ->where('roles.0.name', 'Kasir Gudang')
        ->where('roles.1.name', 'Manager')
    );
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --compact tests/Feature/StaffTest.php --filter="lists system roles"`
Expected: FAIL — `has('roles', 2)` fails because the current query returns 0 roles (the `TenantScope` conflict described in the design spec).

- [ ] **Step 3: Fix `StaffController`**

Edit `app/Http/Controllers/StaffController.php`. Add the import alongside the existing `use` statements:

```php
use App\Models\Scopes\TenantScope;
```

In `create()`, replace (currently lines 73-78):

```php
        // Get available roles (system roles, excluding tenant_admin)
        $roles = Role::whereNull('tenant_id')
            ->where('is_system_role', true)
            ->where('slug', '!=', 'tenant_admin')
            ->select('id', 'name', 'slug', 'description')
            ->get();
```

with:

```php
        // System roles need withoutGlobalScope: TenantScope always ANDs
        // tenant_id = current tenant, which would otherwise contradict
        // the explicit whereNull('tenant_id') below and return nothing.
        $systemRoles = Role::withoutGlobalScope(TenantScope::class)
            ->whereNull('tenant_id')
            ->where('is_system_role', true)
            ->where('slug', '!=', 'tenant_admin')
            ->select('id', 'name', 'slug', 'description')
            ->get();

        $customRoles = Role::where('is_system_role', false)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        $roles = $systemRoles->concat($customRoles)->sortBy('name')->values();
```

In `edit()`, replace the identical block (currently lines 191-195) the same way.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/StaffTest.php`
Expected: PASS (all tests in the file, including the new one)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/StaffController.php tests/Feature/StaffTest.php
git commit -m "fix(staff): role dropdown was always empty due to TenantScope conflict"
```

---

### Task 5: Frontend — Roles/Form.vue + Sidebar entry

**Files:**
- Create: `resources/js/pages/Roles/Form.vue`
- Modify: `resources/js/components/Sidebar.vue:72`

**Interfaces:**
- Consumes: Inertia props from `RoleController::create()`/`edit()` (Task 1/2): `permissions: Record<string, {id:number; name:string; slug:string; module:string}[]>`, optional `role: {id:number; name:string; description:string|null; permissions:{id:number}[]}`. Posts to `/roles` (create) or puts to `/roles/{id}` (edit) — routes from Task 1/2.
- Produces: page component `Roles/Form` referenced by `Inertia::render('Roles/Form', ...)` in `RoleController`.

- [ ] **Step 1: Create the form page**

Create `resources/js/pages/Roles/Form.vue`:

```vue
<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Permission {
    id: number;
    name: string;
    slug: string;
    module: string;
}

interface Role {
    id: number;
    name: string;
    description: string | null;
    permissions?: Permission[];
}

const props = defineProps<{
    role?: Role;
    permissions: Record<string, Permission[]>;
}>();

const isEditing = !!props.role?.id;

const form = useForm({
    name: props.role?.name || '',
    description: props.role?.description || '',
    permission_ids: props.role?.permissions?.map((p) => p.id) || ([] as number[]),
});

const submit = () => {
    if (props.role?.id) {
        form.put(`/roles/${props.role.id}`, { preserveScroll: true });
    } else {
        form.post('/roles', { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Role' : 'Tambah Role'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Role' : 'Tambah Role Baru' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ isEditing ? 'Perbarui nama, deskripsi, dan izin role' : 'Buat role khusus dengan izin yang Anda tentukan' }}
                        </p>
                    </div>
                    <Link
                        href="/roles"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <FormSection title="Informasi Dasar" description="Nama dan deskripsi role">
                        <div class="grid grid-cols-1 gap-6">
                            <FormField
                                v-model="form.name"
                                label="Nama Role"
                                type="text"
                                placeholder="Contoh: Kasir Gudang"
                                :required="true"
                                :error="form.errors.name"
                            />
                            <FormField
                                v-model="form.description"
                                label="Deskripsi"
                                type="textarea"
                                placeholder="Deskripsi singkat kegunaan role ini"
                                :error="form.errors.description"
                            />
                        </div>
                    </FormSection>

                    <FormSection title="Izin Akses" description="Pilih menu/aksi yang bisa diakses role ini">
                        <p v-if="form.errors.permission_ids" class="mb-4 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.permission_ids }}
                        </p>
                        <div class="space-y-6">
                            <div v-for="(modulePermissions, moduleName) in permissions" :key="moduleName">
                                <h4 class="mb-2 text-sm font-semibold text-gray-700 uppercase dark:text-gray-300">{{ moduleName }}</h4>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in modulePermissions"
                                        :key="permission.id"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700"
                                    >
                                        <input
                                            v-model="form.permission_ids"
                                            type="checkbox"
                                            :value="permission.id"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600"
                                        />
                                        <span class="text-gray-700 dark:text-gray-200">{{ permission.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </FormSection>

                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            href="/roles"
                            class="rounded-lg border border-gray-300 px-6 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                        >
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
```

- [ ] **Step 2: Add the Sidebar entry**

Edit `resources/js/components/Sidebar.vue`. Replace line 72:

```js
        { name: 'Staff', href: '/staff', permission: null, adminOnly: true },
```

with:

```js
        { name: 'Staff', href: '/staff', permission: null, adminOnly: true },
        { name: 'Role & Izin', href: '/roles', permission: null, adminOnly: true },
```

- [ ] **Step 3: Verify in the browser**

Run: `composer dev` (or ensure `php artisan serve` + `npm run dev` are already running)

Log in as `admin@konveksi.com` / `password`, open the sidebar "Master Data" group, click "Role & Izin" (should 200, empty custom-role list since Task 6's Index page doesn't exist yet — expect a blank/broken page here, that's fine, Task 6 delivers `Roles/Index.vue`), then navigate directly to `/roles/create` and confirm the permission checklist renders grouped by module and the "Tambah Role" flow redirects back with a success flash.

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/Roles/Form.vue resources/js/components/Sidebar.vue
git commit -m "feat(role): add role create/edit form and sidebar entry"
```

---

### Task 6: Frontend — Roles/Index.vue

**Files:**
- Create: `resources/js/pages/Roles/Index.vue`

**Interfaces:**
- Consumes: Inertia props from `RoleController::index()` (Task 1): `systemRoles: {id; name; slug; description; is_system_role; permissions_count}[]`, `customRoles: {...same, staff_members_count}[]`. Deletes via `router.delete('/roles/{id}')` (Task 1 route).
- Produces: page component `Roles/Index` referenced by `Inertia::render('Roles/Index', ...)`.

- [ ] **Step 1: Create the index page**

Create `resources/js/pages/Roles/Index.vue`:

```vue
<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Shield, Trash2 } from 'lucide-vue-next';

interface RoleRow {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_system_role: boolean;
    permissions_count: number;
    staff_members_count?: number;
}

defineProps<{
    systemRoles: RoleRow[];
    customRoles: RoleRow[];
}>();

const { confirm } = useSweetAlert();

const deleteRole = async (role: RoleRow) => {
    const result = await confirm('Hapus Role', `Apakah Anda yakin ingin menghapus role "${role.name}"?`, 'Ya, Hapus', 'warning', '#dc2626');

    if (result.isConfirmed) {
        router.delete(`/roles/${role.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Role & Izin" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Role & Izin</h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Kelola role custom dan lihat izin tiap role</p>
                    </div>
                    <Link
                        href="/roles/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    >
                        <Plus :size="16" />
                        Tambah Role
                    </Link>
                </div>

                <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Role Custom Tenant</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Deskripsi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Jumlah Izin
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Jumlah Staff
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="role in customRoles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.description || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.permissions_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.staff_members_count ?? 0 }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="`/roles/${role.id}/edit`"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                                title="Edit"
                                            >
                                                <Edit :size="16" />
                                            </Link>
                                            <button
                                                @click="deleteRole(role)"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                                title="Hapus"
                                            >
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="customRoles.length === 0" class="p-12 text-center">
                            <Shield :size="40" class="mx-auto mb-3 text-gray-300 dark:text-gray-600" />
                            <p class="text-gray-500 dark:text-gray-400">Belum ada role custom. Buat role baru untuk mengatur izin akses staff.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Role Sistem (Bawaan, Tidak Bisa Diubah)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Deskripsi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Jumlah Izin
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="role in systemRoles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.description || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.permissions_count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
```

- [ ] **Step 2: Verify in the browser**

With the dev server running, log in as `admin@konveksi.com` / `password`, click "Role & Izin" in the sidebar. Confirm:
- The system-role table lists the seeded roles (Tenant Admin, Manager, Production Staff, etc.) with correct permission counts, no Edit/Hapus buttons.
- Any custom role created while testing Task 5 appears in the top table with working Edit/Hapus.
- Deleting a custom role that's assigned to a staff member shows the red "Role tidak bisa dihapus..." flash and the row stays.
- Deleting an unused custom role removes it and shows a green success flash.

- [ ] **Step 3: Run the full backend test suite**

Run: `php artisan test --compact`
Expected: PASS (no regressions in `RoleControllerTest`, `StaffTest`, or elsewhere)

- [ ] **Step 4: Lint**

Run: `npm run lint && vendor/bin/pint --dirty --format agent`
Expected: no errors (Pint may auto-fix formatting — re-check `git diff` if so)

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/Roles/Index.vue
git commit -m "feat(role): add role list page with system/custom sections"
```

---

## Self-Review Notes

- **Spec coverage:** index/create/store/edit/update/destroy (Tasks 1-3), StaffController bug fix (Task 4), Roles/Form.vue + Sidebar (Task 5), Roles/Index.vue (Task 6), tenant-module permission rejection (Task 1 store test + Task 2 request), delete-in-use guard (Task 3), system-role/cross-tenant 404 (Task 2 + 3), duplicate-name slug suffix (Task 1) — all spec sections have a task.
- **Deviation from spec doc:** the design spec mentioned importing generated Wayfinder actions "the same way `Staff/Form.vue` imports `StaffController` actions" — checked the actual file and `Staff/Form.vue` posts to plain string URLs (`form.post('/staff', ...)`), it does not use Wayfinder. Task 5/6 follow the real pattern (plain URL strings) instead, since that's what's actually consistent in this codebase.
- **Type consistency:** `permissions_count`/`staff_members_count` (controller `withCount` output) match the `RoleRow` interface field names in `Roles/Index.vue`; `permission_ids` (Form Request key) matches the `useForm()` field name in `Roles/Form.vue`; route names `roles.index`/`roles.create`/`roles.store`/`roles.edit`/`roles.update`/`roles.destroy` are consistent across all controller tests and the resource route registered in Task 1.
