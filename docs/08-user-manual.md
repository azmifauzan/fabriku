# User Manual - Fabriku

**Version**: 1.0 (Simplified Preparation)  
**Last Updated**: 15 Januari 2026

---

## 📖 Daftar Isi

1. [Pengantar](#pengantar)
2. [Memulai](#memulai)
3. [Alur Kerja Lengkap](#alur-kerja-lengkap)
4. [Modul Material (Bahan Baku)](#modul-material-bahan-baku)
5. [Modul Pattern](#modul-pattern)
6. [Modul Preparation Order](#modul-preparation-order)
7. [Modul Production Order](#modul-production-order)
8. [Tips & Best Practices](#tips--best-practices)
9. [FAQ](#faq)

---

## Pengantar

### Apa itu Fabriku?

Fabriku adalah aplikasi manajemen produksi dan penjualan untuk UMKM yang mendukung berbagai kategori bisnis. Saat ini fokus pada **Garment/Konveksi** dengan rencana ekspansi ke kategori lainnya.

### Fitur Utama

✅ **Manajemen Bahan Baku** - Track stok material dengan atribut dinamis  
✅ **Pattern Library** - Template produk dengan spesifikasi lengkap  
✅ **Preparation Order** - Proses persiapan bahan (cutting/mixing)  
✅ **Production Order** - Track produksi internal & outsourcing  
✅ **Multi-Tenant** - Data terpisah per bisnis  
✅ **Dark Mode** - UI modern dengan tema gelap/terang

---

## Memulai

### Login ke Aplikasi

1. Buka browser dan akses URL aplikasi
2. Masukkan **email** dan **password**
3. Klik tombol **"Sign In"**

### Tampilan Dashboard

Setelah login, Anda akan melihat dashboard dengan:
- **Ringkasan Statistik**: Pending preparation, production status, inventory alerts
- **Menu Navigasi** (sidebar kiri):
  - Dashboard
  - Master Data (Bahan Baku)
  - Preparation Order
  - Sewing Order (Production)
  - Inventory Items
  - Sales Order
  - Reports

---

## Alur Kerja Lengkap

### Overview Workflow

```
1. INPUT BAHAN BAKU
   ↓
2. BUAT PATTERN (Optional)
   ↓
3. BUAT PREPARATION ORDER
   ↓
4. BUAT PRODUCTION ORDER
   ↓
5. INVENTORY PRODUK JADI
   ↓
6. PENJUALAN
```

### Penjelasan Singkat

1. **Input Bahan Baku**: Catat material yang masuk dari supplier
2. **Buat Pattern**: Template produk (ukuran, spesifikasi) - *optional*
3. **Preparation Order**: Proses persiapan bahan (cutting/mixing)
4. **Production Order**: Proses produksi menjadi barang jadi
5. **Inventory**: Barang jadi masuk gudang
6. **Penjualan**: Jual ke customer, stok berkurang otomatis

---

## Modul Material (Bahan Baku)

### 1. Melihat Daftar Material

**Path**: Dashboard → Bahan Baku

**Fitur**:
- 🔍 **Search**: Cari berdasarkan nama atau kode material
- 🏷️ **Filter Jenis**: Kain, Benang, Aksesoris, Kemasan, Lainnya
- ✅ **Filter Status**: Aktif / Nonaktif
- 📊 **Info Stok**: Current stock, reorder point, harga

**Indikator Stok Rendah**: Material dengan stok ≤ reorder point ditandai dengan ⚠️ warning merah.

---

### 2. Menambah Material Baru

**Path**: Bahan Baku → Tambah Bahan Baku

#### Langkah-langkah:

**A. Informasi Dasar**
1. **Kode Bahan** ⭐ Required
   - Contoh: `KA-001`, `BN-002`
   - Harus unik

2. **Nama Bahan** ⭐ Required
   - Contoh: `Kain Katun Rayon Premium`

3. **Jenis Bahan** ⭐ Required
   - Pilihan: Kain, Benang, Aksesoris, Kemasan, Lainnya

4. **Satuan** ⭐ Required
   - Pilihan: Meter, Yard, Kg, Gram, Roll, Pieces, Lusin, Pack

**B. Stok dan Harga**

5. **Jumlah Stok Awal** (Optional)
   - Input stok awal jika sudah punya
   - Bisa dikosongkan (default: 0)

6. **Harga Standar** (Optional)
   - Harga per satuan dalam Rupiah
   - Untuk referensi costing

7. **Minimum Stok** (Optional)
   - Batas reorder point
   - Sistem akan alert jika stok ≤ nilai ini

8. **Status Aktif**
   - ✅ Centang = Material dapat digunakan untuk produksi
   - ❌ Tidak dicentang = Material nonaktif

**C. Atribut Tambahan**

Tambahkan informasi spesifik material:

**Contoh untuk Kain**:
- `Warna` → `Merah Maroon`
- `Lebar` → `150 cm`
- `Gramasi` → `180 gsm`
- `Corak` → `Polos`

**Contoh untuk Benang**:
- `Warna` → `Hitam`
- `Tipe` → `Polyester`
- `Nomor` → `40/2`

**Cara Menambah Atribut**:
1. Klik tombol **"+ Tambah Atribut"**
2. Isi **Nama Atribut** (misal: Warna)
3. Isi **Nilai Atribut** (misal: Merah Maroon)
4. Ulangi untuk atribut lainnya
5. Klik ikon **X** untuk hapus atribut yang tidak perlu

**D. Simpan**

Klik tombol **"Simpan"** untuk menyimpan material.

---

### 3. Mengedit Material

**Path**: Bahan Baku → Klik "Edit" pada material yang ingin diubah

- Semua field dapat diubah kecuali stok
- Untuk mengubah stok, gunakan **Material Receipt** (penerimaan barang)
- Klik **"Update"** untuk menyimpan perubahan

---

### 4. Material Receipt (Penerimaan Barang)

**Kapan digunakan?**
- Ketika menerima material baru dari supplier
- Untuk menambah stok material existing

**Path**: Bahan Baku → Detail Material → Tab "Penerimaan" → Tambah Penerimaan

#### Langkah-langkah:

1. **Nomor Penerimaan** (Auto-generated)
   - Format: `RCV-2026-001`

2. **Supplier**
   - Nama pemasok/supplier

3. **Tanggal Penerimaan** ⭐ Required

4. **Jumlah** ⭐ Required
   - Jumlah barang yang diterima
   - Satuan otomatis dari material

5. **Harga per Unit** (Optional)
   - Harga beli per satuan
   - Untuk tracking cost

6. **Batch Number** (Optional)
   - Nomor batch dari supplier
   - Untuk traceability

7. **Catatan** (Optional)

8. Klik **"Simpan"**

**Hasil**: Stok material otomatis bertambah sesuai jumlah yang di-input.

---

## Modul Pattern

### 1. Melihat Daftar Pattern

**Path**: Dashboard → Master Data → Pattern

**Info yang ditampilkan**:
- Kode & Nama Pattern
- Kategori (Garment, Makanan, dll)
- Tipe Produk (Mukena, Gamis, dll)
- Jumlah Preparation Order yang menggunakan pattern ini

---

### 2. Membuat Pattern Baru

**Path**: Pattern → Tambah Pattern

#### Langkah-langkah:

**A. Informasi Dasar**

1. **Kode Pattern** ⭐ Required
   - Contoh: `MKN-001`, `GMIS-002`

2. **Nama Pattern** ⭐ Required
   - Contoh: `Mukena Dewasa Katun Rayon`

3. **Kategori** ⭐ Required
   - Pilihan: Garment, Makanan, dll

4. **Tipe Produk** ⭐ Required
   - Garment: Mukena, Daster, Gamis, Jilbab, Kemeja, Celana, Lainnya
   - Makanan: Cake, Brownies, Cookies, Roti, Kue Kering, Lainnya

5. **Ukuran** (Optional)
   - Garment: XS, S, M, L, XL, XXL, XXXL, All Size
   - Makanan: 10 pcs, 20 pcs, 1 kg, dll

**B. Spesifikasi**

6. **Deskripsi** (Optional)
   - Detail spesifikasi produk

7. **Target Output per Batch**
   - Jumlah pieces/unit per batch produksi
   - Contoh: 10 pieces

8. **Output Unit**
   - Satuan output (pieces, kg, batch)

9. **Estimasi Material yang Dibutuhkan** (Optional)
   - Estimasi kebutuhan material
   - Contoh: 2.5 meter kain per piece

**C. Status**

10. **Pattern Aktif**
    - ✅ Aktif = Dapat digunakan untuk preparation
    - ❌ Nonaktif = Tidak muncul di dropdown

11. Klik **"Simpan"**

---

### 3. Menggunakan Pattern

Setelah pattern dibuat:
- Pattern dapat dipilih saat membuat **Preparation Order**
- Pattern memberikan **referensi** kebutuhan material
- Anda tetap **input manual** material yang sebenarnya digunakan

---

## Modul Preparation Order

### 1. Apa itu Preparation Order?

**Preparation Order** adalah proses persiapan bahan untuk produksi:
- **Garment**: Proses pemotongan/cutting kain
- **Makanan**: Proses mixing/persiapan adonan

**Fungsi Utama**:
- Track material yang digunakan
- Catat output hasil preparation
- **Auto deduct stock** saat status = completed

---

### 2. Melihat Daftar Preparation Order

**Path**: Dashboard → Preparation Order

**Fitur**:
- 🔍 **Search**: Cari berdasarkan nomor order
- 🏷️ **Filter Status**: Draft, In Progress, Completed, Cancelled
- 📊 **Info**: Nomor order, pattern, output, tanggal, status

**Status Badge**:
- 🔘 **Draft**: Masih rencana, stok belum dipotong
- 🔵 **In Progress**: Sedang dikerjakan, stok belum dipotong
- ✅ **Completed**: Selesai, stok otomatis dipotong
- ❌ **Cancelled**: Dibatalkan

---

### 3. Membuat Preparation Order

**Path**: Preparation Order → Tambah Preparation

#### Langkah-langkah:

**A. Informasi Dasar**

1. **Pattern** (Optional)
   - Pilih pattern sebagai **referensi**
   - Atau kosongkan untuk custom preparation
   - **Note**: Pattern hanya sebagai acuan, tidak auto-fill material

2. **Tanggal Order** ⭐ Required

3. **Penanggung Jawab** (Optional)
   - Pilih staff yang handle preparation

**B. Material yang Digunakan** ⭐ **PENTING**

Ini adalah **inti dari preparation order**:

**Cara Input Material**:

1. Klik tombol **"+ Tambah Material"**
2. Akan muncul form baru:
   - **Material**: Pilih dari dropdown
     - Dropdown menampilkan stok tersedia
     - Format: `Nama Material (Stock: 100 meter)`
   - **Jumlah**: Input jumlah yang digunakan
   - **Satuan**: Auto-fill dari material (read-only)

3. Untuk menambah material lain:
   - Klik **"+ Tambah Material"** lagi
   - Ulangi langkah di atas

4. Untuk menghapus material:
   - Klik tombol **X** merah di sebelah kanan

**Contoh Input Material (Garment - Mukena)**:
```
Material 1:
- Material: Kain Katun Rayon Premium (Stock: 150 meter)
- Jumlah: 25
- Satuan: meter

Material 2:
- Material: Benang Hitam (Stock: 50 roll)
- Jumlah: 2
- Satuan: roll

Material 3:
- Material: Renda Bordir (Stock: 30 meter)
- Jumlah: 5
- Satuan: meter
```

**C. Output Hasil Preparation**

5. **Jumlah Output** ⭐ Required
   - Hasil output dari preparation
   - Contoh: 10 (untuk 10 pieces potongan kain)

6. **Satuan Output** ⭐ Required
   - Satuan hasil output
   - Contoh: pieces, kg, batch, set

**D. Status dan Catatan**

7. **Status** ⭐ Required
   - **Draft**: Order masih rencana, **stok TIDAK dipotong**
   - **In Progress**: Sedang dikerjakan, **stok TIDAK dipotong**
   - **Completed**: Selesai, **stok OTOMATIS DIPOTONG** ⚠️
   - **Cancelled**: Dibatalkan

   ⚠️ **PENTING**: 
   - Pilih **Completed** hanya jika preparation sudah selesai
   - Saat status = Completed, stok material langsung berkurang
   - Tidak bisa undo, pastikan data sudah benar!

8. **Catatan** (Optional)
   - Catatan tambahan tentang preparation

9. Klik **"Simpan"**

---

### 4. Validation & Stock Check

**Saat Menyimpan**, sistem akan:

1. ✅ **Cek Stok Availability**
   - Validasi apakah stok material cukup
   - Jika tidak cukup → ERROR, order tidak disimpan
   - Tampilkan material mana yang kurang

2. ✅ **Save Order**
   - Jika status = Draft/In Progress:
     - Order disimpan
     - Stok **BELUM** berkurang
   
   - Jika status = Completed:
     - Order disimpan
     - Stok **LANGSUNG** berkurang otomatis

---

### 5. Mengubah Preparation Order

**Path**: Preparation Order → Klik "Edit" pada order

**Aturan Edit**:
- ✅ Bisa edit jika status = **Draft** atau **In Progress**
- ❌ Tidak bisa edit jika status = **Completed** atau **Cancelled**

**Saat Mengubah Status ke Completed**:
- Sistem akan auto deduct stok material
- Pastikan data sudah benar sebelum save!

---

### 6. Melihat Detail Preparation Order

**Path**: Preparation Order → Klik "Detail" pada order

**Info yang ditampilkan**:
- **Header**: Nomor order, tanggal, status, pattern
- **Material yang Digunakan**: 
  - Tabel material dengan jumlah & satuan
  - Total cost (jika ada harga material)
- **Output**: Jumlah dan satuan output
- **Penanggung Jawab**: Staff yang handle
- **Production Orders**: List production yang menggunakan prep order ini
- **Catatan**: Notes tambahan

---

## Modul Production Order

### 1. Apa itu Production Order?

**Production Order** adalah proses produksi dari hasil preparation menjadi barang jadi:
- **Garment**: Proses jahit/sewing
- **Makanan**: Proses baking/cooking

**Tipe Production**:
- **Internal**: Dikerjakan oleh staff sendiri
- **External**: Di-outsource ke kontraktor/penjahit

---

### 2. Membuat Production Order

**Path**: Production Order → Tambah Production

**Precondition**: Harus ada **Preparation Order** yang sudah **Completed**

#### Langkah-langkah:

1. **Nomor Order** (Auto-generated)
   - Format: `PRD-2026-001`

2. **Preparation Order** ⭐ Required
   - Pilih dari preparation yang sudah completed
   - Dropdown menampilkan: pattern, output quantity

3. **Tipe Production** ⭐ Required
   - **Internal**: Dikerjakan sendiri
   - **External**: Outsource ke kontraktor

4. **Kontraktor** (Required jika External)
   - Pilih kontraktor/penjahit
   - Hanya muncul jika tipe = External

5. **Jumlah yang Diminta** ⭐ Required
   - Berapa banyak yang ingin diproduksi

6. **Biaya Tenaga Kerja** (Optional)
   - Cost per unit atau total
   - Untuk external: biaya ke kontraktor

7. **Tanggal**:
   - **Tanggal Permintaan** ⭐ Required
   - **Tanggal Deadline** (Optional)
   - **Tanggal Kirim** (jika external)
   - **Tanggal Selesai** (update saat produksi selesai)

8. **Priority**
   - Low, Normal, High, Urgent

9. **Status**
   - Pending, Sent (external), In Progress, Completed, Cancelled

10. **Catatan** (Optional)

11. Klik **"Simpan"**

---

### 3. Tracking Production

**Update Status Production**:
- **Pending** → **Sent** (jika external)
- **Sent** → **In Progress** (saat mulai dikerjakan)
- **In Progress** → **Completed** (saat selesai)

**Saat Completed**:
- Input hasil produksi:
  - Jumlah yang diproduksi
  - Jumlah bagus (Grade A/B)
  - Jumlah reject
  - Catatan penyelesaian

---

## Tips & Best Practices

### 1. Material Management

✅ **DO**:
- Gunakan kode material yang konsisten (misal: `KA-xxx` untuk kain)
- Isi atribut material selengkap mungkin
- Set reorder point yang realistis
- Update stok via Material Receipt, bukan edit langsung

❌ **DON'T**:
- Jangan edit stok manual di master material
- Jangan hapus material yang sudah digunakan dalam preparation

---

### 2. Pattern Usage

✅ **DO**:
- Buat pattern untuk produk yang sering dibuat
- Gunakan pattern sebagai referensi kebutuhan material
- Update pattern jika ada perubahan spesifikasi

❌ **DON'T**:
- Jangan mengandalkan pattern untuk auto-fill material
- Pattern hanya **referensi**, tetap input manual di preparation order

---

### 3. Preparation Order

✅ **DO**:
- Gunakan status **Draft** untuk planning
- Cek stok material sebelum membuat order
- Set status **Completed** hanya jika benar-benar selesai
- Double-check jumlah material sebelum save completed

❌ **DON'T**:
- Jangan langsung set status Completed jika belum selesai
- Jangan lupa input material yang digunakan
- Jangan abaikan warning stok kurang

---

### 4. Workflow Efficiency

✅ **Best Practice Workflow**:

**Scenario 1: Ada Pattern**
```
1. Input Material Receipt (stok tersedia)
2. Pilih Pattern existing
3. Buat Preparation Order
   - Pilih pattern
   - Input material yang actually digunakan
   - Set status Completed
4. Buat Production Order dari prep order
```

**Scenario 2: Tanpa Pattern (Custom)**
```
1. Input Material Receipt (stok tersedia)
2. Langsung Buat Preparation Order
   - Skip pattern selection
   - Input material yang digunakan
   - Input output quantity/unit
   - Set status Completed
3. Buat Production Order dari prep order
```

**Scenario 3: Planning Phase**
```
1. Buat Preparation Order dengan status Draft
2. Review dengan tim
3. Pastikan material tersedia
4. Update status ke In Progress saat mulai
5. Update status ke Completed saat selesai
```

---

## FAQ

### Q1: Bagaimana cara menambah stok material?

**A**: Gunakan **Material Receipt** (Penerimaan Barang):
- Buka detail material
- Klik tab "Penerimaan"
- Tambah penerimaan baru
- Stok otomatis bertambah

### Q2: Apakah pattern wajib digunakan?

**A**: **Tidak wajib**. Pattern hanya sebagai referensi. Anda bisa skip pattern dan langsung input material di preparation order.

### Q3: Kapan stok material berkurang?

**A**: Stok berkurang saat **Preparation Order** di-set status **Completed**. Auto deduct sesuai material yang di-input.

### Q4: Bisa undo preparation order yang sudah completed?

**A**: **Tidak bisa**. Status completed akan langsung potong stok. Pastikan data benar sebelum save!

### Q5: Apa bedanya Draft dan In Progress?

**A**: 
- **Draft**: Masih planning, belum mulai kerja, stok belum dipotong
- **In Progress**: Sudah mulai dikerjakan, tapi stok belum dipotong
- Keduanya belum potong stok, hanya **Completed** yang potong stok

### Q6: Bisa edit preparation order setelah completed?

**A**: **Tidak bisa**. Order yang sudah completed atau cancelled tidak bisa diedit.

### Q7: Material yang digunakan harus sesuai pattern?

**A**: **Tidak harus**. Pattern hanya referensi. Anda input manual material yang **actually used** di preparation order.

### Q8: Bagaimana jika stok material tidak cukup?

**A**: Sistem akan menolak saat save dan tampilkan error. Anda harus:
1. Tambah stok via Material Receipt, atau
2. Kurangi jumlah material yang digunakan

### Q9: Bisa buat preparation tanpa output?

**A**: **Tidak**. Output quantity dan unit wajib diisi. Ini adalah hasil dari proses preparation.

### Q10: Apakah preparation order bisa dihapus?

**A**: Preparation order yang sudah **Completed** tidak bisa dihapus karena sudah potong stok. Draft dan In Progress bisa dihapus (jika belum ada production order yang terkait).

---

## Support & Contact

Jika ada pertanyaan atau kendala:
- 📧 Email: support@fabriku.com
- 📱 WhatsApp: +62 xxx-xxxx-xxxx
- 📚 Dokumentasi: https://docs.fabriku.com

---

**Happy Managing! 🎉**
