---
description: Guidelines and instructions for Antigravity/Copilot when working on Fabriku workspace
---

# Antigravity & Copilot Instructions - Fabriku

**Last Updated**: 25 January 2026  
**Project Status**: Phase 8/8 - Polish & Testing (MVP Feature Complete)

---

## 🚀 Project Overview

**Fabriku** is a multi-tenant SaaS platform for SME manufacturing management, supporting multiple business categories (Garment, Food, Craft, Cosmetics) with a unified workflow:

```
Raw Materials → Pattern/Recipe → Preparation → Production → Inventory → Sales
```

### Current Implementation Status
| Phase | Feature | Status |
|-------|---------|--------|
| 1 | Foundation (Multi-tenancy, Auth) | ✅ Complete |
| 2 | Material Management | ✅ Complete |
| 3 | Pattern & Preparation | ✅ Complete |
| 4 | Production Management | ✅ Complete |
| 5 | Inventory Management | ✅ Complete |
| 6 | Sales Management | ✅ Complete |
| 7 | Dashboard & Reports | ✅ Complete |
| 8 | Polish & Testing | 🔄 In Progress |

---

## 🛠 Technology Stack (STRICT)

### Backend
- **Framework**: Laravel 12 (PHP 8.4)
- **Database**: PostgreSQL (single DB, row-level tenant isolation)
- **Testing**: Pest v4
- **Code Style**: Laravel Pint

### Frontend
- **Framework**: Vue 3.5 (Composition API with `<script setup>`)
- **SPA**: Inertia.js v2
- **Styling**: Tailwind CSS v4 (Utility-first)
- **Type Safety**: TypeScript 5.2
- **Routing**: Laravel Wayfinder (type-safe)
- **Icons**: Lucide Vue Next (`lucide-vue-next`)
- **Build**: Vite 7

---

## 📐 Architecture Rules

### 1. Multi-Tenancy (CRITICAL)
- **Isolation**: Single database with `tenant_id` column on all tenant-scoped tables.
- **Scope**: ALWAYS apply tenant filtering. Models use `TenantScope`.
- **Middleware**: Tenant context handled by `tenant` middleware.
- **NEVER** query tenant-scoped data without `tenant_id` filter.

### 2. Frontend Architecture

#### Layout System
- **Always** use `AppLayout.vue` as the wrapper for authenticated pages.
- **Components**:
  | Component | Purpose |
  |-----------|---------|
  | `Navbar.vue` | Fixed top bar, theme toggle, user menu |
  | `Sidebar.vue` | Navigation with submenus, drawer on mobile, panel on desktop |
  | `PageHeader.vue` | Page title with optional action buttons |
  | `Footer.vue` | Copyright info |

#### Mobile-First Responsive Design
- **START** with mobile styles (no breakpoint prefix).
- **Progressively enhance** for larger screens using `sm:`, `md:`, `lg:`.
- **Breakpoints**: 
  - `sm:` = 640px
  - `md:` = 768px
  - `lg:` = 1024px
- **Touch targets**: Minimum 44x44px for interactive elements.

#### Dark Mode
- Use custom `useDarkMode` composable (NOT VueUse default).
- Storage key: `localStorage['fabriku-theme']`.
- **ALL components MUST support `dark:` variant classes.**
- Colors: See `docs/07-frontend-ui-architecture.md` for palette guidelines.

### 3. Database & Business Logic

#### Material System
- `materials` table stores bahan baku with `current_stock` field.
- `material_attributes` table for category-specific attributes (key-value pairs).
- Stock tracking is automatic via Observers.

#### Preparation Orders (Simplified)
- **NO BOM table** (`pattern_materials` was removed).
- **NO `cutting_results` table** (was removed).
- Materials used stored as **JSONB** in `preparation_orders.materials_used`.
- Pattern is **optional** reference only.
- **Auto stock deduction**: Handled by `PreparationOrderObserver` when status = `completed`.

#### Production Orders
- Links to `preparation_order_id` (optional FK).
- Types: `internal` or `external` (contractor).
- Status workflow: `draft` → `pending` → `in_progress` → `completed`.

#### Inventory Items
- Links to production batches and patterns.
- SKU generation automatic.
- Tracks: `initial_quantity`, `current_quantity`, `reserved_quantity`.

#### Sales Orders
- Links to customers.
- Status workflow: `pending` → `confirmed` → `packed` → `shipped` → `delivered`.
- Payment status: `unpaid`, `partial`, `paid`.

---

## 📝 Coding Standards

### PHP/Laravel
- **Naming**: `snake_case` for database columns, methods; `PascalCase` for classes.
- **Validation**: Use FormRequest classes for all store/update operations.
- **Testing**: Write Feature tests (Pest) for all new controller actions.

### TypeScript/Vue
- **Naming**: `camelCase` for variables/functions; `PascalCase` for components.
- **Props**: Define TypeScript interfaces for all component props.
- **Icons**: Import from `lucide-vue-next`.

### Styling
- Use Tailwind utility classes exclusively.
- **NO hardcoded colors** - use Tailwind palette (gray, indigo, red, green, etc.).
- **NO inline styles**.

---

## ⚠️ Critical Rules (DO NOT VIOLATE)

### ❌ DO NOT
1. Create `pattern_materials` or any BOM table (removed in refactor).
2. Create `cutting_results` table (removed in refactor).
3. Forget `tenant_id` in queries for tenant-scoped data.
4. Use hardcoded colors or inline styles.
5. Skip dark mode support on new components.
6. Manually deduct stock (Observers handle it).
7. Assume pattern is required (it's optional).

### ✅ DO
1. Check stock availability server-side before completing preparation orders.
2. Use `materials_used` JSONB column for material tracking.
3. Test with and without pattern references.
4. Follow mobile-first responsive design.
5. Write Pest feature tests for backend logic.
6. Use TypeScript interfaces for type safety.

---

## 📁 Key File Locations

### Backend
```
app/
├── Http/Controllers/    # 15 controllers (+ Auth)
├── Models/              # 16 models + Scopes
├── Observers/           # Stock deduction observers
├── Services/            # Business logic services
└── Http/Requests/       # Form validation

routes/web.php           # All web routes
database/migrations/     # 10 migration files
```

### Frontend
```
resources/js/
├── layouts/AppLayout.vue     # Main layout
├── components/               # Navbar, Sidebar, PageHeader, etc.
├── pages/                    # 12 page directories + Dashboard, Welcome
├── composables/              # useDarkMode, etc.
├── types/                    # TypeScript interfaces
└── lib/utils.ts              # Utility functions
```

### Tests
```
tests/
├── Feature/          # 14 feature test files
├── Browser/          # Browser tests
└── Unit/             # Unit tests
```

---

## 📚 Reference Documentation

| Document | Purpose |
|----------|---------|
| `docs/README.md` | Project overview & progress |
| `docs/02-system-architecture.md` | Technical architecture |
| `docs/03-database-schema.md` | Database design & ERD |
| `docs/07-frontend-ui-architecture.md` | UI/UX guidelines, dark mode, responsive |
| `docs/08-user-manual.md` | Panduan lengkap cara menggunakan aplikasi |
| `docs/09-workflow-summary.md` | Business workflow & data flow |
| `docs/refactoring-preparation-simplification.md` | BOM removal explanation |

---

## 🔧 Common Commands

```bash
# Development
npm run dev              # Start Vite dev server
php artisan serve        # Start Laravel server

# Testing
php artisan test         # Run all tests
php artisan test --filter=MaterialTest  # Run specific test

# Code Quality
vendor/bin/pint          # Fix PHP code style
npm run lint             # Lint JavaScript/TypeScript
npm run format           # Format with Prettier

# Database
php artisan migrate:fresh --seed  # Reset database with seeds
```

---

## 🏷️ Sidebar Menu Structure

The sidebar (`Sidebar.vue`) has the following navigation structure:

1. **Dashboard** - `/dashboard`
2. **Master Data** (submenu)
   - Jenis Bahan - `/material-types`
   - Staff - `/staff`
   - Lokasi Inventory - `/inventory/locations`
   - Customer - `/customers`
   - Pattern - `/patterns`
   - Kontraktor - `/contractors`
3. **Bahan Baku** - `/materials`
4. **Preparation** - `/preparation-orders`
5. **Production Order** - `/production-orders`
6. **Inventory Items** - `/inventory/items`
7. **Sales Order** - `/sales-orders`
8. **Reports** (submenu)
   - Material - `/reports/material`
   - Inventory - `/reports/inventory`
   - Penjualan - `/reports/sales`
   - Produksi - `/reports/production`

---

**Remember**: When in doubt, check the docs folder. The documentation is comprehensive and up-to-date.
