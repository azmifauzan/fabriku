# Enhancement Plans

> **Status (Juni 2026)**: Plan A (Retail), Plan B (Homemade), dan Plan C (Service/Jasa — termasuk laporan layanan, staff assignment, consumable auto-deduct) **selesai dan dipindah** ke `docs/current-status.md`. Suite test lengkap hijau (340 passed). Dokumen ini berisi backlog yang belum dikerjakan.

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

### Temuan inti: lifecycle status terkunci

Satu-satunya jalur mengubah `status` SO (selain create=`draft` dan quick-checkout=`completed`) adalah **Edit form**, dan Edit hanya bisa dibuka kalau `canBeEdited()` true (`SalesOrder.php:117`) = status `draft`/`confirmed` (atau `completed` yang belum `paid`). Akibatnya:

- `draft → confirmed → processing` masih bisa via Edit.
- **`processing` ke atas mentok total** — tidak ada UI/endpoint untuk `processing → shipped`, `→ completed`, atau `→ cancelled`. Order nyangkut di `processing` selamanya.
- Tombol Edit (`Show.vue:23`, `Index.vue:348`) dan tombol Hapus (`Index.vue:356`, hanya `draft`) ikut terkunci.

### CRITICAL — bug nyata, wajib fix

1. **Tidak ada fitur transisi status pesanan (`processing`/`shipped` → lanjut).** *(P-INTI 1)*
   Order nyangkut di `processing` selamanya — ini bug fungsional, bukan kemewahan ERP. Butuh endpoint + modal "Update Status" terpisah dari Edit:
   - transition map: `processing` → `shipped`/`completed`/`cancelled`; `shipped` → `completed`/`cancelled`.
   - validasi di backend: tolak transisi di luar map (422), jangan percaya input frontend.
   - set via `$salesOrder->update(['status' => ...])` → `SalesOrderObserver` menangani stok.
   - `resi_number` opsional diisi saat target `shipped` (sekarang resi cuma bisa diisi di Edit yang keburu terkunci).
   - opsi `cancelled` masuk modal ini (bukan soft-delete) supaya order tetap tercatat; konfirmasi via SweetAlert karena stok balik.
   - Permission `sales.edit`. Route `PATCH sales-orders/{id}/update-status`.
   - Cukup map sederhana (helper di model atau Form Request) — **tidak perlu** workflow engine.

2. **Bug observer: transisi dari `shipped` tidak menyentuh stok.** *(P-INTI 1)*
   `SalesOrderObserver::updated()` (`:29`,`:34`) hanya cek origin `confirmed`/`processing`. `deleted()` (`:49`) & `forceDeleting()` (`:73`) sama. Begitu `shipped` bisa dicapai (lihat #1):
   - `shipped → completed` **tidak deduct stok**; `shipped → cancelled` / hapus order `shipped` **tidak release reserved stock** → stok bocor.
   - Fix: tambahkan `'shipped'` ke ketiga pengecekan `in_array(...)`. Wajib barengan #1.

3. **Edit SO terkonfirmasi bisa bikin stok desync.** *(P-INTI 3, versi UMKM)*
   `update()` (`:243-244`) menghapus semua `items` lalu `createMany` ulang; stok hanya benar kalau `status` ikut berubah (komentar `:238-242`). Edit item tanpa ganti status → stok desync. **Versi UMKM (bukan immutability penuh):** UMKM tetap boleh edit, tapi edit harus aman — saat item berubah pada order yang sudah reserve stok, lakukan release-lama + reserve-baru di dalam transaksi. Atau lebih simpel: kunci Edit penuh ke `draft` saja, dan untuk `confirmed`+ arahkan ke aksi spesifik (update status, update payment). **Jangan** paksa pola credit-note untuk koreksi typo. Berkaitan dengan #1.

### HIGH — right-sized untuk UMKM

4. **Tidak ada catatan pembayaran (DP + pelunasan).** *(P-INTI 2)*
   `paid_amount` satu kolom yang ditimpa tiap update (termasuk fitur Update Pembayaran baru). DP lalu pelunasan — sangat umum di UMKM — tidak punya jejak kapan/berapa/metode. **Versi UMKM:** tabel `payments` **sederhana** (`sales_order_id`, `tenant_id`, `amount`, `method`, `paid_at`, `note`); `paid_amount` = SUM, `payment_status` = turunan. Update Pembayaran yang ada di-refactor jadi "tambah baris pembayaran". **Tidak perlu** alokasi multi-invoice atau rekonsiliasi bank. Fondasi untuk #5.

5. **Tidak ada refund + retur barang.** *(P-OPSI 4)*
   Enum `payment_status = 'refunded'` ada tapi tak pernah di-set. Order berbayar lalu batal → uang kembali tidak tercatat, stok retur tidak masuk lagi. **Versi UMKM:** saat cancel order berbayar, catat baris `payments` bernilai negatif (refund) + (kalau barang balik) kembalikan stok; set `payment_status = 'refunded'`. **Tidak perlu** dokumen credit note akuntansi formal. Butuh #4.

### MEDIUM

6. **`payment_due_date` mati → tidak ada penanda jatuh tempo.** *(versi UMKM dari AR aging)*
   Kolom ada + di-cast (`SalesOrder.php:34`,`:45`) tapi tak pernah diisi/dipakai. **Versi UMKM:** input tanggal jatuh tempo di form + badge "jatuh tempo/overdue" di Index/Show + filter "piutang overdue". **Tidak perlu** laporan aging berember 0-30/31-60/61-90 ala ERP. Reminder terjadwal opsional menyusul.

7. **`shipping_cost` kolom orphan.**
   Ada di tabel + factory, tapi **tidak** dipakai di Store/Update/Form/Show — total tak menghitung ongkir. Putuskan: aktifkan (input form + masuk `total_amount`) atau drop kolomnya. Keputusan kecil, tidak ada nuansa ERP.

8. **`invoice_number` manual, tanpa auto-generate.** *(P-OPSI 6, versi UMKM)*
   `order_number` sudah auto-sequence (`SalesOrder.php:83`), tapi `invoice_number` text bebas → bisa kosong/duplikat. **Versi UMKM:** auto-generate + unik per tenant (mis. `INV/2026/06/0001`), terbit saat confirm. **Tidak perlu** gapless legal sequence (itu kebutuhan PKP/PPN — lihat scope di bawah).

### LOW

9. **Pajak (`tax_amount`) input nominal manual.** *(opsional, hanya bila ada tenant PKP)*
   Tidak ada `tax_rate`/PPN 11% otomatis/toggle inklusif. Untuk UMKM non-PKP (mayoritas) ini **cukup apa adanya** — angkat hanya kalau menargetkan tenant PKP. Bukan prioritas.

10. **`print()` salah nama.** Mengembalikan Inertia page (bukan PDF). Rename `invoice()` + update route & frontend. Sudah tercatat di `docs/code-review.md`.

11. **Export "Invoice" hanya CSV + belum ada PDF.** `Print.vue` cuma render layar; belum ada PDF resmi untuk dilampirkan ke pelanggan. Nice-to-have.

### Sengaja DI LUAR scope (overkill untuk UMKM)

- Pecah SO/Delivery/Invoice/Payment jadi tabel terpisah — cukup satu `sales_orders` + tabel `payments` sederhana.
- Dokumen credit note akuntansi formal, jurnal double-entry, rekonsiliasi bank.
- Gapless legal invoice numbering, tax engine per-baris, e-Faktur — hanya relevan kalau menargetkan PKP.
- Partial delivery / partial invoicing / backorder, multi-currency, dunning otomatis, ATP/MRP.

### ✅ Sudah dikerjakan (sesi ini)

- **Update Pembayaran** — modal di `Show.vue` + endpoint `PATCH sales-orders/{id}/update-payment` (`updatePayment`, permission `sales.edit`). Validasi `paid_amount` (0..`total_amount`), auto-derive `payment_status` (unpaid/partial/paid), blokir status `cancelled`. 6 test di `tests/Feature/SalesOrderUpdatePaymentTest.php`. Mengisi sebagian gap "ubah pembayaran setelah order tidak bisa diedit", tapi **belum** catatan pembayaran (#4) — saat tabel `payments` dibuat, fitur ini di-refactor jadi "tambah baris pembayaran".

### Urutan eksekusi yang disarankan

1. **#1 + #2 + #3 bareng** (modal Update Status + fix observer `shipped` + edit aman) — saling bergantung, semua menyentuh observer & lifecycle. Nilai tertinggi, ini yang bikin alur dasar tidak nyangkut.
2. **#4** catatan pembayaran sederhana (fondasi uang; refactor fitur Update Pembayaran ke ledger).
3. **#5** refund + retur (butuh #4).
4. **#6** jatuh tempo / overdue → **#7** ongkir → **#8** auto invoice number.
5. **#9–#11** (PKP/PPN, PDF, rename) hanya kalau ada kebutuhan nyata — jangan dikerjakan spekulatif.

> #1–#3 inti integritas stok; #4–#5 inti integritas uang. Sisanya cleanup/nice-to-have. Putuskan desain #1–#3 sekaligus sebelum ngoding supaya observer tidak digeser berulang.

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
