# Enhancement Plans

> **Status (Juni 2026)**: Plan A (Retail), Plan B (Homemade), dan Plan C (Service/Jasa — termasuk laporan layanan, staff assignment, consumable auto-deduct) **selesai dan dipindah** ke `docs/current-status.md`. Sales Order CRITICAL #1-3 (Update Status, fix observer `shipped`, edit dikunci ke `draft`) dan HIGH (catatan pembayaran via tabel `payments` + refund saat cancel) **selesai**. Suite test lengkap hijau (360 passed). Dokumen ini berisi backlog MEDIUM/LOW yang belum dikerjakan.

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

### MEDIUM

1. **`payment_due_date` mati → tidak ada penanda jatuh tempo.** *(versi UMKM dari AR aging)*
   Kolom ada + di-cast (`SalesOrder.php`) tapi tak pernah diisi/dipakai. **Versi UMKM:** input tanggal jatuh tempo di form + badge "jatuh tempo/overdue" di Index/Show + filter "piutang overdue". **Tidak perlu** laporan aging berember 0-30/31-60/61-90 ala ERP. Reminder terjadwal opsional menyusul.

2. **`shipping_cost` kolom orphan.**
   Ada di tabel + factory, tapi **tidak** dipakai di Store/Update/Form/Show — total tak menghitung ongkir. Putuskan: aktifkan (input form + masuk `total_amount`) atau drop kolomnya. Keputusan kecil, tidak ada nuansa ERP.

3. **`invoice_number` manual, tanpa auto-generate.** *(P-OPSI 6, versi UMKM)*
   `order_number` sudah auto-sequence (`SalesOrder::generateOrderNumber()`), tapi `invoice_number` text bebas → bisa kosong/duplikat. **Versi UMKM:** auto-generate + unik per tenant (mis. `INV/2026/06/0001`), terbit saat confirm. **Tidak perlu** gapless legal sequence (itu kebutuhan PKP/PPN — lihat scope di bawah).

### LOW

4. **Pajak (`tax_amount`) input nominal manual.** *(opsional, hanya bila ada tenant PKP)*
   Tidak ada `tax_rate`/PPN 11% otomatis/toggle inklusif. Untuk UMKM non-PKP (mayoritas) ini **cukup apa adanya** — angkat hanya kalau menargetkan tenant PKP. Bukan prioritas.

5. **`print()` salah nama.** Mengembalikan Inertia page (bukan PDF). Rename `invoice()` + update route & frontend. Sudah tercatat di `docs/code-review.md`.

6. **Export "Invoice" hanya CSV + belum ada PDF.** `Print.vue` cuma render layar; belum ada PDF resmi untuk dilampirkan ke pelanggan. Nice-to-have.

### Sengaja DI LUAR scope (overkill untuk UMKM)

- Pecah SO/Delivery/Invoice/Payment jadi tabel terpisah — cukup satu `sales_orders` + tabel `payments` sederhana.
- Dokumen credit note akuntansi formal, jurnal double-entry, rekonsiliasi bank.
- Gapless legal invoice numbering, tax engine per-baris, e-Faktur — hanya relevan kalau menargetkan PKP.
- Partial delivery / partial invoicing / backorder, multi-currency, dunning otomatis, ATP/MRP.

### Urutan eksekusi yang disarankan

1. **#1** jatuh tempo / overdue → **#2** ongkir → **#3** auto invoice number.
2. **#4–#6** (PKP/PPN, PDF, rename) hanya kalau ada kebutuhan nyata — jangan dikerjakan spekulatif.

> Sisa item di sini cleanup/nice-to-have, tidak ada lagi yang mengganggu integritas stok/uang.

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
