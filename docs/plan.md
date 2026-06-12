# Enhancement Plans

> **Status (Juni 2026)**: Plan A (Retail), Plan B (Homemade), dan Plan C (Service/Jasa — termasuk laporan layanan, staff assignment, consumable auto-deduct) **selesai dan dipindah** ke `docs/current-status.md`. Sales Order CRITICAL #1-3 (Update Status, fix observer `shipped`, edit dikunci ke `draft`) dan HIGH (catatan pembayaran via tabel `payments` + refund saat cancel) **selesai**. Sales Order MEDIUM #1-3 (jatuh tempo/overdue, shipping_cost aktif, auto invoice number) **selesai**. LOW backlog dari `docs/code-review.md` (rename `print()`→`invoice()`, standarisasi `paginate()`, unit test domain logic, refactor query `InventoryItemController`) **selesai**. Suite test lengkap hijau (377 passed, 6 skipped). Sisa backlog di dokumen ini scope-nya lintas kategori / out-of-scope, bukan technical debt.

---

## Sales Order / Invoicing — Gap Analysis & ERP Alignment (Juni 2026)

> Audit menyeluruh alur sales order + invoicing, di-benchmark ke praktik ERP standar (Order-to-Cash). Sumber kebenaran: `SalesOrderController`, `SalesOrderObserver`, `app/Models/SalesOrder.php`, `resources/js/pages/SalesOrders/*`. Diurutkan per severity. Item ✅ sudah dikerjakan di sesi ini.

### Model referensi: Order-to-Cash (O2C) — versi UMKM

ERP matang (SAP, Odoo, NetSuite) memisahkan SO → Delivery/Goods Issue → Invoice → Payment jadi 4 dokumen. Fabriku menggabung semua ke satu baris `sales_orders` — dan untuk UMKM **itu keputusan yang benar**, jangan dipecah. Yang diambil dari ERP hanya **prinsip yang melindungi integritas stok & uang**, masing-masing sudah di-right-size ke skala UMKM:

| Prinsip | Versi UMKM (target) | Versi enterprise (DILEWATI) |
|---|---|---|
| **P-INTI 1 — State machine status** | transisi terbatas & divalidasi backend; tidak ada order nyangkut | workflow engine, approval bertingkat |
| **P-INTI 2 — Catatan pembayaran** | tabel `payments` sederhana (tanggal, jumlah, metode, catatan) untuk DP + pelunasan | alokasi multi-invoice, rekonsiliasi bank |
| **P-INTI 3 — Edit aman** | edit tidak boleh bikin stok desync; batasi field yang bisa diubah setelah confirm | dokumen posted 100% imutabel, koreksi via revisi terkontrol |
| **P-OPSI 4 — Refund + retur** | catat uang kembali di `payments` (nilai negatif) + stok balik | credit note sebagai dokumen akuntansi formal |
| **P-OPSI 5 — Goods issue saat kirim** | (opsional) deduct stok di `shipped` bukan `completed` | delivery note terpisah, partial delivery |
| **P-OPSI 6 — Nomor invoice rapi** | auto-generate, unik per tenant, tidak kosong | sequence legal gapless (wajib PKP/PPN) |

Banyak UMKM bahkan melewati status `shipped` (langsung `draft → completed`), jadi P-OPSI 4/5/6 sengaja prioritas rendah. Temuan di bawah dipetakan ke prinsip ini.

### ✅ CRITICAL selesai (Juni 2026) — lifecycle status & edit aman

Sebelumnya: satu-satunya jalur ubah `status` SO adalah Edit form (terkunci di `draft`/`confirmed`/`completed`-belum-`paid`), jadi `processing`/`shipped` mentok tanpa jalur lanjut. Fix:

- **Update Status modal** — `StatusUpdateModal.vue` (Index & Show) + `PATCH sales-orders/{id}/update-status` (`UpdateStatusRequest`, permission `sales.edit`). State machine `SalesOrder::transitionMap()`: `draft→{confirmed,processing,cancelled}`, `confirmed→{processing,shipped,completed,cancelled}`, `processing→{shipped,completed,cancelled}`, `shipped→{completed,cancelled}`. Transisi di luar map → 422. `resi_number`+`shipped_date` diisi saat target `shipped`; `completed_date` saat `completed`.
- **Observer fix `shipped`** — `'shipped'` ditambahkan ke ketiga `in_array` check di `SalesOrderObserver` (`updated`/`deleted`/`forceDeleting`), jadi `shipped→completed` deduct stok dan `shipped→cancelled`/hapus order `shipped` release reserved stock.
- **Edit dikunci ke `draft`** — `canBeEdited()` sekarang strict `status === 'draft'` (sebelumnya juga true untuk `confirmed`/`completed`-belum-`paid`, sumber stock desync). Confirmed+ pakai Update Status / Update Pembayaran.
- Test: `tests/Feature/SalesOrderUpdateStatusTest.php` (16 test).

Detail lengkap di `docs/code-review.md` (bagian SalesOrder).

### ✅ HIGH selesai (Juni 2026) — catatan pembayaran + refund

- **Tabel `payments`** (`tenant_id`, `sales_order_id`, `amount`, `method`, `paid_at`, `note`) — `paid_amount` = `payments()->sum('amount')`, `payment_status` derivasi (`unpaid`/`partial`/`paid`). Endpoint `updatePayment` di-refactor jadi "tambah baris pembayaran" (`StorePaymentRequest`); `PaymentUpdateModal.vue` rewrite (amount/method/paid_at/note); `Show.vue` tambah tabel "Riwayat Pembayaran".
- **Refund saat cancel order berbayar** — `updateStatus()`: bila transisi ke `cancelled` dan `paid_amount > 0`, catat baris `payments` negatif (`method='refund'`) + set `paid_amount=0`, `payment_status='refunded'`. Badge `refunded` di Show.vue.
- **Stok retur**: tidak ada langkah tambahan — `transitionMap()` hanya izinkan `cancelled` dari `confirmed/processing/shipped` (belum `completed`, stok masih *reserved* bukan *deducted*), jadi `SalesOrderObserver`'s existing `releaseReservedStock` pada `→cancelled` sudah cukup. Skenario "retur barang dari order `completed`" sengaja di luar scope (butuh transisi `completed→cancelled` + restock logic terpisah — lihat backlog bila dibutuhkan).
- Test: `tests/Feature/SalesOrderUpdatePaymentTest.php` (rewrite, 6 test) + tambahan refund test di `SalesOrderUpdateStatusTest.php`.

### ✅ MEDIUM selesai (Juni 2026) — jatuh tempo, ongkir, auto invoice number

1. **`payment_due_date` aktif.** Input tanggal jatuh tempo di form (Create/Edit). Badge "Jatuh Tempo" di Index + Show. Badge "Overdue" di kolom pembayaran Index + Show (merah, muncul bila `payment_status ∈ {unpaid,partial}` + `status ≠ cancelled` + tanggal sudah lewat). Filter `?payment_status=overdue` di controller + opsi dropdown di Index. Test: `it stores payment due date correctly` + `it can filter sales orders by overdue payment status`.

2. **`shipping_cost` aktif.** Input ongkos kirim di form (di bagian kalkulasi). Masuk `total_amount = subtotal - discount + tax + shipping_cost` di frontend dan backend (store + update). Ditampilkan di Show.vue (tfoot) dan masuk di CSV export. Test: `it calculates totals correctly with shipping cost`.

3. **`invoice_number` auto-generate.** `SalesOrder::booted()` `saving` hook: generate `INV/YYYY/MM/NNNN` unik per tenant per bulan saat SO pertama kali keluar dari `draft` (bukan `cancelled`). Manual override tetap dimungkinkan (input teks di form). Tampil di Index dan Show. Test: `it auto-generates invoice number when transitioning to confirmed`.

Fix tambahan (dari code review): status dropdown di Form.vue (create/edit) diganti read-only badge "Draft" + keterangan — menghilangkan UI menyesatkan karena backend selalu force `draft` saat create dan state machine hanya bisa diubah via `updateStatus`; bug semantik komparasi tanggal overdue di Index + Show diperbaiki (`new Date(new Date().setHours(0,0,0,0))`).

### ✅ LOW selesai (Juni 2026) — rename, standarisasi paginate, unit test, refactor query

1. **`print()` → `invoice()`.** `SalesOrderController::print` di-rename `invoice()`; route `sales-orders/{id}/print` → `sales-orders/{id}/invoice` (`sales-orders.invoice`); `Print.vue` → `Invoice.vue`. Link "Print Invoice" di Index/Show dan `docs/04-api-endpoints.md` ikut diupdate. Test: `it can print invoice for a sales order` (rewrite ke `sales-orders.invoice` + komponen `SalesOrders/Invoice`).

2. **`paginate()` distandarkan.** `Controller::DEFAULT_PER_PAGE = 15` (constant baru di base `Controller`). Semua controller (Sales, Inventory, Admin Audit/Monitoring/Payment/Tenant/User, Contractor, Customer, Material, MaterialType, Pattern, PreparationOrder, ProductionOrder, PurchaseReceipt, Service, SimpleProduction, Staff, InventoryLocation) pakai `self::DEFAULT_PER_PAGE` — sebelumnya campur `15`/`20`.

3. **Unit test domain logic.** `tests/Unit/InventoryItemSkuTest.php` (prefix SKU per kategori dari `config/business.php` + auto-increment + skip SKU yang sudah ada), `tests/Unit/MaterialStockServiceTest.php` (cek ketersediaan stok per batch + deduct FIFO lintas `MaterialReceipt`), `tests/Unit/SalesOrderStatusTransitionTest.php` (6 test, semua state `transitionMap()`). `tests/Pest.php` extend `Unit` selain `Feature`. Factory `SalesOrderFactory` tambah state `shipped()`/`cancelled()`.

4. **`InventoryItemController::create/edit` query dipindah ke service.** `InventoryService::getFormDataForCreateOrEdit(?InventoryItem $item = null)` — return `locations`, `patterns`, `categories`, `productionOrders` (termasuk kalkulasi `material_cost`). Controller cuma panggil service + render Inertia.

Test suite penuh: 377 passed, 6 skipped.

---

## Backlog Lintas Kategori

- Payment gateway terintegrasi (Midtrans/Xendit) — subscription masih manual upload bukti.
- Shipping API (JNE/JNT/SiCepat).
- Multi-warehouse (saat ini `inventory_locations` per tenant tunggal).
- Multi-bahasa (semua hardcoded Bahasa Indonesia).
- Mobile app native.
- Barcode scanning untuk material (baru inventory yang punya QR).
- Backup/restore otomatis per tenant.

## Out of Scope Kategori `service` (keputusan tetap)

- Booking / appointment / antrian online.
- Perhitungan komisi otomatis (persentase, payroll) — data dasar sudah ada via `served_by` + laporan per staff.
- Work order tracking detail bengkel (status kendaraan) — status SO existing cukup.
- Data aset pelanggan (kendaraan, riwayat servis per unit).
- Paket/membership layanan (voucher 10x cuci).
