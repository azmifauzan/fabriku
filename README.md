# Fabriku

> **Platform SaaS Multi-Kategori untuk Manajemen Produksi & Penjualan UMKM**

Fabriku adalah aplikasi berbasis web yang dirancang untuk membantu UMKM dalam mengelola seluruh proses bisnis mereka dari berbagai kategori industri. Saat ini mendukung **Garment & Konveksi** dan **Makanan & Kue**, dengan rencana ekspansi ke kategori lain (Kerajinan, Kosmetik, dll).

Platform ini mengelola workflow universal: **Bahan Baku → Pattern/Resep → Persiapan → Produksi → Inventory → Penjualan**, dengan terminologi dan business rules yang disesuaikan per kategori bisnis.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)

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

### 📋 Pattern/Resep & BOM Management
- **Pattern Library** (Garment): Pola dengan ukuran dan kebutuhan kain
- **Recipe Library** (Makanan): Resep dengan serving size dan komposisi bahan
- Bill of Materials (BOM) dengan cost calculation otomatis
- Interactive BOM builder di frontend
- Support untuk berbagai product types per kategori

### ✂️ Proses Persiapan (Multi-Kategori)
- **Cutting Orders** (Garment): Pemotongan kain berdasarkan pattern
- **Preparation Orders** (Makanan): Mixing/persiapan bahan berdasarkan resep
- Material usage tracking dan stock sufficiency check
- Waste/efficiency calculation
- Status workflow dengan guards (draft, in_progress, completed, cancelled)

### 🧵 Manajemen Produksi (Coming in Phase 4)
- Support produksi internal dan outsourcing:
  - **Garment**: Penjahit/kontraktor jahit
  - **Makanan**: Dapur sharing/outsource produksi
- Tracking progress dan timeline produksi
- Quality control:
  - **Garment**: Grade A/B/Reject
  - **Makanan**: Premium/Standar, expired date tracking
- Rating dan evaluasi kontraktor/partner

### 📊 Inventory Produk Jadi (Coming in Phase 5)
- Manajemen lokasi penyimpanan (rak)
- Tracking dengan FIFO/FEFO
- **Makanan**: Expired date monitoring dan shelf life alerts
- **Garment**: Batch tracking dan quality grades
- Low stock alerts dan stock adjustment

### 💰 Manajemen Penjualan (Coming in Phase 6)
- Multi-channel sales (offline, online, marketplace, reseller)
- Customer relationship management
- Payment tracking (unpaid, partial, paid)
- Invoice generation otomatis
- Stock deduction otomatis saat penjualan

### 📈 Pelaporan & Analytics (Coming in Phase 7)
- Dashboard dengan real-time KPI per kategori bisnis
- Laporan bahan baku (pembelian, pemakaian, stok)
- Laporan produksi (efisiensi, produktivitas, waste)
- Laporan penjualan (revenue, profit, top products)
- Profit & Loss statement
- Export ke PDF dan Excel

## 🏗️ Technology Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.4
- **Database**: PostgreSQL (recommended) / MySQL
- **Cache**: Redis
- **Queue**: Redis
- **Testing**: Pest 4 (with Browser Testing)

### Frontend
- **Framework**: Vue 3 (Composition API)
- **SSR**: Inertia.js v2
- **Styling**: Tailwind CSS 4
- **Type Safety**: TypeScript
- **Routing**: Laravel Wayfinder
- **Build Tool**: Vite

### Development Tools
- **Code Style**: Laravel Pint
- **Static Analysis**: PHPStan (optional)
- **Linting**: ESLint + Prettier
- **Version Control**: Git

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

# Seed akan membuat 2 demo tenants:
# 1. Konveksi Fabriku (Garment) - admin@konveksi.com / password
# 2. Kue Mama Homemade (Food) - admin@kuemama.com / password
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
```

Visit: http://localhost:8000

## 🐳 Docker Setup (Alternative)

```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Access application
# http://localhost:8000
```

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

- [README](docs/README.md) - Project overview dan category comparison
- [Business Requirements](docs/01-business-requirements.md) - Kebutuhan bisnis multi-category
- [System Architecture](docs/02-system-architecture.md) - Arsitektur sistem dan design patterns
- [Database Schema](docs/03-database-schema.md) - Struktur database category-agnostic
- [API Endpoints](docs/04-api-endpoints.md) - Dokumentasi API lengkap
- [User Flows](docs/05-user-flows.md) - Alur kerja pengguna per kategori
- [MVP Development Plan](docs/06-mvp-development-plan.md) - Rencana implementasi MVP
- [Multi-Category Architecture](docs/multi-category-architecture.md) - Panduan arsitektur multi-kategori

## 🏢 Multi-Kategori Bisnis

Fabriku dirancang dengan arsitektur **category-agnostic** yang memungkinkan satu platform mendukung berbagai jenis bisnis UMKM:

### Kategori yang Didukung (MVP)

| Kategori | Terminologi | Contoh Produk | Status |
|----------|-------------|---------------|--------|
| **Garment & Konveksi** | Pattern → Cutting → Sewing | Mukena, Daster, Gamis, Jilbab | ✅ Active |
| **Makanan & Kue** | Resep → Mixing → Baking | Cake, Brownies, Cookies, Roti | ✅ Active |
| **Kerajinan & Craft** | Desain → Persiapan → Pembuatan | Souvenir, Aksesoris, Dekorasi | 🔜 Coming Soon |
| **Kosmetik & Skincare** | Formula → Mixing → Produksi | Skincare, Makeup, Herbal | 🔜 Coming Soon |

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
- ✅ User authentication & authorization
- ✅ Landing page dengan multi-category showcase

### ✅ Phase 2: Material Management (Completed)
- ✅ Material master data dengan dynamic attributes
- ✅ Material receipt recording
- ✅ Stock tracking dengan FIFO/FEFO
- ✅ Category-specific material attributes (warna, expired date, dll)

### ✅ Phase 3: Pattern/Recipe & Preparation (Completed)
- ✅ Pattern/Recipe library dengan BOM
- ✅ Interactive BOM builder
- ✅ Cutting/Preparation orders
- ✅ Material usage tracking
- ✅ Waste/efficiency calculation
- ✅ Real-time cost calculation

### 🔄 Phase 4: Production Management (In Progress)
- 🔄 Contractors/Partners management
- 🔄 Production orders (internal & external)
- 🔄 Quality control (Grade A/B/Reject, expired tracking)
- 🔄 Production costing

### 📅 Phase 5: Inventory Management (Q1 2026)
- Inventory items with SKU
- Location management (racks)
- Stock view and search
- Expired date tracking & alerts (food category)
- Production date & best before date

### 📅 Phase 6: Sales Management (Q1 2026)
- Customer management
- Sales order creation
- Multi-channel support
- Payment tracking
- Stock deduction otomatis

### 📅 Phase 7: Dashboard & Reporting (Q2 2026)
- Dashboard KPI per kategori
- Material, production, inventory reports
- Sales summary & profit analysis
- Category-specific analytics

### 📅 Phase 8: Polish & Testing (Q2 2026)
- Comprehensive testing
- Browser testing (Pest 4)
- UI/UX polish
- Performance optimization
- Documentation finalization

### 🚀 Future Enhancements (Q3-Q4 2026)
- 📱 Mobile app (React Native)
- 🎨 Kategori baru: Kerajinan & Craft
- 💄 Kategori baru: Kosmetik & Skincare
- 📷 Barcode/QR code scanning
- 🔔 Real-time notifications
- 📧 Email automation
- 🛒 E-commerce integration
- 💳 Payment gateway integration
- 📊 Advanced analytics & AI forecasting
- 🌐 Multi-warehouse support

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
