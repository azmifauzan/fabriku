# Database Schema - Fabriku

## Overview
Database schema untuk Fabriku menggunakan PostgreSQL dengan multi-tenancy architecture. Setiap tenant memiliki data yang terisolasi dengan tenant_id. 

**Design Philosophy**: Schema dirancang **category-agnostic** (tidak spesifik garment saja) untuk mendukung berbagai jenis bisnis UMKM. Terminologi menggunakan istilah generik yang bisa diaplikasikan untuk garment, makanan/kue, craft, dll.

**Supported Categories** (MVP):
1. **Garment** - Pattern, Cutting (Preparation), Sewing (Production)
2. **Food/Kue** - Recipe, Mixing/Prep (Preparation), Baking/Cooking (Production)
3. **Craft** - Template, Assembly Prep (Preparation), Assembly (Production)
4. **Cosmetic** - Formula, Formulation (Preparation), Production

**Simplified Approach**: No Bill of Materials (BOM), no presisi tracking - cocok untuk UMKM yang masih manual.

## Entity Relationship Diagram (ERD)

```
┌──────────────┐
│   tenants    │
└──────┬───────┘
       │
       │ (1:N)
       │
┌──────┴───────────────────────────────────────────┐
│                                                   │
│  ┌─────────┐    ┌──────────────┐    ┌─────────┐ │
│  │  users  │    │   patterns   │    │customers│ │
│  └─────────┘    └──────────────┘    └─────────┘ │
│                                                   │
│  ┌──────────────┐    ┌─────────────────┐        │
│  │  materials   │    │  contractors    │        │
│  └──────┬───────┘    └─────────────────┘        │
│         │                                         │
│         │ (1:N)                                   │
│  ┌──────┴──────────────┐                        │
│  │ material_receipts   │                        │
│  └─────────────────────┘                        │
│                                                   │
│  ┌─────────────────────┐                        │
│  │ preparation_orders  │  ← Generic: Cutting/   │
│  │ (materials_used)    │    Mixing/Assembly     │
│  └──────┬──────────────┘    Auto deduct stock   │
│         │                                         │
│         │ (N:1)                                   │
│  ┌──────┴──────────────┐                        │
│  │ production_orders   │                        │
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (1:N)                                   │
│  ┌──────┴──────────────┐                        │
│  │ production_batches  │                        │
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (1:N)                                   │
│  ┌──────┴──────────────┐                        │
│  │  inventory_items    │                        │
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (N:1)                                   │
│  ┌──────┴──────────────┐                        │
│  │   sales_orders      │                        │
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (1:N)                                   │
│  ┌──────┴──────────────┐                        │
│  │   sales_items       │                        │
│  └─────────────────────┘                        │
│                                                   │
└───────────────────────────────────────────────────┘
```

## Table Definitions

### 1. tenants
Informasi organisasi/tenant dalam sistem SaaS.

```sql
CREATE TABLE tenants (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    logo_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    subscription_plan VARCHAR(50) DEFAULT 'trial',
    subscription_expires_at TIMESTAMP,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tenants_slug ON tenants(slug);
CREATE INDEX idx_tenants_is_active ON tenants(is_active);
```

### 2. users
User accounts dengan multi-role support.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'staff',
    phone VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_tenant_email_unique UNIQUE (tenant_id, email)
);

CREATE INDEX idx_users_tenant_id ON users(tenant_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
```

**Roles**: 
- `admin` - Tenant administrator
- `manager` - Manager (full access)
- `production_staff` - Staff produksi
- `warehouse_staff` - Staff gudang
- `sales_staff` - Staff penjualan
- `viewer` - Read-only access

### 3. materials
Master data bahan baku/bahan mentah (universal untuk semua kategori).

**Category Examples**:
- **Garment**: kain, benang, resleting, kancing, dll
- **Kue**: tepung, gula, telur, mentega, coklat, dll

```sql
CREATE TABLE materials (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100),  -- 'kain', 'benang', 'aksesoris', 'tepung', 'gula', 'telur', dll
    unit VARCHAR(20) DEFAULT 'pcs',  -- 'meter', 'roll', 'kg', 'gram', 'pcs', 'butir', dll
    description TEXT,
    standard_price DECIMAL(15,2),
    current_stock DECIMAL(15,2) DEFAULT 0,
    reorder_point DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT materials_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_materials_tenant_id ON materials(tenant_id);
CREATE INDEX idx_materials_code ON materials(code);
```

**Note**: Atribut dinamis (warna, expired date, dll) disimpan di tabel `material_attributes` terpisah untuk fleksibilitas.

### 3.1 material_attributes
Atribut dinamis untuk materials (unlimited attributes per material).

```sql
CREATE TABLE material_attributes (
    id BIGSERIAL PRIMARY KEY,
    material_id BIGINT NOT NULL REFERENCES materials(id) ON DELETE CASCADE,
    attribute_name VARCHAR(100) NOT NULL,  -- 'warna', 'lebar_kain', 'expired_date', 'storage_temp', dll
    attribute_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT material_attributes_unique UNIQUE (material_id, attribute_name)
);

CREATE INDEX idx_material_attributes_material_id ON material_attributes(material_id);
```

**Attribute Examples**:
- **Garment**: warna='Merah', lebar_kain='150cm', gramasi='200gsm'
- **Kue**: expired_date='2026-02-01', storage_temp='chilled', batch='A123'

### 4. material_receipts
Penerimaan bahan baku/mentah dari supplier (universal).

```sql
CREATE TABLE material_receipts (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    receipt_number VARCHAR(50) NOT NULL,
    material_id BIGINT NOT NULL REFERENCES materials(id) ON DELETE RESTRICT,
    supplier_name VARCHAR(255) NOT NULL,
    receipt_date DATE NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    rolls_count INTEGER,  -- Opsional, untuk kain dalam roll
    length_per_roll DECIMAL(10,2),  -- Opsional
    batch_number VARCHAR(100),
    notes TEXT,
    received_by BIGINT REFERENCES users(id),
    attachments JSONB DEFAULT '[]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT material_receipts_tenant_number_unique UNIQUE (tenant_id, receipt_number)
);

CREATE INDEX idx_material_receipts_tenant_id ON material_receipts(tenant_id);
CREATE INDEX idx_material_receipts_material_id ON material_receipts(material_id);
CREATE INDEX idx_material_receipts_receipt_date ON material_receipts(receipt_date);
CREATE INDEX idx_material_receipts_batch_number ON material_receipts(batch_number);
```

### 5. patterns
Template produk (universal - garment patterns, food recipes, craft templates, dll).

**Category Values**: 'garment', 'food', 'craft', 'cosmetic', 'other'

**Product Type Examples**:
- **Garment**: 'mukena', 'daster', 'gamis', 'jilbab', 'kemeja', 'celana', dll
- **Food**: 'cake', 'brownies', 'cookies', 'roti', 'kue_kering', 'martabak', dll
- **Craft**: 'gelang', 'tas', 'gantungan_kunci', 'bros', 'rajutan', dll
- **Cosmetic**: 'sabun', 'lotion', 'lip_balm', 'scrub', 'body_butter', dll

```sql
CREATE TABLE patterns (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,  -- MKN-001, CAKE-001, BRG-001, SBN-001, dll
    name VARCHAR(255) NOT NULL,  -- 'Mukena Dewasa', 'Brownies Coklat', 'Gelang Rajut'
    category VARCHAR(50) NOT NULL DEFAULT 'other',  -- garment/food/craft/cosmetic/other
    product_type VARCHAR(100) NOT NULL,  -- disesuaikan kategori
    size VARCHAR(50),  -- 'all_size', 'L', 'XL' untuk garment; '24cm', '1kg' untuk food
    description TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT patterns_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_patterns_tenant_id ON patterns(tenant_id);
CREATE INDEX idx_patterns_category ON patterns(category);
CREATE INDEX idx_patterns_product_type ON patterns(product_type);
```

**Notes**:
- **NO Bill of Materials (BOM)** - UMKM tidak perlu presisi pabrik
- Pattern hanya template produk, bukan resep detail
- Materials yang dipakai dicatat di `preparation_orders.materials_used` (flexible)

### 6. preparation_orders
Order persiapan bahan sebelum produksi (universal untuk semua kategori).

**Terminology per Category**:
- **Garment**: Cutting (pemotongan kain)
- **Food**: Mixing/Prep (persiapan adonan/bahan)
- **Craft**: Assembly Prep (persiapan komponen)
- **Cosmetic**: Formulation (mixing formula)

**Key Features**:
- Pattern optional (bisa prep tanpa pattern)
- Materials used stored as JSON (flexible, tidak terikat BOM)
- Auto deduct material stock saat status = 'completed'
- Output unit flexible (pieces/kg/batch/liter/dll)

```sql
CREATE TABLE preparation_orders (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) NOT NULL,  -- PRP-2026-001
    pattern_id BIGINT REFERENCES patterns(id) ON DELETE RESTRICT,  -- NULLABLE
    order_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'draft',  -- draft/in_progress/completed/cancelled
    prepared_by BIGINT REFERENCES users(id),
    output_quantity DECIMAL(10,2) NOT NULL,  -- berapa jadi (5 pieces, 2.5 kg, 3 batch, dll)
    output_unit VARCHAR(20) DEFAULT 'pieces',  -- pieces/kg/batch/liter/gram/pcs/dll
    materials_used JSONB NOT NULL,  -- [{material_id, material_name, quantity, unit}]
    notes TEXT,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT preparation_orders_tenant_number_unique UNIQUE (tenant_id, order_number)
);

CREATE INDEX idx_preparation_orders_tenant_id ON preparation_orders(tenant_id);
CREATE INDEX idx_preparation_orders_status ON preparation_orders(status);
CREATE INDEX idx_preparation_orders_order_date ON preparation_orders(order_date);
CREATE INDEX idx_preparation_orders_pattern_id ON preparation_orders(pattern_id);
```

**Status values**: `draft`, `in_progress`, `completed`, `cancelled`

**materials_used JSON structure**:
```json
[
  {
    "material_id": 1,
    "material_name": "Kain Katun Putih",
    "quantity": 5.0,
    "unit": "meter"
  },
  {
    "material_id": 2,
    "material_name": "Benang Jahit Hitam",
    "quantity": 2.0,
    "unit": "roll"
  }
]
```

**Stock Deduction Logic**:
- When status changes to 'completed', loop through materials_used
- Deduct each material.quantity from materials.current_stock
- Log transaction untuk audit trail (optional)

### 7. contractors
Pihak ketiga untuk outsourcing produksi (universal).

**Type Examples**:
- **Garment**: penjahit, bordir, sablon
- **Food**: dapur sharing, packaging, catering
- **Craft**: pengrajin, assembler
- **Cosmetic**: lab produksi, packaging

```sql
CREATE TABLE contractors (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50),  -- 'penjahit', 'dapur', 'pengrajin', 'lab', dll
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    price_per_unit DECIMAL(15,2),  -- per piece/kg/batch/dll
    rating DECIMAL(3,2),
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT contractors_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_contractors_tenant_id ON contractors(tenant_id);
CREATE INDEX idx_contractors_type ON contractors(type);
CREATE INDEX idx_contractors_is_active ON contractors(is_active);
```

### 8. production_orders
Order produksi (internal & eksternal) - universal untuk semua kategori.

**Production Type Examples**:
- **Garment**: Sewing (jahit), Embroidery (bordir), Printing (sablon)
- **Food**: Baking (panggang), Cooking (masak), Frying (goreng)
- **Craft**: Assembly (rakit), Weaving (rajut), Carving (ukir)
- **Cosmetic**: Mixing (campur), Filling (isi), Packaging (kemas)

```sql
CREATE TABLE production_orders (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) NOT NULL,  -- PO-2026-001
    preparation_order_id BIGINT REFERENCES preparation_orders(id) ON DELETE RESTRICT,  -- NULLABLE
    production_type VARCHAR(50) NOT NULL,  -- 'internal' or 'external'
    contractor_id BIGINT REFERENCES contractors(id) ON DELETE RESTRICT,  -- for external
    order_date DATE NOT NULL,
    quantity_target DECIMAL(10,2) NOT NULL,
    quantity_unit VARCHAR(20) DEFAULT 'pieces',
    quantity_completed DECIMAL(10,2) DEFAULT 0,
    cost_per_unit DECIMAL(15,2),
    total_cost DECIMAL(15,2),
    status VARCHAR(50) DEFAULT 'draft',
    sent_at TIMESTAMP,
    expected_return_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT production_orders_tenant_number_unique UNIQUE (tenant_id, order_number)
);

CREATE INDEX idx_production_orders_tenant_id ON production_orders(tenant_id);
CREATE INDEX idx_production_orders_status ON production_orders(status);
CREATE INDEX idx_production_orders_production_type ON production_orders(production_type);
```

**production_type values**: `internal`, `external`
**Status values**: `draft`, `pending`, `in_progress`, `completed`, `returned`, `cancelled`

### 9. production_batches
Batch hasil produksi jahit yang dikembalikan.

```sql
CREATE TABLE production_batches (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    batch_number VARCHAR(50) NOT NULL,
    production_order_id BIGINT NOT NULL REFERENCES production_orders(id) ON DELETE CASCADE,
    return_date DATE NOT NULL,
    pieces_received INTEGER NOT NULL,
    pieces_good INTEGER NOT NULL,
    pieces_defect INTEGER DEFAULT 0,
    quality_grade VARCHAR(20),
    qc_notes TEXT,
    qc_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT production_batches_tenant_number_unique UNIQUE (tenant_id, batch_number)
);

CREATE INDEX idx_production_batches_tenant_id ON production_batches(tenant_id);
CREATE INDEX idx_production_batches_production_order_id ON production_batches(production_order_id);
CREATE INDEX idx_production_batches_return_date ON production_batches(return_date);
```

### 11. inventory_locations
Lokasi penyimpanan di gudang (rak).

```sql
CREATE TABLE inventory_locations (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'rack',
    capacity INTEGER,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT inventory_locations_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_inventory_locations_tenant_id ON inventory_locations(tenant_id);
```

### 12. inventory_items
Produk jadi dalam gudang.

```sql
CREATE TABLE inventory_items (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    sku VARCHAR(100) NOT NULL,
    production_batch_id BIGINT NOT NULL REFERENCES production_batches(id) ON DELETE RESTRICT,
    pattern_id BIGINT NOT NULL REFERENCES patterns(id) ON DELETE RESTRICT,
    location_id BIGINT REFERENCES inventory_locations(id) ON DELETE SET NULL,
    initial_quantity INTEGER NOT NULL,
    current_quantity INTEGER NOT NULL,
    reserved_quantity INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'available',
    cost_per_piece DECIMAL(15,2),
    selling_price DECIMAL(15,2),
    stored_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT inventory_items_tenant_sku_unique UNIQUE (tenant_id, sku)
);

CREATE INDEX idx_inventory_items_tenant_id ON inventory_items(tenant_id);
CREATE INDEX idx_inventory_items_production_batch_id ON inventory_items(production_batch_id);
CREATE INDEX idx_inventory_items_pattern_id ON inventory_items(pattern_id);
CREATE INDEX idx_inventory_items_status ON inventory_items(status);
```

**Status values**: `available`, `reserved`, `depleted`

### 13. customers
Data pelanggan.

```sql
CREATE TABLE customers (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'retail',
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    tax_id VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT customers_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_customers_tenant_id ON customers(tenant_id);
CREATE INDEX idx_customers_type ON customers(type);
```

**Type values**: `retail`, `wholesale`, `reseller`, `online`

### 14. sales_orders
Order penjualan.

```sql
CREATE TABLE sales_orders (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) NOT NULL,
    customer_id BIGINT REFERENCES customers(id) ON DELETE RESTRICT,
    order_date DATE NOT NULL,
    sales_channel VARCHAR(50) DEFAULT 'offline',
    subtotal DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    shipping_cost DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'unpaid',
    paid_amount DECIMAL(15,2) DEFAULT 0,
    payment_method VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    sold_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sales_orders_tenant_number_unique UNIQUE (tenant_id, order_number)
);

CREATE INDEX idx_sales_orders_tenant_id ON sales_orders(tenant_id);
CREATE INDEX idx_sales_orders_customer_id ON sales_orders(customer_id);
CREATE INDEX idx_sales_orders_order_date ON sales_orders(order_date);
CREATE INDEX idx_sales_orders_status ON sales_orders(status);
CREATE INDEX idx_sales_orders_payment_status ON sales_orders(payment_status);
```

**sales_channel values**: `offline`, `online`, `marketplace`, `reseller`
**payment_status values**: `unpaid`, `partial`, `paid`
**Status values**: `pending`, `confirmed`, `packed`, `shipped`, `delivered`, `cancelled`

### 15. sales_items
Item dalam order penjualan.

```sql
CREATE TABLE sales_items (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    sales_order_id BIGINT NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    inventory_item_id BIGINT NOT NULL REFERENCES inventory_items(id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sales_items_tenant_id ON sales_items(tenant_id);
CREATE INDEX idx_sales_items_sales_order_id ON sales_items(sales_order_id);
CREATE INDEX idx_sales_items_inventory_item_id ON sales_items(inventory_item_id);
```

### 16. audit_logs
Audit trail untuk tracking perubahan penting.

```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    auditable_type VARCHAR(255) NOT NULL,
    auditable_id BIGINT NOT NULL,
    event VARCHAR(50) NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_logs_tenant_id ON audit_logs(tenant_id);
CREATE INDEX idx_audit_logs_auditable ON audit_logs(auditable_type, auditable_id);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
```

## Calculated Fields & Views

### Inventory Summary View
```sql
CREATE VIEW inventory_summary AS
SELECT 
    i.tenant_id,
    p.product_type,
    p.name AS product_name,
    SUM(i.current_quantity) AS total_stock,
    SUM(i.reserved_quantity) AS total_reserved,
    SUM(i.current_quantity - i.reserved_quantity) AS available_stock,
    COUNT(i.id) AS batch_count
FROM inventory_items i
JOIN patterns p ON i.pattern_id = p.id
WHERE i.status = 'available'
GROUP BY i.tenant_id, p.product_type, p.name;
```

### Production Efficiency View
```sql
CREATE VIEW production_efficiency AS
SELECT 
    co.tenant_id,
    p.name AS pattern_name,
    COUNT(co.id) AS total_orders,
    SUM(co.material_used) AS total_material,
    SUM(cr.actual_pieces) AS total_pieces,
    AVG(cr.efficiency_percentage) AS avg_efficiency,
    SUM(cr.waste_material) AS total_waste
FROM cutting_orders co
JOIN cutting_results cr ON co.id = cr.cutting_order_id
JOIN patterns p ON co.pattern_id = p.id
WHERE co.status = 'completed'
GROUP BY co.tenant_id, p.name;
```

## Data Integrity Rules

1. **Tenant Isolation**: Semua query harus di-scope by tenant_id
2. **Soft Deletes**: Tidak digunakan, gunakan is_active flag untuk master data
3. **Cascading**: DELETE CASCADE untuk detail tables, RESTRICT untuk references
4. **Stock Validation**: Trigger untuk validasi stok sebelum sales
5. **Audit Trail**: Automatic logging untuk critical operations

## Migration Strategy

1. Create tenant table first
2. Create users table (depends on tenants)
3. Create master data tables (materials, patterns, contractors, customers, locations)
4. Create transaction tables in order of dependency
5. Create views and indexes
6. Seed initial data (roles, default settings)

## Indexes Strategy

- Primary keys (automatic)
- Foreign keys (untuk join performance)
- tenant_id (untuk tenant isolation)
- Status fields (untuk filtering)
- Date fields (untuk reporting)
- Unique constraints (untuk business rules)

## Backup & Recovery

- Daily full backup
- Point-in-time recovery enabled
- Backup retention: 30 days
- Test restore monthly
