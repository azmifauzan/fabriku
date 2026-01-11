# User Flows - Fabriku

## Overview
Dokumen ini menjelaskan alur kerja pengguna (user flows) untuk berbagai proses bisnis di aplikasi Fabriku.

## User Roles & Permissions

### 1. Admin (Tenant Administrator)
- Full access ke semua fitur
- Manage users dan permissions
- Configure tenant settings

### 2. Manager
- View all reports
- Approve critical transactions
- Full CRUD pada semua modules

### 3. Production Staff
- Material receipts
- Cutting orders
- Production orders
- Update production status

### 4. Warehouse Staff
- Inventory management
- Stock locations
- Stock adjustments

### 5. Sales Staff
- Create sales orders
- Manage customers
- Process payments
- View sales reports

### 6. Viewer
- Read-only access
- View reports only

---

## Main User Flows

### Flow 1: Material Receipt Process

**Actor**: Production Staff / Warehouse Staff

**Precondition**: Bahan baku telah diterima dari supplier

**Steps**:
1. User login ke sistem
2. Navigate to "Bahan Baku" → "Penerimaan Baru"
3. System menampilkan form penerimaan
4. User input data:
   - Pilih material (atau buat baru jika belum ada)
   - Nomor penerimaan (auto-generated atau manual)
   - Nama supplier
   - Tanggal penerimaan
   - Jumlah (meter/roll)
   - Jumlah roll & panjang per roll
   - Harga per unit
   - Batch number
   - Catatan (opsional)
   - Upload foto/dokumen (opsional)
5. System validasi input
6. User click "Simpan"
7. System:
   - Simpan data penerimaan
   - Update stok material
   - Generate notification
8. System redirect ke detail penerimaan
9. User dapat print label/barcode (opsional)

**Postcondition**: Stok material bertambah

**Alternative Flow**:
- 5a. Validation error → System tampilkan error message
- 7a. Material belum ada → Create material master terlebih dahulu

---

### Flow 2: Cutting Process

**Actor**: Production Staff

**Precondition**: 
- Material tersedia di gudang
- Pola/pattern sudah dibuat

**Steps**:
1. Navigate to "Produksi" → "Pemotongan" → "Order Baru"
2. System menampilkan form cutting order
3. User input data:
   - Nomor order (auto-generated)
   - Pilih material receipt (dari dropdown)
   - Pilih pola/pattern
   - Jumlah material yang akan digunakan
   - Target jumlah pieces
   - Assign ke operator (opsional)
   - Tanggal order
   - Catatan
4. System kalkulasi estimasi:
   - Estimasi pieces yang akan dihasilkan
   - Estimasi efisiensi
5. User click "Buat Order"
6. System:
   - Simpan cutting order (status: pending)
   - Reserve material
7. System redirect ke detail order

**During Cutting**:
8. Operator start cutting → Click "Mulai Pemotongan"
9. System update status ke "in_progress" dan catat waktu mulai

**After Cutting**:
10. Operator click "Selesai & Input Hasil"
11. System tampilkan form hasil:
    - Jumlah pieces actual
    - Jumlah pieces bagus
    - Jumlah pieces cacat
    - Sisa material (waste)
    - Grade kualitas
    - Catatan
12. User input hasil dan click "Simpan"
13. System:
    - Simpan cutting result
    - Kalkulasi efisiensi actual
    - Update status ke "completed"
    - Kurangi stok material
    - Generate notification
14. System redirect ke detail hasil

**Postcondition**: 
- Cutting order completed
- Material berkurang
- Ready untuk production order

---

### Flow 3: Production (Sewing) Process

**Actor**: Production Staff / Manager

**Precondition**: Cutting result tersedia

#### Internal Production

**Steps**:
1. Navigate to "Produksi" → "Jahit" → "Order Baru"
2. System tampilkan form
3. User input:
   - Nomor order
   - Pilih cutting result
   - Tipe produksi: "Internal"
   - Jumlah pieces
   - Cost per piece (untuk kalkulasi)
   - Target tanggal selesai
4. User click "Buat Order"
5. System simpan production order (status: pending)
6. Operator start → Click "Mulai Produksi"
7. System update status ke "in_progress"
8. After completion → Click "Selesai & Catat Hasil"
9. System tampilkan form:
   - Tanggal selesai
   - Jumlah pieces selesai
   - Jumlah pieces bagus
   - Jumlah pieces cacat
   - Quality grade
   - QC notes
10. System:
    - Simpan production batch
    - Update order status ke "completed"
    - Create inventory items
11. Redirect ke inventory

#### External Production (Outsourcing)

**Steps**:
1. Navigate to "Produksi" → "Jahit" → "Order Baru"
2. System tampilkan form
3. User input:
   - Nomor order
   - Pilih cutting result
   - Tipe produksi: "External"
   - Pilih kontraktor
   - Jumlah pieces
   - Cost per piece
   - Target tanggal kembali
4. User click "Buat Order"
5. System simpan (status: pending)
6. User click "Kirim ke Kontraktor"
7. System:
   - Update status ke "in_progress"
   - Catat tanggal pengiriman
   - Generate surat jalan (opsional)

**When Return from Contractor**:
8. User click "Terima dari Kontraktor"
9. System tampilkan form penerimaan:
   - Batch number
   - Tanggal kembali
   - Jumlah pieces diterima
   - Jumlah pieces bagus
   - Jumlah pieces cacat
   - Quality grade
   - QC notes
10. User input dan click "Simpan"
11. System:
    - Simpan production batch
    - Update order status ke "returned"
    - Create inventory items dari pieces bagus
    - Generate notification ke kontraktor (rating/feedback)
12. Redirect ke inventory

**Postcondition**: 
- Production batch created
- Inventory items available

---

### Flow 4: Inventory Management

**Actor**: Warehouse Staff

#### Store to Location

**Steps**:
1. Navigate to "Gudang" → "Inventori"
2. System tampilkan list inventory items
3. User filter/search item yang baru masuk (tanpa lokasi)
4. User click item → Detail page
5. User click "Atur Lokasi"
6. System tampilkan dropdown lokasi penyimpanan
7. User pilih lokasi (Rak)
8. User click "Simpan"
9. System:
   - Update inventory item location
   - Update stored_at timestamp
10. System tampilkan success message

#### Stock Adjustment

**Steps**:
1. Navigate to "Gudang" → "Inventori"
2. User click item yang akan disesuaikan
3. User click "Penyesuaian Stok"
4. System tampilkan form:
   - Current quantity (readonly)
   - Adjustment type (Tambah/Kurang)
   - Quantity adjustment
   - Reason (dropdown: stock opname, damage, lost, found, etc.)
   - Notes
5. User input dan click "Simpan"
6. System:
   - Validate adjustment
   - Update inventory quantity
   - Log adjustment ke audit trail
7. System tampilkan success message

**Postcondition**: Stock quantity updated

---

### Flow 5: Sales Process

**Actor**: Sales Staff

**Precondition**: 
- Customer registered (or walk-in)
- Inventory available

**Steps**:
1. Navigate to "Penjualan" → "Order Baru"
2. System tampilkan form sales order
3. User input:
   - Nomor order (auto-generated)
   - Pilih/tambah customer
   - Tanggal order
   - Sales channel (offline/online/marketplace/reseller)
4. User click "Tambah Item"
5. System tampilkan modal:
   - Search/pilih inventory item (filter by pattern, available stock)
   - System tampilkan available stock
   - Input quantity (validasi <= available stock)
   - System auto-fill selling price (editable)
   - Input discount per item (opsional)
6. User click "Tambah" → Item masuk ke order
7. Repeat step 4-6 untuk item lain
8. User input:
   - Discount order (opsional)
   - Ongkir (opsional)
   - Payment method
   - Notes
9. System auto-kalkulasi:
   - Subtotal
   - Total discount
   - Total amount
10. User review order
11. User click "Buat Order"
12. System:
    - Validate stock availability
    - Create sales order (status: pending)
    - Reserve inventory (update reserved_quantity)
    - Generate notification
13. System tampilkan preview order
14. User option:
    - "Cetak Invoice" → PDF invoice
    - "Konfirmasi Order" → Continue to confirmation

**Order Confirmation**:
15. User click "Konfirmasi Order"
16. System:
    - Update status ke "confirmed"
    - Deduct inventory (reduce current_quantity)
    - Update payment status (jika cash/transfer langsung)
17. Redirect ke detail order

**Payment Processing** (jika belum lunas):
18. User click "Catat Pembayaran"
19. System tampilkan form:
    - Amount (max: remaining amount)
    - Payment method
    - Payment date
    - Notes/reference number
20. User input dan click "Simpan"
21. System:
    - Record payment
    - Update paid_amount
    - Update payment_status (partial/paid)
    - Generate receipt
22. If fully paid → Send notification

**Postcondition**: 
- Sales order created
- Inventory reduced
- Revenue recorded

---

### Flow 6: Order Cancellation

**Actor**: Sales Staff / Manager

**Steps**:
1. Navigate to order detail
2. User click "Batalkan Order"
3. System tampilkan confirmation dialog
4. User input reason untuk pembatalan
5. User confirm
6. System:
   - Update order status ke "cancelled"
   - Restore inventory (unreserve/add back)
   - If payment received → Note for refund process
   - Log cancellation
7. System tampilkan success message

**Postcondition**: 
- Order cancelled
- Stock restored

---

### Flow 7: Report Generation

**Actor**: Manager / Admin

#### Dashboard View
1. User login
2. System auto-redirect ke Dashboard (atau click "Dashboard")
3. System load dan tampilkan:
   - KPI cards (sales today, sales month, inventory value, alerts)
   - Sales trend chart (last 30 days)
   - Top products chart
   - Production efficiency chart
   - Recent transactions
   - Low stock alerts
4. User dapat click chart untuk drill-down detail

#### Detailed Reports

**Material Report**:
1. Navigate to "Laporan" → "Laporan Bahan Baku"
2. System tampilkan filter form:
   - Date range (from-to)
   - Material (optional)
3. User set filter dan click "Generate"
4. System:
   - Query data
   - Calculate summary
   - Render report
5. System tampilkan report dengan:
   - Summary section
   - Detailed table
   - Charts (optional)
6. User option:
   - "Export PDF"
   - "Export Excel"
   - "Cetak"

**Production Report**:
1. Navigate to "Laporan" → "Laporan Produksi"
2. User set:
   - Date range
   - Production type (optional)
   - Pattern (optional)
3. Click "Generate"
4. System tampilkan:
   - Total orders, pieces, efficiency, waste
   - Breakdown by pattern
   - Efficiency trend
5. Export options available

**Sales Report**:
1. Navigate to "Laporan" → "Laporan Penjualan"
2. User set:
   - Date range
   - Customer (optional)
   - Sales channel (optional)
   - Product type (optional)
3. Click "Generate"
4. System tampilkan:
   - Revenue, profit, margin
   - Breakdown by product
   - Breakdown by channel
   - Breakdown by customer
   - Sales trend
5. Export options available

**Inventory Report**:
1. Navigate to "Laporan" → "Laporan Inventori"
2. User set:
   - As of date (default: today)
   - Product type (optional)
   - Location (optional)
3. Click "Generate"
4. System tampilkan:
   - Total items, pieces, value
   - Breakdown by product
   - Breakdown by location
   - Aging analysis
   - Turnover rate
5. Export options available

**Profit & Loss Report**:
1. Navigate to "Laporan" → "Laba Rugi"
2. User set date range
3. Click "Generate"
4. System:
   - Calculate revenue (sales)
   - Calculate COGS (materials + labor + overhead)
   - Calculate gross profit
   - Calculate operating expenses
   - Calculate net profit
5. System tampilkan P&L statement format
6. Export options available

**Postcondition**: Report generated and can be exported

---

## User Journey Examples

### Journey 1: Complete Production Cycle (Happy Path)

1. **Receive Material** (Warehouse Staff)
   - Login → Materials → New Receipt
   - Input: 100m katun from supplier X
   - System: Stock updated

2. **Create Cutting Order** (Production Staff)
   - Production → Cutting → New Order
   - Input: Use 50m, pattern mukena, target 20 pcs
   - System: Order created

3. **Execute Cutting** (Operator)
   - Start cutting → Complete
   - Input results: 19 good pieces, 1 waste
   - System: Result recorded, efficiency 95%

4. **Create Production Order** (Production Staff)
   - Production → Sewing → New Order
   - Type: External, contractor CV Jaya
   - Send 19 pieces
   - System: Order sent

5. **Receive from Contractor** (QC Staff)
   - After 1 week: Receive production
   - Input: 18 good, 1 defect
   - System: Create inventory batch

6. **Store to Location** (Warehouse Staff)
   - Inventory → Assign to Rak A-01
   - System: Location updated

7. **Create Sales Order** (Sales Staff)
   - Sales → New Order
   - Customer: Toko Berkah
   - Add 10 pieces @ 150k
   - Confirm order
   - System: Stock deducted, invoice generated

8. **Record Payment** (Sales Staff)
   - Order detail → Record payment
   - Input: Full payment via transfer
   - System: Order paid, receipt generated

9. **View Reports** (Manager)
   - Dashboard: See today's sales, month trends
   - Reports → Profit & Loss
   - Export to PDF

**Result**: Complete cycle dari bahan baku sampai penjualan tercatat dengan lengkap

---

### Journey 2: Handle Production Defect

1. Production order returns with defects
2. QC inputs: 15 good, 5 defect
3. System creates inventory for 15 good only
4. Defects tracked in production batch notes
5. Manager reviews defect rate
6. Decision: Send 5 pieces back to contractor for rework OR
7. Adjust contractor rating/price

---

### Journey 3: Low Stock Alert

1. System detects inventory < reorder point
2. System generates alert notification
3. Manager receives notification
4. Manager reviews:
   - Current stock level
   - Sales trend
   - Production pipeline
5. Decision: Create new material receipt OR adjust reorder point

---

## Mobile/Responsive Considerations

All flows harus dapat diakses via mobile browser:
- Form input optimized untuk mobile
- Camera integration untuk foto upload
- Touch-friendly buttons
- Simplified navigation
- Offline capability (future)

## Integration Points

Future enhancements untuk user flows:
- Barcode scanning untuk material receipt
- QR code untuk inventory tracking
- WhatsApp notification untuk order status
- Email invoice otomatis
- E-commerce integration untuk online orders
- Accounting software export

## Error Handling

Setiap flow harus handle:
- Validation errors dengan clear message
- Network errors dengan retry option
- Permission errors dengan helpful message
- Data conflicts dengan resolution options
- System errors dengan fallback actions

## Performance Expectations

- Page load: < 2 seconds
- Form submission: < 1 second
- Report generation (simple): < 3 seconds
- Report generation (complex): < 10 seconds
- Real-time stock update: < 500ms
