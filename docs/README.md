# Fabriku - Multi-Category Production & Sales Management

> **Last Updated**: February 3, 2026

## 🎯 Project Vision
Fabriku adalah aplikasi SaaS yang membantu UMKM mengelola produksi dan penjualan secara efisien, **mendukung berbagai jenis kategori bisnis** dengan satu platform terpadu.

## 🏭 Target Industries

### Current MVP (Phase 1)
1. **Garment/Konveksi** 🧵
   - Produksi pakaian: mukena, daster, gamis, jilbab
   - Workflow: Material → Pattern → Cutting → Sewing → Inventory → Sales
   - Tracking: batch number, warna, ukuran, quality grade

2. **Kue Rumahan/Bakery** 🍰
   - Produksi makanan: cake, brownies, cookies, roti, kue kering
   - Workflow: Bahan Mentah → Resep → Mixing → Baking → Inventory → Sales
   - Tracking: expired date, storage temp, shelf life, food safety

### Future Expansion (Phase 2+)
- **Craft/Kerajinan** 🎨 - Produk handmade, souvenir, aksesoris
- **Cosmetics/Kecantikan** 💄 - Skincare, makeup, produk herbal
- **Dan kategori UMKM lainnya**

## 🌟 Core Features

### Universal Workflow
Semua kategori bisnis mengikuti workflow dasar yang sama:

```
Raw Materials → Recipe/Pattern → Preparation → Production → Inventory → Sales
```

Namun terminologi dan business rules disesuaikan per kategori.

### Key Capabilities
- ✅ **Multi-Tenancy** - Setiap UMKM punya data terpisah
- ✅ **Material Management** - Track bahan baku dengan atribut dinamis
- ✅ **Recipe/Pattern Library** - Template produk dengan BOM (Bill of Materials)
- ✅ **Production Tracking** - Internal & outsourcing process management
- ✅ **Inventory Management** - Stock tracking, location, FIFO/FEFO
- ✅ **Sales Management** - Multi-channel sales, payment tracking
- ✅ **Reports & Analytics** - Production efficiency, P&L, COGS

### Category-Specific Features

| Feature | Garment | Kue/Food |
|---------|---------|----------|
| Material Attributes | warna, lebar kain, gramasi | expired date, storage temp |
| Product Template | Pattern (ukuran XS-XXL) | Recipe (serving size, output) |
| Preparation | Cutting Process | Mixing/Prep Process |
| Production | Sewing (internal/outsource) | Baking (internal/outsource) |
| Quality Control | Grade A/B/Reject | Expired date alerts |
| Waste Tracking | Sisa kain (3-10%) | Sisa bahan |

## 🏗️ Tech Stack

### Backend
- **Framework**: Laravel 12.47.0
- **Language**: PHP 8.4.11
- **Database**: PostgreSQL 16 (multi-tenant architecture)
- **Cache/Queue**: Redis 7
- **PDF**: DomPDF
- **Excel**: Maatwebsite Excel 3.1
- **AI**: OpenAI API (GPT-4o)
- **Messaging**: Telegram Bot SDK

### Frontend
- **Framework**: Vue 3.5.18 (Composition API)
- **SPA**: Inertia.js v2.3.7
- **Styling**: Tailwind CSS v4.1.11
- **Type Safety**: TypeScript 5.2.2
- **Routing**: Laravel Wayfinder 0.1.13
- **Build**: Vite 7
- **Icons**: Lucide Vue Next

### Testing & Development
- **Testing**: Pest v4.3.1 (with browser testing support)
- **Code Style**: Laravel Pint 1.27
- **Linting**: ESLint 9 + Prettier 3
- **Debug**: Laravel Pail, Laravel Boost MCP

## 📁 Documentation Structure

### User Guides
- **[📖 User Manual](08-user-manual.md)** - Panduan lengkap cara menggunakan aplikasi (START HERE!)
- **[🔐 Admin Panel Guide](10-admin-panel.md)** - Panduan Admin Panel untuk platform management

### Technical Documentation
- [01-business-requirements.md](01-business-requirements.md) - User stories & business rules
- [02-system-architecture.md](02-system-architecture.md) - Technical architecture & patterns
- [03-database-schema.md](03-database-schema.md) - Database design (37 tables)
- [04-api-endpoints.md](04-api-endpoints.md) - API specifications (175+ routes)
- [05-user-flows.md](05-user-flows.md) - User journey & UI flows
- [06-mvp-development-plan.md](06-mvp-development-plan.md) - Implementation roadmap
- [07-frontend-ui-architecture.md](07-frontend-ui-architecture.md) - Frontend UI/UX design system
- [09-workflow-summary.md](09-workflow-summary.md) - Workflow & data flow summary

### Feature Documentation
- **[🤖 AI Assistant](12-fabriku-assistant.md)** - Fabriku AI Chat Assistant documentation
- [📧 Email Features](11-email-features.md) - Email system & notifications
- [multi-category-architecture.md](multi-category-architecture.md) - Multi-category design patterns
- [refactoring-preparation-simplification.md](refactoring-preparation-simplification.md) - Preparation simplification (BOM removal)

### DevOps & Deployment
- [DOCKER-ARCHITECTURE.md](DOCKER-ARCHITECTURE.md) - Docker container architecture
- [DOCKER-QUICKREF.md](DOCKER-QUICKREF.md) - Docker quick reference commands
- [EMAIL-SETUP.md](EMAIL-SETUP.md) - Email SMTP configuration
- [SCHEDULE-MONITORING.md](SCHEDULE-MONITORING.md) - Scheduled tasks monitoring

## 🚀 Development Progress

### ✅ Phase 1: Foundation (Completed)
- Multi-tenancy setup dengan category selection
- Authentication & user management (Login & Register)
- Tenant context middleware
- Landing page dengan multi-category showcase

### ✅ Phase 2: Material Management (Completed)
- Material master data dengan atribut dinamis
- Material Types management
- Material receipt recording
- Stock tracking dengan auto-update
- Staff management

### ✅ Phase 3: Pattern/Recipe & Preparation (Completed)
- Pattern/Recipe library dengan spesifikasi lengkap
- Preparation orders (simplified - manual material input)
- Material usage tracking dengan auto deduct stock
- Pattern sebagai referensi (optional, tidak auto-fill)
- Status workflow dengan validation
- UI/UX modernization complete:
  - Mobile-first responsive layout
  - Collapsible sidebar navigation with submenus
  - Dark/light theme support
  - Lucide icon integration
  - Consistent styling

### ✅ Phase 4: Production Management (Completed)
- Contractors/Partners management (CRUD)
- Production orders (internal & external)
- Status workflow (draft → sent → in_progress → completed)
- Action endpoints: send, start, mark-complete
- Quality control tracking
- Cost calculation

### ✅ Phase 5: Inventory Management (Completed)
- Inventory locations (racks) CRUD
- Inventory items with SKU generation
- Link to production batches & patterns
- Stock quantity tracking (initial, current, reserved)
- Stock Adjustments (Opening Balance, Correction, Damage, etc)
- Status management (available, reserved, damaged, expired)
- Selling price & cost tracking
- Image upload support

### ✅ Phase 6: Sales Management (Completed)
- Customer management (CRUD)
- Customer types (retail, wholesale, reseller, online)
- Sales order creation with line items
- Multi-channel support (offline, online, marketplace)
- Payment tracking (unpaid, partial, paid)
- Order status workflow (draft → confirmed → processing → completed)
- Invoice generation & printing
- Export to PDF

### ✅ Phase 7: Dashboard & Reporting (Completed)
- Dashboard with real-time KPI
- Material Report with export
- Inventory Report with export
- Sales Report with export
- Production Report with export
- Sales Recap Report

### ✅ Phase 8: Polish & Testing (Completed)
- Comprehensive testing (32 test files)
- Browser testing (Pest 4)
- UI/UX polish & consistency
- Performance optimization
- Documentation finalization

### ✅ Phase 9: Admin Panel (Completed)
- Admin authentication & authorization (separate guard)
- Tenant management (Full CRUD, suspend/activate)
- User management across tenants
- Role & Permission system (RBAC)
- Audit logging with change tracking
- Platform statistics dashboard
- Subscription payment management
- System monitoring & job management
- 15+ admin pages with modern UI

### ✅ Phase 10: AI Assistant & Integrations (Completed)
- AI Assistant with OpenAI GPT-4o integration
- Natural language business queries
- Conversation management & history
- Telegram Bot integration
- Multi-channel support (Web, Telegram)
- Assistant usage tracking per tenant/user
- Pending action confirmation system
- Email system with custom templates (Bahasa Indonesia)
- Trial reminder automation (7 days, 3 days, 1 day)
- Email verification & welcome emails
- Email logging & tracking

## 🎨 Design Philosophy

### Category-Agnostic Core
Aplikasi dirancang dengan core yang generic, memungkinkan:
- ✅ Business logic yang reusable
- ✅ Terminologi yang disesuaikan per kategori
- ✅ Atribut material yang dinamis
- ✅ Business rules yang fleksibel

### Implementation Approach
1. **Generic Database Schema** - Tabel yang tidak spesifik ke satu kategori
2. **Flexible Attributes** - Atribut dinamis via separate table
3. **Enum per Category** - product_type enum disesuaikan kategori
4. **Conditional UI** - Frontend menampilkan field sesuai kategori tenant
5. **Business Rule Engine** - Rules engine yang bisa di-customize per kategori

## 🧪 Quality Standards

Setiap implementasi wajib melalui validasi:

1. ✅ **Error Check** - `get_errors` untuk compile/syntax errors
2. ✅ **Code Format** - `vendor/bin/pint` untuk PSR-12 compliance
3. ✅ **Feature Tests** - Pest tests dengan coverage 80%+
4. ✅ **Browser Tests** - Manual/automated UI testing

## � Project Statistics

| Metric | Count |
|--------|-------|
| Database Tables | 37 |
| API Routes | 175+ |
| Test Files | 32 |
| Vue Pages | 60+ |
| Models | 28 |
| Controllers | 25+ |

## 🗄️ Database Migration Workflow (Dev)

Untuk perubahan schema selama development:
- Jangan buat migration baru untuk mengubah tabel yang sudah ada.
- Update migration existing yang paling relevan (biasanya migration `create_*` yang pertama kali membuat tabel).
- Jalankan `php artisan migrate:fresh --seed`.

## 🤝 Contributing

Development mengikuti Laravel best practices dengan focus pada:
- Clean code & SOLID principles
- Comprehensive testing (Unit, Feature, Browser)
- Multi-tenancy data isolation
- Category-agnostic design patterns

---

**Status**: ✅ MVP Complete - All 10 Phases Completed

**Current Version**: v1.0.0 (Production Ready)

