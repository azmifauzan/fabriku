# Fabriku - Multi-Category Production & Sales Management

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

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: Vue 3, Inertia.js v2, Tailwind CSS v4
- **Database**: PostgreSQL (multi-tenant architecture)
- **Testing**: Pest v4 (with browser testing support)
- **Build**: Vite, Laravel Wayfinder (type-safe routing)

## 📁 Documentation Structure

- [01-business-requirements.md](01-business-requirements.md) - User stories & business rules
- [02-system-architecture.md](02-system-architecture.md) - Technical architecture & patterns
- [03-database-schema.md](03-database-schema.md) - Database design & ERD
- [04-api-endpoints.md](04-api-endpoints.md) - API specifications
- [05-user-flows.md](05-user-flows.md) - User journey & UI flows
- [06-mvp-development-plan.md](06-mvp-development-plan.md) - Implementation roadmap

## 🚀 Development Progress

### ✅ Phase 1: Foundation (Completed)
- Multi-tenancy setup
- Authentication & user management
- Tenant context middleware

### ✅ Phase 2: Material Management (Completed)
- Material master data dengan atribut dinamis
- Material receipt recording
- Stock tracking
- 12 tests passing ✅

### 🔄 Phase 3: Pattern/Recipe & Preparation (In Progress)
- Pattern/Recipe library dengan BOM
- Preparation/Cutting orders
- Material usage & efficiency tracking
- 20 tests passing (backend complete) ✅
- Frontend components (2/4 done)

### 📋 Next Phases
- Phase 4: Production Management (Sewing/Baking)
- Phase 5: Inventory Management
- Phase 6: Sales Management
- Phase 7: Reports & Analytics

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

## 🤝 Contributing

Development mengikuti Laravel best practices dengan focus pada:
- Clean code & SOLID principles
- Comprehensive testing (Unit, Feature, Browser)
- Multi-tenancy data isolation
- Category-agnostic design patterns

---

**Status**: 🚧 MVP Development - Phase 3/7 Complete

**Next Milestone**: Complete Phase 3 frontend components, then move to Production Management (Phase 4)
