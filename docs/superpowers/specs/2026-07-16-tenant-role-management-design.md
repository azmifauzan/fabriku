# Tenant Role Management — Design Spec

Date: 2026-07-16

## Problem

Staff create/edit form lets an admin pick a `role_id`, but there is no tenant-facing UI to see what a role actually grants, or to create/edit/delete roles scoped to the tenant. Role management exists today only in the super-admin panel (`AdminRoleController`, `/admin/roles`), which manages **global** roles (`tenant_id IS NULL`) shared across every tenant — tenant admins cannot reach it and should not be able to mutate roles that affect other tenants.

Additionally, while investigating, found the role dropdown in `StaffController::create()`/`edit()` is likely always empty for any tenant user:

```php
Role::whereNull('tenant_id')->where('is_system_role', true)...
```

`Role` has a global scope (`App\Models\Scopes\TenantScope`) that unconditionally adds `WHERE tenant_id = <current tenant>` whenever an authenticated user with a `tenant_id` runs a query. Combined with the explicit `whereNull('tenant_id')`, the resulting query is `WHERE tenant_id = X AND tenant_id IS NULL`, which can never match a row. This fix is bundled into this spec since it touches the same query being extended to include custom roles.

## Scope

Full CRUD for tenant-scoped custom roles (`roles.tenant_id = current tenant`), built on top of the existing `roles` / `permissions` / `role_permissions` schema (already supports `tenant_id` nullable with a `unique(tenant_id, slug)` constraint — this feature was schema-ready but never had a controller/UI).

Global system roles (`tenant_id IS NULL`, e.g. Manager, Sales Staff, Tenant Admin) remain read-only from the tenant side — visible in the list and selectable on staff, but not editable/deletable by tenant admins. Only super-admin (`/admin/roles`) can touch those.

Out of scope: editing permissions of existing system roles from the tenant side, role duplication/cloning, a `show` detail page (index list carries enough info), permission-based route gating (this reuses the existing `isAdmin()`-in-controller pattern already used by `StaffController`, not the `permission:<slug>` middleware layer).

## Backend

### `App\Http\Controllers\RoleController` (new, tenant-scoped)

Route: `Route::resource('roles', RoleController::class)->except(['show'])` inside the existing tenant middleware group (`auth`, `verified`, `tenant`, `subscription.check`) in `routes/web.php`, next to `Route::resource('staff', StaffController::class)`.

Every method starts with the same gate `StaffController` uses:

```php
if (! auth()->user()->isAdmin()) {
    abort(403, 'Hanya admin yang dapat mengelola role.');
}
```

- **index()** — Returns two sets:
  - System roles: `Role::withoutGlobalScope(TenantScope::class)->whereNull('tenant_id')->where('is_system_role', true)->withCount('permissions')->get()` (no per-tenant staff count needed/meaningful across tenants — omit `staff_count` for this set, or compute a tenant-scoped staff count separately if trivial; simplest: omit and rely on the "read-only" label to signal it's shared).
  - Custom roles: `Role::where('is_system_role', false)->withCount('permissions')->get()` — `TenantScope` already restricts this to the current tenant. Add staff count via `withCount(['staffMembers'])` (see model change below) or a `Staff::where('role_id', $role->id)->count()` map.
  - Render `Inertia::render('Roles/Index', ['systemRoles' => ..., 'customRoles' => ...])`.

- **create()** — `Permission::where('module', '!=', 'tenant')->orderBy('module')->get()->groupBy('module')`, render `Roles/Form`.

- **store(StoreRoleRequest $request)** —
  - `slug = Str::slug($validated['name'])`; if a role with that slug already exists for this tenant, append `-2`, `-3`, etc. until unique (mirrors how `sku`/`code` collision handling is done elsewhere in the codebase per per-tenant-unique convention).
  - `Role::create(['tenant_id' => auth()->user()->tenant_id, 'name' => ..., 'description' => ..., 'is_system_role' => false])`.
  - `$role->permissions()->sync($validated['permission_ids'])`.
  - Redirect to `roles.index` with success flash.

- **edit(Role $role)** — Implicit route-model binding runs through `Role`'s global `TenantScope`, so a system role (`tenant_id IS NULL`) or another tenant's custom role 404s automatically before the method body runs — no manual ownership check needed. Load `$role->load('permissions')`, permissions grouped by module (excluding `tenant`), render `Roles/Form`.

- **update(UpdateRoleRequest $request, Role $role)** — Same 404-by-scope protection. Update `name`/`description` only; slug stays immutable after creation (avoids invalidating anything that might reference the slug). Re-`sync` permissions.

- **destroy(Role $role)** — Same 404-by-scope protection (can't delete a system role or another tenant's role via this route). Block deletion if in use:
  ```php
  if (Staff::where('role_id', $role->id)->exists() || $role->users()->exists()) {
      return back()->with('error', 'Role tidak bisa dihapus karena masih digunakan.');
  }
  ```
  Then `$role->delete()` (cascades `role_permissions` rows via existing FK).

### Form Requests

`StoreRoleRequest` / `UpdateRoleRequest` (per CLAUDE.md convention — Form Requests, not inline `$request->validate()`):

```php
'name' => ['required', 'string', 'max:100'],
'description' => ['nullable', 'string'],
'permission_ids' => ['required', 'array', 'min:1'],
'permission_ids.*' => [
    'integer',
    Rule::exists('permissions', 'id')->where(fn ($q) => $q->where('module', '!=', 'tenant')),
],
```

The `where('module', '!=', 'tenant')` clause is the server-side enforcement that a tampered request can't smuggle in a platform-only permission id — the `create()`/`edit()` UI never renders those checkboxes, but the request must reject them independently.

`authorize()` returns `$this->user() !== null` (existing project pattern) since the real gate is the `isAdmin()` check in the controller, matching `StaffController`.

### `StaffController::create()` / `StaffController::edit()` — bug fix + custom roles

Replace the current single (broken) query with:

```php
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

(`$customRoles` stays correctly tenant-scoped via `TenantScope`, unaffected by this change.)

### Model change

`App\Models\Role` gets a `staffMembers()` relation for the index staff-count:

```php
public function staffMembers(): HasMany
{
    return $this->hasMany(Staff::class);
}
```

## Frontend

- `resources/js/pages/Roles/Index.vue` — two sections or one table with a "Sistem" badge column. Columns: Nama, Deskripsi, Jumlah Izin, Jumlah Staff (custom roles only). Row actions: Edit/Hapus enabled only for custom roles (`!is_system_role`); system rows show badge only, no actions. "+ Tambah Role" button linking to `roles.create`. Delete uses existing `useSweetAlert` confirm pattern (matches `Staff/Index.vue`).

- `resources/js/pages/Roles/Form.vue` — shared create/edit form (mirrors `Staff/Form.vue` structure: `useForm()`, `watch()` re-sync if needed for edit-prop reloads). Fields: Nama (text), Deskripsi (textarea), and a permission checklist grouped by module with a section heading per module (Material, Pattern, Preparation, Production, Inventory, Sales, Purchase, Service, Report, Settings, Audit, Role, User — whatever modules exist minus `tenant`). Plain checkboxes, no "select all" control (module count is small enough not to need it).

- `resources/js/components/Sidebar.vue` — add to `masterDataChildren` (line ~72), right after the `Staff` entry:
  ```js
  { name: 'Role & Izin', href: '/roles', permission: null, adminOnly: true },
  ```

- Run `php artisan wayfinder:generate` after the controller/routes exist; import generated actions in the new Vue pages the same way `Staff/Form.vue` imports `StaffController` actions.

## Edge Cases / Validation Summary

| Case | Behavior |
|---|---|
| Non-admin hits any `roles.*` route | 403 (`isAdmin()` check) |
| Edit/update/delete a system role (`tenant_id` null) via tenant route | 404 (route-model binding scoped out by `TenantScope`) |
| Edit/update/delete another tenant's custom role | 404 (same scoping) |
| `permission_ids` includes a `module = 'tenant'` permission | 422 (Form Request rule) |
| Delete a custom role still assigned to `Staff.role_id` or any `User` via `user_roles` | Rejected with flash error, role not deleted |
| Duplicate role name within the same tenant | Allowed (name isn't unique), but slug auto-suffixes (`-2`, `-3`, ...) to satisfy `unique(tenant_id, slug)` |
| Staff create/edit form role dropdown | Now shows system roles (bug fixed) + tenant's custom roles, sorted by name |

## Testing

`tests/Feature/RoleControllerTest.php` (new):
- Tenant admin can create a custom role with a subset of non-`tenant` permissions.
- Tenant admin can edit a custom role's name/description/permissions.
- Tenant admin can delete an unused custom role.
- Non-admin gets 403 on index/create/store/edit/update/destroy.
- GET `/roles/{system_role}/edit` → 404.
- GET `/roles/{other_tenant_role}/edit` → 404.
- DELETE a role assigned to a `Staff` row → rejected, role still exists.
- POST with a `tenant.*` permission id in `permission_ids` → 422.
- Two tenants can each create a role with the same `name` (and even same slugified name) without conflict.

`tests/Feature/StaffTest.php` (extend):
- `roles` prop on `Staff/Form` create/edit response includes the seeded system roles (proves the `TenantScope`/`whereNull` bug is fixed — previously this list was empty).
- A tenant's custom role appears in that same `roles` prop and can be assigned to a new staff member end-to-end.
