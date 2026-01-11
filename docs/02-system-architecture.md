# System Architecture - Fabriku

## Architecture Overview

Fabriku menggunakan arsitektur modern berbasis Laravel 12 dengan Inertia.js dan Vue 3, dirancang sebagai aplikasi SaaS multi-tenant yang scalable dan maintainable.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Client Layer                          │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Vue 3 + Inertia.js (SPA)                          │   │
│  │  - Tailwind CSS 4                                   │   │
│  │  - Wayfinder (Type-safe routing)                   │   │
│  │  - Pinia (State management - jika diperlukan)      │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↕ HTTPS
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Laravel 12 (PHP 8.4)                              │   │
│  │  - Controllers (Request handling)                   │   │
│  │  - Form Requests (Validation)                      │   │
│  │  - Services (Business logic)                       │   │
│  │  - Jobs (Async processing)                         │   │
│  │  - Events & Listeners                              │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                      Domain Layer                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  - Models (Eloquent ORM)                           │   │
│  │  - Repositories (Data access patterns)             │   │
│  │  - Policies (Authorization)                        │   │
│  │  - Value Objects                                   │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  PostgreSQL  │  │    Redis     │  │   Storage    │     │
│  │  (Primary)   │  │   (Cache)    │  │   (Files)    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Frontend Architecture

**Technology Stack**:
- Vue 3 (Composition API)
- Inertia.js v2 (SSR-capable)
- Tailwind CSS 4 (Utility-first CSS)
- TypeScript (Type safety)
- Wayfinder (Type-safe routing)

**Component Structure**:
```
resources/js/
├── pages/              # Inertia pages
│   ├── Dashboard.vue
│   ├── Materials/      # Bahan baku
│   ├── Cutting/        # Pemotongan
│   ├── Production/     # Produksi jahit
│   ├── Inventory/      # Gudang produk jadi
│   ├── Sales/          # Penjualan
│   └── Reports/        # Laporan
├── components/         # Reusable components
│   ├── layouts/
│   ├── forms/
│   ├── tables/
│   └── charts/
├── composables/        # Vue composables
├── types/              # TypeScript types
└── lib/                # Utilities & helpers
```

### 2. Backend Architecture

**Layered Architecture**:

```
app/
├── Http/
│   ├── Controllers/           # Request handlers
│   │   ├── MaterialController.php
│   │   ├── CuttingController.php
│   │   ├── ProductionController.php
│   │   ├── InventoryController.php
│   │   └── SalesController.php
│   ├── Requests/              # Form validation
│   └── Middleware/            # Request filtering
├── Services/                  # Business logic
│   ├── MaterialService.php
│   ├── CuttingService.php
│   ├── ProductionService.php
│   ├── InventoryService.php
│   ├── SalesService.php
│   └── ReportService.php
├── Models/                    # Eloquent models
├── Policies/                  # Authorization
├── Events/                    # Domain events
├── Listeners/                 # Event handlers
├── Jobs/                      # Async tasks
└── Observers/                 # Model observers
```

### 3. Database Architecture

**Database**: PostgreSQL (recommended untuk SaaS multi-tenant)

**Multi-Tenancy Strategy**: Database per tenant (isolated data)

**Core Tables**:
1. **tenants** - Tenant/organization information
2. **users** - User accounts
3. **materials** - Bahan baku (kain)
4. **material_receipts** - Penerimaan bahan baku
5. **patterns** - Pola/template produk
6. **cutting_orders** - Order pemotongan
7. **cutting_results** - Hasil pemotongan
8. **production_orders** - Order produksi jahit
9. **production_batches** - Batch produksi
10. **contractors** - Pihak ketiga (kontraktor jahit)
11. **inventory_items** - Produk jadi dalam gudang
12. **inventory_locations** - Lokasi penyimpanan (rak)
13. **sales_orders** - Order penjualan
14. **sales_items** - Item dalam order
15. **customers** - Data pelanggan
16. **reports** - Generated reports cache

## Key Design Patterns

### 1. Service Layer Pattern
Memisahkan business logic dari controller untuk:
- Reusability
- Testability
- Single Responsibility

```php
// Example
class MaterialService
{
    public function receiveMaterial(array $data): MaterialReceipt
    {
        // Complex business logic here
        // Validation, calculations, events
    }
}
```

### 2. Repository Pattern (Optional)
Untuk query kompleks atau jika perlu abstraksi data layer:
```php
interface MaterialRepositoryInterface
{
    public function findLowStock(int $threshold): Collection;
}
```

### 3. Observer Pattern
Untuk automated actions setelah model events:
```php
class InventoryObserver
{
    public function updated(InventoryItem $item): void
    {
        // Trigger low stock alert
        if ($item->quantity <= $item->reorder_point) {
            event(new LowStockAlert($item));
        }
    }
}
```

### 4. Job Queue Pattern
Untuk operasi yang membutuhkan waktu lama:
```php
// Generate complex reports
GenerateMonthlyReportJob::dispatch($tenantId, $month);

// Send notifications
SendLowStockNotificationJob::dispatch($items);
```

## Security Architecture

### Multi-Tenancy Security
- Tenant context di setiap request (middleware)
- Row-level security via global scopes
- Tenant isolation di database level

```php
// Global scope untuk tenant isolation
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->where('tenant_id', auth()->user()->tenant_id);
        }
    }
}
```

### Authentication & Authorization
- Laravel Sanctum untuk API authentication
- Policies untuk authorization
- Role-based access control (RBAC)

**Roles**:
- Super Admin (platform management)
- Tenant Admin (organization owner)
- Manager (full access within tenant)
- Staff Production
- Staff Warehouse
- Staff Sales
- Viewer (read-only)

## Performance Optimization

### Caching Strategy
```php
// Cache configuration
Cache::tags(['tenant:' . $tenantId, 'inventory'])
    ->remember('inventory.summary', 3600, function () {
        return $this->getInventorySummary();
    });
```

### Database Optimization
- Proper indexing (tenant_id, foreign keys, search columns)
- Eager loading untuk N+1 prevention
- Database partitioning untuk large tables (by tenant/date)

### Queue System
- Redis untuk queue driver (production)
- Async processing untuk:
  - Report generation
  - Email notifications
  - Data import/export
  - Batch operations

## Scalability Considerations

### Horizontal Scaling
- Stateless application servers
- Load balancer (Nginx/HAProxy)
- Session storage di Redis
- File storage di S3/Minio

### Vertical Scaling
- Database connection pooling
- Query optimization
- Caching aggressive untuk read-heavy operations

### Monitoring & Logging
- Laravel Telescope (development)
- Application logs (Monolog)
- Performance monitoring (New Relic/DataDog - optional)
- Error tracking (Sentry/Bugsnag - optional)

## Deployment Architecture

### Development Environment
```
Docker Compose
├── PHP 8.4 (Laravel)
├── PostgreSQL
├── Redis
├── Node.js (Vite)
└── Mailpit (email testing)
```

### Production Environment (Recommended)
```
Cloud Provider (AWS/GCP/DigitalOcean)
├── Application Servers (Load balanced)
├── Database (Managed PostgreSQL)
├── Cache (Managed Redis)
├── Storage (S3/Spaces)
├── Queue Workers
└── Scheduler (Laravel Cron)
```

## API Architecture

### RESTful Principles
- Resource-based URLs
- HTTP verbs (GET, POST, PUT, PATCH, DELETE)
- Proper status codes
- JSON response format

### API Versioning
```
/api/v1/materials
/api/v1/inventory
```

### Rate Limiting
- Per tenant rate limiting
- Different limits per role
- Configurable via environment

## Testing Strategy

### Test Pyramid
```
     ┌────────┐
    /  E2E     \     Browser tests (Pest 4)
   /────────────\
  /  Integration \   Feature tests (Pest 4)
 /────────────────\
/      Unit         \ Unit tests (Pest 4)
────────────────────
```

**Test Coverage Target**: > 80%

### Testing Tools
- Pest 4 (testing framework)
- Browser testing (Pest 4 built-in)
- Laravel Factories untuk test data
- Database transactions untuk test isolation

## Development Workflow

### Git Workflow
- `main` - Production
- `develop` - Development
- `feature/*` - Feature branches
- `hotfix/*` - Urgent fixes

### CI/CD Pipeline
1. Code push
2. Run tests (Pest)
3. Run linter (Pint)
4. Build assets (Vite)
5. Deploy (if tests pass)

### Code Quality
- Laravel Pint (code formatting)
- PHPStan (static analysis - optional)
- ESLint + Prettier (JavaScript/TypeScript)
- Pre-commit hooks

## Documentation
- Inline PHPDoc blocks
- API documentation (Swagger/OpenAPI - optional)
- User documentation (separate)
- Development setup guide
