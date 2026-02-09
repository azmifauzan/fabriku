# QR Code Features for Inventory Management

## Fitur yang Ditambahkan

### 1. Generate & Print QR Code
- Setiap inventory item dapat mencetak QR code yang berisi SKU
- QR code dapat dicetak atau diunduh dalam format SVG
- Halaman khusus untuk print QR code dengan informasi item

**Lokasi:**
- Button "Print QR Code" di halaman detail inventory item ([Show.vue](resources/js/pages/Inventory/Items/Show.vue))
- Halaman print: `/inventory/items/{item}/qrcode/print`

### 2. Scan QR Code
- Scanner QR code menggunakan kamera device
- Setelah scan berhasil, langsung mengarah ke halaman detail item
- Real-time scanning dengan feedback visual

**Lokasi Button Scan:**
- Dashboard - di bagian header ([Dashboard.vue](resources/js/pages/Dashboard.vue))
- Inventory List - di bagian header action buttons ([Index.vue](resources/js/pages/Inventory/Items/Index.vue))

## File yang Dibuat/Dimodifikasi

### Backend
1. **Controller** - `app/Http/Controllers/InventoryItemController.php`
   - `printQrCode()` - Menampilkan halaman print QR code
   - `generateQrCode()` - Generate QR code SVG
   - `scanLookup()` - Lookup item berdasarkan SKU dari scan

2. **Routes** - `routes/web.php`
   - `GET /inventory/items/{item}/qrcode/print` - Halaman print
   - `GET /inventory/items/{item}/qrcode/generate` - Generate QR SVG
   - `POST /inventory/items/scan-lookup` - Lookup item by SKU

3. **Tests** - `tests/Feature/InventoryQrCodeTest.php`
   - Test generate QR code
   - Test print page
   - Test scan lookup
   - Test tenant isolation

### Frontend
1. **Components**
   - `resources/js/components/QrScanner.vue` - Reusable QR scanner component

2. **Pages**
   - `resources/js/pages/Inventory/Items/PrintQrCode.vue` - Print QR code page
   - `resources/js/pages/Inventory/Items/Show.vue` - Added Print QR button
   - `resources/js/pages/Inventory/Items/Index.vue` - Added Scan button
   - `resources/js/pages/Dashboard.vue` - Added Scan button

### Packages
1. **PHP**
   - `simplesoftwareio/simple-qrcode` - QR code generation

2. **JavaScript**
   - `html5-qrcode` - QR code scanning dengan kamera

## Cara Penggunaan

### Print QR Code
1. Buka detail inventory item
2. Click tombol "Print QR Code"
3. Pilih Print atau Download SVG

### Scan QR Code
1. Click tombol "Scan QR Code" di Dashboard atau Inventory List
2. Izinkan akses kamera jika diminta
3. Arahkan kamera ke QR code
4. Sistem akan otomatis redirect ke detail item

## Testing

Run tests dengan command:
```bash
php artisan test --filter=InventoryQrCodeTest
```

Tests mencakup:
- ✅ Generate QR code
- ✅ Display print page
- ✅ Scan lookup functionality
- ✅ Tenant isolation
- ✅ Validation

## Technical Notes

### QR Code Content
QR code berisi SKU dari inventory item. Format: `SKU-ITEM-001`

### Security
- Scan lookup menggunakan tenant scope untuk security
- Hanya item dari tenant yang sama yang bisa diakses
- CSRF token validation untuk scan lookup endpoint

### Browser Compatibility
QR Scanner component require:
- Camera access (HTTPS atau localhost)
- Modern browser dengan getUserMedia support
- Chrome, Firefox, Safari (iOS 11+), Edge

### Performance
- QR code generation on-demand (tidak disimpan)
- SVG format untuk ukuran file kecil
- Real-time scanning tanpa delay

## Future Enhancements
- Batch print multiple QR codes
- Customize QR code size dan design
- Export QR codes ke PDF
- QR code dengan logo/branding
- History scan records
