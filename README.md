# Fabriku

> **Platform SaaS Multi-Kategori untuk Manajemen Produksi & Penjualan UMKM**  
> **Status**: ✅ Production Ready | **Last Updated**: February 10, 2026

Fabriku adalah aplikasi berbasis web yang dirancang untuk membantu UMKM dalam mengelola seluruh proses bisnis mereka dari berbagai kategori industri. Saat ini mendukung **Garment & Konveksi** dan **Makanan & Kue**, dengan rencana ekspansi ke kategori lain (Kerajinan, Kosmetik, dll).

Platform ini mengelola workflow universal: **Bahan Baku → Pattern/Resep → Persiapan → Produksi → Inventory → Penjualan**, dengan terminologi dan business rules yang disesuaikan per kategori bisnis.

**🎯 Current Version**: v1.0.0  
**📊 Test Coverage**: 100+ integration & feature tests  
**🏢 Multi-Tenant Support**: Full tenant isolation & security

[![Laravel](https://img.shields.io/badge/Laravel-12.47-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4.11-777BB4?style=flat&logo=php)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat&logo=vue.js)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.3-9553E9?style=flat)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.1-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql)](https://postgresql.org)
[![Pest](https://img.shields.io/badge/Pest-4.3-f472b6?style=flat)](https://pestphp.com)

## ✨ Fitur Utama

### 🎯 Multi-Kategori Bisnis
- **Garment & Konveksi**: Pattern, Cutting, Sewing (Mukena, Daster, Gamis, dll)
- **Makanan & Kue**: Resep, Mixing/Prep, Baking (Cake, Brownies, Cookies, dll)
- **Coming Soon**: Kerajinan, Kosmetik, dan kategori lainnya
- Terminologi dan business rules disesuaikan per kategori
- Extensible architecture untuk menambah kategori baru

### 📦 Manajemen Bahan Baku (Multi-Kategori)
- Pencatatan penerimaan bahan dengan atribut dinamis:
  - **Garment**: warna, lebar kain, gramasi, batch number
  - **Makanan**: expired date, storage temp, batch number, halal certified
- Tracking batch dan stok real-time dengan FIFO/FEFO
- Alert untuk reorder point dan expired date (makanan)
- Material attributes yang fleksibel per kategori

### 📋 Pattern/Resep Management
- **Pattern Library** (Garment): Pola dengan ukuran dan spesifikasi
- **Recipe Library** (Makanan): Resep dengan serving size dan output
- Pattern/Recipe sebagai referensi untuk preparation (tidak wajib)
- Support untuk berbagai product types per kategori
- Estimasi kebutuhan material (opsional)

### ✂️ Proses Persiapan (Simplified - Multi-Kategori)
- **Preparation Orders** (Garment: Cutting | Makanan: Mixing/Prep)
- Manual material usage input (flexible & practical)
- Pattern/Recipe sebagai referensi (optional)
- Output quantity tracking
- **Auto deduct stock** saat status completed
- Status workflow dengan guards (draft, in_progress, completed, cancelled)

### 🧵 Manajemen Produksi
- Support produksi internal dan outsourcing:
  - **Garment**: Penjahit/kontraktor jahit
  - **Makanan**: Dapur sharing/outsource produksi
- Tracking progress dan timeline produksi
- Quality control:
  - **Garment**: Grade A/B/Reject
  - **Makanan**: Premium/Standar, expired date tracking
- Rating dan evaluasi kontraktor/partner
- Status workflow: draft → pending → in_progress → completed

### 📊 Inventory Produk Jadi
- Manajemen lokasi penyimpanan (rak)
- Tracking dengan SKU generation
- Stock quantity: initial, current, reserved
- **Makanan**: Expired date monitoring dan shelf life alerts
- **Garment**: Batch tracking dan quality grades
- Status management: available, reserved, depleted

### 💰 Manajemen Penjualan
- Multi-channel sales (offline, online, marketplace, reseller)
- Customer relationship management (retail, wholesale, reseller)
- Payment tracking (unpaid, partial, paid)
- Order status workflow (pending → confirmed → packed → shipped → delivered)
- Stock integration dengan inventory

### 📈 Pelaporan & Analytics
- Dashboard dengan real-time KPI
- Laporan bahan baku (Material Report)
- Laporan inventory (Inventory Report)
- Laporan penjualan (Sales Report)
- Laporan produksi (Production Report)
- Export ke PDF & Excel

### 🤖 AI Assistant (Fabriku Assistant)
- Chat assistant berbasis OpenAI GPT
- Natural language query untuk data bisnis
- Multi-channel support (Web, Telegram)
- Conversation history & context awareness
- Usage tracking per tenant
- Smart suggestions & proactive alerts

### 📱 Telegram Integration
- Telegram Bot untuk notifikasi
- Connect account via QR code atau token
- Real-time notifications untuk admin
- Support perintah bisnis via chat

### 📧 Email System
- Custom email templates (Bahasa Indonesia)
- Email verification dengan design modern
- Welcome email untuk user baru
- Reset password flow
- Trial reminder emails (7 hari, 3 hari, 1 hari)
- Email logging & tracking

### 🔐 Admin Panel (Platform Management)
- **Super Admin Dashboard**: Platform-wide statistics and tenant overview
- **Tenant Management**: Complete CRUD for managing tenants
  - Create tenants with admin user
  - View detailed tenant statistics
  - Suspend/activate tenants
  - Subscription management
- **User Management**: Cross-tenant user administration
  - Create and manage users
  - Assign roles and permissions
  - User activity tracking
- **Role & Permission System**: Full RBAC implementation
  - Create custom roles
  - Assign granular permissions
  - System role protection
  - Permission grouped by modules
- **Audit Logs**: Complete activity tracking
  - View all system activities
  - Filter by date, event type, model
  - Detailed change comparison (old vs new values)
- **Secure Authentication**: Separate admin guard with enhanced security

## 🏗️ Technology Stack

### Backend
- **Framework**: Laravel 12.47.0
- **Language**: PHP 8.4.11
- **Database**: PostgreSQL 16 (recommended) / MySQL 8.0+
- **Cache**: Redis 7
- **Queue**: Redis
- **Testing**: Pest 4.3.1 (with Browser Testing)
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel 3.1
- **AI Integration**: OpenAI API
- **Notifications**: Telegram Bot SDK

### Frontend
- **Framework**: Vue 3.5.18 (Composition API with `<script setup>`)
- **SSR**: Inertia.js v2.3.7
- **Styling**: Tailwind CSS 4.1.11
- **Type Safety**: TypeScript 5.2.2
- **Routing**: Laravel Wayfinder 0.1.13
- **Build Tool**: Vite 7
- **Icons**: Lucide Vue Next 0.562
- **Utilities**: VueUse Core 12.8
- **Alerts**: SweetAlert2

### Development Tools
- **Code Style**: Laravel Pint 1.27
- **Linting**: ESLint 9.32 + Prettier 3.6
- **Version Control**: Git
- **Debug**: Laravel Pail, Laravel Boost MCP

## 📋 Prerequisites

- PHP 8.4 or higher
- Composer 2.x
- Node.js 18.x or higher
- npm or yarn
- PostgreSQL 14+ / MySQL 8.0+
- Redis 6.x+

## 🚀 Getting Started

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/fabriku.git
cd fabriku
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fabriku
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 5. Run Migrations

```bash
# Run database migrations
php artisan migrate

# Seed database (optional, untuk development)
php artisan db:seed

# Seed akan membuat 4 demo tenants dengan data lengkap:
# 1. Konveksi Fabriku (Garment) - admin@konveksi.com / password
# 2. Kue Mama Homemade (Food) - admin@kuemama.com / password
# 3. Crafty Handmade (Craft) - admin@crafty.com / password
# 4. Glow Beauty Lab (Cosmetic) - admin@glowbeauty.com / password
```

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite dev server
npm run dev

# In another terminal, start queue worker (if needed)
php artisan queue:work

# In another terminal, start scheduler (for demo auto-reset)
php artisan schedule:work
```

Visit: http://localhost:8000

**Demo Credentials (Tenant Users):**
- Konveksi Fabriku: `admin@konveksi.com` / `password`
- Kue Mama Homemade: `admin@kuemama.com` / `password`
- Crafty Handmade: `admin@crafty.com` / `password`
- Glow Beauty Lab: `admin@glowbeauty.com` / `password`

**Admin Panel Access:**
- URL: http://localhost:8000/admin/login
- Super Admin: `admin@fabriku.com` / `password`

### 🔄 Demo Data Management

Demo tenants are automatically reset every hour to maintain clean environments. The reset command now properly reseeds data to match the initial state.

**Manual Reset Commands:**
```bash
# Reset all demo tenants and reseed with default data
php artisan demo:reset

# Reset specific tenant (by ID or name pattern)
php artisan demo:reset --tenant=1
php artisan demo:reset --tenant="Konveksi"

# Reset without reseeding (just clear data)
php artisan demo:reset --no-reseed

# Recalculate material stock (if needed)
php artisan material:recalculate-stock

# Send trial reminder emails
php artisan trial:send-reminders
```

**Production Setup (Cron):**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🐳 Docker Setup

### Option 1: Production (External PostgreSQL & Redis)

Single container yang menjalankan Apache, Queue Worker, dan Scheduler sekaligus untuk hemat resource.

**Prerequisites:**
- PostgreSQL 14+ on host (port 5432)
- Redis 6+ on host (port 6379)

```bash
# 1. Create PostgreSQL database
psql -U postgres -c "CREATE DATABASE fabriku;"
psql -U postgres -c "CREATE USER fabriku WITH PASSWORD 'secret';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE fabriku TO fabriku;"

# 2. Copy environment file
cp .env.docker.example .env

# 3. Update .env with your PostgreSQL and Redis hosts
# DB_HOST=localhost
# REDIS_HOST=localhost

# 4. Generate key, build, and start
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate --seed
```

**Single Container Runs:**
- ✅ Apache (Web Server)
- ✅ Cron (Laravel Scheduler - hourly demo reset)
- ✅ Queue Worker (Background jobs)

All managed by Supervisor in one container.

### Option 2: Development (All in Docker)

Single app container + PostgreSQL + Redis untuk local development.

```bash
# 1. Copy environment file
cp .env.dev.example .env

# 2. Generate key, build, and start
docker compose -f docker-compose.dev.yml run --rm app php artisan key:generate
docker compose -f docker-compose.dev.yml up -d

# 3. Wait and migrate
sleep 10
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

**Services:**
- ✅ App Container (Apache + Cron + Queue in one)
- ✅ PostgreSQL 16 (separate container)
- ✅ Redis 7 (separate container)

**Access:**
- Application: http://localhost:8000
- PostgreSQL: localhost:5432 (fabriku/secret)
- Redis: localhost:6379

### 🛠️ Docker Helper Scripts

**Windows:**
```bash
docker.bat setup          # Initial setup (build, start, migrate)
docker.bat start          # Start containers
docker.bat stop           # Stop containers
docker.bat logs           # View all logs
docker.bat logs app       # View specific service logs
docker.bat artisan migrate # Run artisan commands
docker.bat shell          # Access app container shell
docker.bat demo-reset     # Reset demo data
docker.bat help           # Show all commands
```

**Linux/Mac:**
```bash
chmod +x docker.sh        # Make executable
./docker.sh setup         # Initial setup
./docker.sh start         # Start containers
./docker.sh artisan migrate
./docker.sh demo-reset
```

**Using Makefile:**
```bash
make setup                # Initial setup
make start                # Start containers
make logs                 # View logs
make logs-scheduler       # View scheduler logs
make migrate              # Run migrations
make demo-reset           # Reset demo data
make shell                # Access container
make help                 # Show all commands
```

See [docker/README.md](docker/README.md) for detailed Docker documentation.

## 💻 Local Development Setup (Alternative)

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/MaterialTest.php

# Run with coverage
php artisan test --coverage

# Browser testing (Pest 4)
php artisan test --filter=browser
```

## 🎨 Code Style

```bash
# Fix code style automatically
vendor/bin/pint

# Check code style without fixing
vendor/bin/pint --test

# Lint JavaScript
npm run lint

# Format JavaScript
npm run format
```

## 📚 Documentation

Dokumentasi lengkap tersedia di folder `docs/`:

### User Guides
- **[User Manual](docs/08-user-manual.md)** - 📖 **Panduan lengkap cara menggunakan aplikasi**
- **[Admin Panel Guide](docs/10-admin-panel.md)** - 🔐 **Panduan Admin Panel untuk platform management**

### Technical Documentation
- [README](docs/README.md) - Project overview dan category comparison
- [Business Requirements](docs/01-business-requirements.md) - Kebutuhan bisnis multi-category
- [System Architecture](docs/02-system-architecture.md) - Arsitektur sistem dan design patterns
- [Database Schema](docs/03-database-schema.md) - Struktur database (37 tables)
- [API Endpoints](docs/04-api-endpoints.md) - Dokumentasi API (175+ routes)
- [User Flows](docs/05-user-flows.md) - Alur kerja pengguna per kategori
- [MVP Development Plan](docs/06-mvp-development-plan.md) - Rencana implementasi MVP
- [Frontend UI Architecture](docs/07-frontend-ui-architecture.md) - UI/UX design system
- [Workflow Summary](docs/09-workflow-summary.md) - Ringkasan workflow dan data flow

### Feature Documentation
- **[AI Assistant](docs/12-fabriku-assistant.md)** - 🤖 **Dokumentasi Fabriku Assistant (AI Chat)**
- [Email Features](docs/11-email-features.md) - Sistem email dan notifikasi
- [Multi-Category Architecture](docs/multi-category-architecture.md) - Panduan arsitektur multi-kategori
- [Refactoring Summary](docs/refactoring-preparation-simplification.md) - Preparation simplification

### DevOps & Deployment
- [Docker Architecture](docs/DOCKER-ARCHITECTURE.md) - Arsitektur Docker container
- [Docker Quick Reference](docs/DOCKER-QUICKREF.md) - Command reference Docker
- [Email Setup](docs/EMAIL-SETUP.md) - Konfigurasi email SMTP
- [Schedule Monitoring](docs/SCHEDULE-MONITORING.md) - Monitoring scheduled tasks

## 🏢 Multi-Kategori Bisnis

Fabriku dirancang dengan arsitektur **category-agnostic** yang memungkinkan satu platform mendukung berbagai jenis bisnis UMKM:

### Kategori yang Didukung (MVP)

| Kategori | Terminologi | Contoh Produk | Status |
|----------|-------------|---------------|--------|
| **Garment & Konveksi** | Pattern → Cutting → Sewing | Mukena, Daster, Gamis, Jilbab | ✅ Active |
| **Makanan & Kue** | Resep → Mixing → Baking | Cake, Brownies, Cookies, Roti | ✅ Active |
| **Kerajinan & Craft** | Desain → Persiapan → Pembuatan | Souvenir, Aksesoris, Dekorasi | ✅ Active |
| **Kosmetik & Skincare** | Formula → Mixing → Produksi | Skincare, Makeup, Herbal | ✅ Active |

### Fitur Multi-Kategori

- ✅ **Dynamic Terminology**: UI menyesuaikan istilah per kategori (Pattern/Resep, Cutting/Mixing, dll)
- ✅ **Flexible Attributes**: Material attributes dinamis (warna/expired_date, lebar kain/storage temp)
- ✅ **Category-Specific Rules**: Business rules berbeda per kategori (waste %, quality grades, shelf life)
- ✅ **Extensible Config**: Mudah menambah kategori baru via `config/business.php` tanpa migration
- ✅ **Tenant Category**: Setiap tenant memilih 1 kategori bisnis saat onboarding

## 🔒 Multi-Tenancy

Fabriku menggunakan multi-tenant architecture dengan tenant isolation di database level:

- Setiap tenant memiliki data yang terisolasi
- Setiap tenant dapat memilih kategori bisnis mereka (garment, food, dll)
- Tenant context di-inject otomatis via middleware
- Global scopes untuk keamanan data
- Subscription management terintegrasi
- Category-specific configurations per tenant

## 🛡️ Security

- CSRF protection enabled
- XSS protection via Vue.js escaping
- SQL injection protection via Eloquent ORM
- Rate limiting per tenant
- Role-based access control (RBAC)
- Audit trail untuk critical operations

## 📊 Performance

- Query optimization dengan eager loading
- Redis caching untuk data yang sering diakses
- Queue system untuk operasi berat (reports, notifications)
- Database indexing strategy
- Asset optimization (minification, compression)

## 🌍 Localization

- Default language: Bahasa Indonesia
- Support untuk multiple languages (future)
- Timezone support per tenant

## 🚢 Deployment

### Production Checklist

```bash
# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build production assets
npm run build

# Generate Wayfinder types
php artisan wayfinder:generate

# Set correct permissions
chmod -R 755 storage bootstrap/cache
```

### Environment Variables

Important production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Enable queue
QUEUE_CONNECTION=redis

# Enable cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Configure mail
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
```

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow Laravel best practices
- Use Laravel Pint for PHP formatting
- Use ESLint + Prettier for JavaScript formatting
- Write tests for new features
- Update documentation as needed

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Product Owner**: [Your Name]
- **Lead Developer**: [Your Name]
- **UI/UX Designer**: [Designer Name]

## 📞 Support

- **Email**: support@fabriku.com
- **Documentation**: https://docs.fabriku.com
- **Issues**: https://github.com/yourusername/fabriku/issues

## 🗺️ Roadmap

### ✅ Phase 1: Foundation (Completed)
- ✅ Multi-tenancy setup dengan category selection
- ✅ User authentication & authorization (Login & Register)
- ✅ Landing page dengan multi-category showcase

### ✅ Phase 2: Material Management (Completed)
- ✅ Material master data dengan dynamic attributes
- ✅ Material Types management
- ✅ Material receipt recording
- ✅ Stock tracking dengan auto-update
- ✅ Category-specific material attributes (warna, expired date, dll)
- ✅ Staff management

### ✅ Phase 3: Pattern/Recipe & Preparation (Completed)
- ✅ Pattern/Recipe library dengan spesifikasi lengkap
- ✅ Preparation orders (simplified - manual material input)
- ✅ Material usage tracking dengan auto deduct stock
- ✅ Pattern sebagai referensi (optional, tidak auto-fill)
- ✅ Status workflow (draft → in_progress → completed)
- ✅ Stock availability validation
- ✅ UI/UX modernization complete:
  - Mobile-first responsive layout
  - Collapsible sidebar navigation with submenus
  - Dark/light theme support
  - Lucide icon integration
  - Consistent styling

### ✅ Phase 4: Production Management (Completed)
- ✅ Contractors/Partners management (CRUD)
- ✅ Production orders (internal & external)
- ✅ Status workflow (draft → pending → in_progress → completed)
- ✅ Action endpoints: send, start, mark-complete
- ✅ Quality control tracking
- ✅ Cost per unit & total cost calculation

### ✅ Phase 5: Inventory Management (Completed)
- ✅ Inventory locations (racks) CRUD
- ✅ Inventory items with SKU generation
- ✅ Link to production batches & patterns
- ✅ Stock quantity tracking (initial, current, reserved)
- ✅ Stock Adjustments (Opening Balance, Correction, Damage, etc)
- ✅ Status management (available, reserved, depleted)
- ✅ Selling price & cost tracking

### ✅ Phase 6: Sales Management (Completed)
- ✅ Customer management (CRUD)
- ✅ Customer types (retail, wholesale, reseller, online)
- ✅ Sales order creation with line items
- ✅ Multi-channel support (offline, online, marketplace)
- ✅ Payment tracking (unpaid, partial, paid)
- ✅ Order status workflow (pending → confirmed → packed → shipped → delivered)

### ✅ Phase 7: Dashboard & Reporting (Completed)
- ✅ Dashboard with real-time KPI
- ✅ Material Report
- ✅ Inventory Report
- ✅ Sales Report
- ✅ Production Report

### ✅ Phase 8: Polish & Testing (Completed)
- ✅ Comprehensive testing (100+ integration & feature tests)
- ✅ Browser testing with Pest 4
- ✅ Integration tests covering complete user journeys
- ✅ Unit tests for models, services, and utilities
- ✅ UI/UX polish & consistency
- ✅ Performance optimization
- ✅ Documentation finalization

### ✅ Phase 9: Admin Panel (Completed)
- ✅ Admin authentication & authorization
- ✅ Tenant management (Full CRUD)
- ✅ User management across tenants
- ✅ Role & Permission system (RBAC)
- ✅ Audit logging with change tracking
- ✅ Platform statistics dashboard
- ✅ Subscription payment management
- ✅ System monitoring & job management
- ✅ 15 admin pages with modern UI

### ✅ Phase 10: AI Assistant & Integrations (Completed)
- ✅ AI Assistant with OpenAI integration (GPT-4o)
- ✅ Natural language business queries
- ✅ Conversation management & history
- ✅ Telegram Bot integration
- ✅ Multi-channel support (Web, Telegram)
- ✅ Assistant usage tracking per tenant/user
- ✅ Pending action confirmation system
- ✅ Email system with custom templates
- ✅ Trial reminder automation
- ✅ Email verification & welcome emails

### 🚀 Future Enhancements
- 📱 Mobile app (React Native / Flutter)
- 📷 Barcode/QR code scanning
- 📩 WhatsApp Business API integration
- 🛒 E-commerce integration (Tokopedia, Shopee, etc)
- 💳 Payment gateway integration (Midtrans, Xendit)
- 📊 Advanced analytics & AI forecasting
- 🌐 Multi-warehouse management
- 🤝 Supplier portal
- 💰 Accounting integration
- 📦 Shipping integration (JNE, J&T, SiCepat)

## 🧪 Testing

Fabriku menggunakan **Pest 4** dengan coverage yang komprehensif:

### Test Structure
```
tests/
├── Feature/           # Feature & Integration Tests (100+ tests)
│   ├── Integration/   # Complete user journey tests
│   │   ├── CompleteUserJourneyTest.php
│   │   ├── RegistrationAndAuthenticationTest.php
│   │   ├── MaterialToPreparationFlowTest.php
│   │   ├── InventoryAndQRCodeTest.php
│   │   ├── SubscriptionAndSettingsTest.php
│   │   └── AssistantAndReportsTest.php
│   ├── Auth/          # Authentication tests
│   ├── *Test.php      # Feature tests for each module
│   └── ...
├── Unit/              # Unit tests
└── Browser/           # Browser tests (Pest 4)
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter Integration

# Run with coverage
php artisan test --coverage

# Run single test file
php artisan test tests/Feature/Integration/CompleteUserJourneyTest.php

# Run tests in parallel (faster)
php artisan test --parallel

# Compact output
php artisan test --compact
```

### Integration Test Coverage

✅ **Complete User Journey** (Garment & Food workflows)
- Material receipt → Preparation → Production → Inventory → Sales
- Multi-category business support

✅ **Authentication & Registration**
- User registration flow with email verification
- Login/logout functionality
- Password reset flow
- Subscription enforcement

✅ **Material Management**
- Material types, materials, receipts
- Stock tracking with FIFO/FEFO
- Batch tracking
- Expiry date alerts (food category)

✅ **Preparation & Production**
- Cutting/mixing orders
- Material usage tracking
- Stock deduction automation
- Quality control

✅ **Inventory Management**
- Location management
- Stock adjustments (damage, lost, found)
- QR code generation & scanning
- Expiry tracking for food items

✅ **Sales Orders**
- Order creation & management
- Stock reservation & deduction
- Payment tracking
- Multi-channel sales

✅ **Reports & Analytics**
- Material, Inventory, Production, Sales reports
- Export to Excel/PDF
- Date range filtering

✅ **Settings & Subscriptions**
- Company settings management
- Subscription plan upgrades
- Trial management
- Multi-user tenant access

✅ **AI Assistant & Integrations**
- OpenAI GPT-4 integration
- Telegram bot connectivity
- Email notifications

### Code Quality

```bash
# Format code with Pint
vendor/bin/pint

# Lint JavaScript/Vue
npm run lint

# Format frontend code
npm run format
```

## 📸 Screenshots

_Coming soon..._

## 🙏 Acknowledgments

- Laravel community
- Inertia.js team
- Vue.js team
- Tailwind CSS team
- All contributors and supporters

---

Made with ❤️ for Indonesian UMKM by Fabriku Team
