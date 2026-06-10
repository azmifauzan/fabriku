# Enhancement Plans

> **Status (Juni 2026)**: Plan A (Retail), Plan B (Homemade), dan Plan C (Service/Jasa — termasuk laporan layanan, staff assignment, consumable auto-deduct) **selesai dan dipindah** ke `docs/current-status.md`. Suite test lengkap hijau (340 passed). Dokumen ini berisi backlog yang belum dikerjakan.

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
