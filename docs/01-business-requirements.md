# Business Requirements - Fabriku

> **Last Updated**: May 24, 2026

## Overview
Fabriku adalah aplikasi SaaS (Software as a Service) yang dirancang untuk membantu UMKM dalam mengelola proses produksi dan penjualan mereka secara efisien dan terintegrasi. Aplikasi ini mendukung **6 kategori bisnis aktif**:
1. **Garment** - Produksi pakaian jadi (mukena, daster, gamis, dll)
2. **Makanan & Kue** - Produksi makanan/kue untuk dijual
3. **Kerajinan** - Craft & handmade products
4. **Kosmetik** - Skincare & beauty products
5. **Toko / Retail** - Toko sederhana: beli produk jadi → stok → kasir (Quick Checkout)
6. **Produksi Rumahan** - UMKM skala rumahan: beli bahan baku → catat produksi sederhana → kasir

## Business Goals
1. Menyediakan solusi manajemen produksi yang mudah digunakan untuk berbagai jenis UMKM
2. Meningkatkan efisiensi dalam pencatatan bahan baku/bahan mentah, proses produksi, dan penjualan
3. Memberikan visibilitas penuh terhadap inventory dan cash flow
4. Menghasilkan laporan yang akurat untuk pengambilan keputusan bisnis
5. Sistem yang fleksibel untuk mendukung berbagai kategori bisnis dengan proses produksi berbeda

## Target Users
- Pemilik UMKM (garment, makanan/kue, kerajinan, kosmetik, toko retail, produksi rumahan)
- Staff produksi
- Staff gudang/inventory
- Staff penjualan / kasir
- Mitra produksi eksternal (penjahit outsourcing, dapur sharing, dll)
- Super Admin (platform management)

## Core Features

### 1. Manajemen Bahan Baku/Bahan Mentah
**User Story**: Sebagai staff gudang, saya ingin mencatat kedatangan bahan baku/bahan mentah dari supplier agar dapat melacak inventory dengan akurat.

**Functional Requirements**:
- Pencatatan kedatangan bahan baku/mentah dengan fleksibilitas untuk berbagai kategori:
  - **Garment**: kain (roll/meter), benang (kg), resleting (pcs), kancing (pcs)
  - **Kue**: tepung (kg), gula (kg), telur (butir), mentega (kg), dll
- Informasi supplier, tanggal kedatangan, jumlah, satuan, harga
- Upload foto/dokumen pendukung (opsional)
- Tracking batch number untuk traceability
- Multi-currency support (IDR default)
- Atribut dinamis per material (warna, ukuran, expired date, dll)

### 2. Pola/Resep Produk (Pattern/Recipe)
**User Story**: Sebagai pemilik usaha, saya ingin membuat template produk dengan kebutuhan bahan agar bisa menghitung biaya produksi dengan akurat.

**Functional Requirements**:
- Library pola/resep produk (template produk):
  - **Garment**: Pattern untuk mukena, daster, gamis dengan ukuran
  - **Kue**: Resep untuk brownies, cookies, cake
  - **Craft**: Template untuk gelang, tas, gantungan kunci
  - **Cosmetic**: Formula untuk sabun, lotion, lip balm
- Informasi produk: kode, nama, kategori, tipe produk, deskripsi
- Foto produk
- **TIDAK ada Bill of Materials (BOM)** - UMKM tidak perlu presisi pabrik
- Kategori produk (garment, food, craft, cosmetic, other)

### 3. Proses Persiapan (Preparation Process)
**User Story**: Sebagai staff produksi, saya ingin mencatat proses persiapan bahan dengan sederhana - bahan apa yang dipakai dan jadi berapa pcs.

**Functional Requirements**:
- Pencatatan proses persiapan (universal untuk semua kategori):
  - **Garment**: Pemotongan kain (cutting)
  - **Kue**: Persiapan/mixing adonan
  - **Craft**: Assembly preparation komponen
  - **Cosmetic**: Formulation mixing
- Input: bahan baku yang digunakan (flexible - pilih material & input quantity manual)
- Output: jumlah hasil persiapan dengan unit flexible (pieces/kg/batch/liter/dll)
- **Auto stock deduction** dari materials saat proses selesai
- Pattern/template optional (bisa prep tanpa pattern)
- Notes untuk catatan tambahan
- **TIDAK ada tracking waste/efficiency** - terlalu kompleks untuk UMKM

### 4. Manajemen Produksi
**User Story**: Sebagai supervisor produksi, saya ingin melacak proses produksi baik internal maupun eksternal agar dapat mengontrol timeline.

**Functional Requirements**:
- Pencatatan produksi internal dan eksternal:
  - **Garment**: Jahit internal atau outsourcing ke penjahit
  - **Kue**: Baking internal atau menggunakan dapur sharing/outsourcing
- Tracking status: Draft, In Progress, Completed, Quality Check, Cancelled
- Pencatatan pihak ketiga (kontraktor/mitra produksi)
- Tanggal pengiriman dan pengembalian
- Quality control checklist
- Biaya produksi (bahan baku + tenaga kerja + overhead)

### 5. Manajemen Inventory Produk Jadi
**User Story**: Sebagai staff gudang, saya ingin menyimpan dan melacak produk jadi dalam sistem batch agar mudah dalam pengambilan stok.

**Functional Requirements**:
- Pencatatan batch produk jadi (garment atau kue)
- Lokasi penyimpanan (rak/bin location)
- Jumlah pieces per batch
- Status produk (Ready, Reserved, Sold)
- FIFO/FEFO tracking (penting untuk produk makanan dengan expired date)
- Low stock alerts
- Tanggal produksi dan expired date (untuk makanan)

### 6. Manajemen Penjualan
**User Story**: Sebagai staff penjualan, saya ingin mencatat transaksi penjualan dan mengurangi stok secara otomatis.

**Functional Requirements**:
- Pencatatan order penjualan
- Customer information
- Item yang dijual (SKU, quantity, price)
- Payment tracking (unpaid, partial, paid)
- Invoice generation
- Automatic stock deduction
- Sales channel tracking (online, offline, reseller)

### 7. Pelaporan & Analytics
**User Story**: Sebagai pemilik bisnis, saya ingin melihat laporan lengkap dari bahan baku hingga penjualan agar dapat menganalisis profitabilitas.

**Functional Requirements**:
- Dashboard overview (KPI metrics)
- Laporan bahan baku/mentah (pembelian, pemakaian, sisa stok)
- Laporan produksi (efisiensi persiapan, produktivitas produksi)
- Laporan inventory (stock level, aging, turnover, expired soon untuk makanan)
- Laporan penjualan (revenue, profit margin, produk terlaris)
- Cost of Goods Sold (COGS) calculation per kategori bisnis
- Profit & Loss report
- Export to PDF/Excel
- Grafik visualisasi data

## Business Rules by Category

### Garment-Specific Rules
- Material tracking: batch number, warna, lebar kain, gramasi
- Pattern dengan ukuran (XS, S, M, L, XL, XXL, all_size)
- Waste percentage untuk cutting process (standar 3-10%)
- Quality grades: Grade A, Grade B, Reject
- Proses: Cutting → Sewing → Quality Check → Packaging
- Kontraktor/outsourcing penjahit tersedia

### Makanan/Kue-Specific Rules
- Material tracking: batch number, expired date, storage temp
- Recipe dengan serving size atau jumlah output (loyang, pieces)
- Storage temperature requirements (frozen, chilled, room temp)
- Shelf life tracking dan expired date alert (7 hari sebelum expired)
- Food safety compliance notes
- Proses: Preparation/Mixing → Baking/Cooking → Quality Check → Packaging
- Export to PDF/Excel

### Kerajinan-Specific Rules
- Material tracking: batch number, warna, ukuran
- Design/template library
- Quality grades: Premium, Standar, Ekonomi
- Waste percentage 5-15%
- Proses: Desain → Persiapan → Pembuatan → Packaging

### Kosmetik-Specific Rules
- Material tracking: batch number, expired date, nomor BPOM, storage temp
- Formula/resep produk
- Shelf life alert 30 hari sebelum expired
- Compliance BPOM wajib
- Proses: Formulasi/Mixing → Produksi → Quality Check → Packaging
- Maklon/contract manufacturing tersedia

### Retail-Specific Rules
- **Tidak ada** modul material/produksi (langsung beli produk jadi)
- Purchase Receipt: catat pembelian dari supplier → stok bertambah
- Quick Checkout POS: grid produk + cart, transaksi langsung completed
- Purchase Report: laporan pembelian per batch/supplier
- Modul yang aktif: Inventory, Penjualan (Quick Checkout), Laporan Pembelian + Penjualan

### Produksi Rumahan (Homemade)-Specific Rules
- Beli bahan baku dari supplier → stok material bertambah
- **Simple Production (Catatan Produksi)**: pilih bahan baku → input produk jadi → stok bahan berkurang + produk masuk inventory
- Tidak ada Production Order, tidak ada Kontraktor, tidak ada Purchase Receipt produk jadi
- Quick Checkout POS untuk penjualan cepat
- Track expired date dan batch number (berguna untuk produk makanan rumahan)
- Modul yang aktif: Bahan Baku, Resep (opsional), Catatan Produksi, Inventory, Penjualan (Quick Checkout), Laporan

## Non-Functional Requirements

### Performance
- Page load time < 2 seconds
- Support concurrent users (target: 100+ per tenant)
- Real-time inventory updates

### Security
- Multi-tenant architecture with data isolation
- Role-based access control (RBAC)
- SSL/TLS encryption
- Regular automated backups
- Audit trail for critical operations

### Usability
- Responsive design (mobile-friendly)
- Bahasa Indonesia as primary language
- Intuitive navigation
- Minimal training required

### Scalability
- Support untuk multiple locations/warehouses
- Support untuk multiple product lines
- Horizontal scaling capability

## Success Metrics
1. User adoption rate > 80% dalam 3 bulan
2. Reduction in inventory discrepancies > 50%
3. Time saving in reporting > 70%
4. User satisfaction score > 4.0/5.0
5. System uptime > 99.5%

## Fitur yang Sudah Aktif (Sebelumnya Direncanakan)

- Kategori `retail` (Toko/Simple Shop) — Purchase Receipt, Quick Checkout POS, Purchase Report ✅
- Kategori `homemade` (Produksi Rumahan) — Simple Production, Quick Checkout POS ✅
- Telegram bot (webhook + push notifikasi) ✅
- Email system (verifikasi, reset, trial reminder) ✅

## Future Enhancements (Phase 2)
- Mobile app untuk operator lapangan
- Barcode scanning untuk material receipt (inventory sudah punya QR code)
- Integration dengan e-commerce platforms (Tokopedia, Shopee)
- Payment gateway terintegrasi (Midtrans/Xendit) — saat ini manual upload bukti
- Automated reorder points (alert low stock sudah ada, auto-order belum)
- Multi-warehouse management
- Integration dengan accounting software
- Shipping API (JNE/JNT/SiCepat)
- Multi-bahasa (saat ini hardcoded Bahasa Indonesia)
