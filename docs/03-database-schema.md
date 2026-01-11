# Database Schema - Fabriku

## Overview
Database schema untuk Fabriku menggunakan PostgreSQL dengan multi-tenancy architecture. Setiap tenant memiliki data yang terisolasi dengan tenant_id. 

**Design Philosophy**: Schema dirancang **category-agnostic** (tidak spesifik garment saja) untuk mendukung berbagai jenis bisnis UMKM. Terminologi menggunakan istilah generik yang bisa diaplikasikan untuk garment, makanan/kue, craft, dll.

**Supported Categories** (MVP):
1. **Garment** - Pattern, Cutting, Sewing
2. **Food/Kue** - Recipe, Preparation, Baking/Cooking

**Future Categories**: Craft, Cosmetics, dll

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
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (N:1)                                   │
│  ┌──────┴──────────────┐                        │
│  │  cutting_orders     │                        │
│  └──────┬──────────────┘                        │
│         │                                         │
│         │ (1:N)                                   │
│  ┌──────┴──────────────┐                        │
│  │  cutting_results    │                        │
│  └──────┬──────────────┘                        │
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
Pola/resep produk (universal - garment patterns atau food recipes).

**Product Type Examples**:
- **Garment**: 'mukena', 'daster', 'gamis', 'jilbab', 'lainnya'
- **Kue**: 'cake', 'brownies', 'cookies', 'roti', 'kue_kering', 'lainnya'

```sql
CREATE TABLE patterns (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,  -- MKN-001, CAKE-001, dll
    name VARCHAR(255) NOT NULL,  -- 'Mukena Dewasa', 'Brownies Coklat'
    product_type VARCHAR(100) NOT NULL,  -- enum disesuaikan kategori
    size VARCHAR(50),  -- 'all_size', 'L', 'XL' untuk garment; '24cm', '1 loyang' untuk kue
    description TEXT,
    estimated_time INTEGER,  -- waktu produksi dalam menit
    standard_waste_percentage DECIMAL(5,2),  -- persentase waste standar
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT patterns_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_patterns_tenant_id ON patterns(tenant_id);
CREATE INDEX idx_patterns_product_type ON patterns(product_type);
```

### 5.1 pattern_materials
Bill of Materials (BOM) - kebutuhan bahan untuk setiap pattern/recipe.

```sql
CREATE TABLE pattern_materials (
    id BIGSERIAL PRIMARY KEY,
    pattern_id BIGINT NOT NULL REFERENCES patterns(id) ON DELETE CASCADE,
    material_id BIGINT NOT NULL REFERENCES materials(id) ON DELETE RESTRICT,
    quantity_needed DECIMAL(10,2) NOT NULL,  -- 2.5 meter, 0.5 kg, dll
    notes TEXT,  -- 'untuk atasan dan bawahan', 'bisa diganti margarin', dll
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pattern_materials_unique UNIQUE (pattern_id, material_id)
);

CREATE INDEX idx_pattern_materials_pattern_id ON pattern_materials(pattern_id);
CREATE INDEX idx_pattern_materials_material_id ON pattern_materials(material_id);
```

### 6. cutting_orders
Order pemotongan kain.

```sql
CREATE TABLE cutting_orders (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) NOT NULL,
    material_receipt_id BIGINT NOT NULL REFERENCES material_receipts(id) ON DELETE RESTRICT,
    pattern_id BIGINT NOT NULL REFERENCES patterns(id) ON DELETE RESTRICT,
    order_date DATE NOT NULL,
    material_used DECIMAL(15,2) NOT NULL,
    target_pieces INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    assigned_to BIGINT REFERENCES users(id),
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT cutting_orders_tenant_number_unique UNIQUE (tenant_id, order_number)
);

CREATE INDEX idx_cutting_orders_tenant_id ON cutting_orders(tenant_id);
CREATE INDEX idx_cutting_orders_status ON cutting_orders(status);
CREATE INDEX idx_cutting_orders_order_date ON cutting_orders(order_date);
```

**Status values**: `pending`, `in_progress`, `completed`, `cancelled`

### 7. cutting_results
Hasil pemotongan kain.

```sql
CREATE TABLE cutting_results (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    cutting_order_id BIGINT NOT NULL REFERENCES cutting_orders(id) ON DELETE CASCADE,
    actual_pieces INTEGER NOT NULL,
    good_pieces INTEGER NOT NULL,
    defect_pieces INTEGER DEFAULT 0,
    waste_material DECIMAL(10,2) DEFAULT 0,
    efficiency_percentage DECIMAL(5,2),
    quality_grade VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_cutting_results_tenant_id ON cutting_results(tenant_id);
CREATE INDEX idx_cutting_results_cutting_order_id ON cutting_results(cutting_order_id);
```

### 8. contractors
Pihak ketiga untuk jahit outsourcing.

```sql
CREATE TABLE contractors (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    price_per_piece DECIMAL(15,2),
    rating DECIMAL(3,2),
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT contractors_tenant_code_unique UNIQUE (tenant_id, code)
);

CREATE INDEX idx_contractors_tenant_id ON contractors(tenant_id);
CREATE INDEX idx_contractors_is_active ON contractors(is_active);
```

### 9. production_orders
Order produksi jahit (internal & eksternal).

```sql
CREATE TABLE production_orders (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    order_number VARCHAR(50) NOT NULL,
    cutting_result_id BIGINT NOT NULL REFERENCES cutting_results(id) ON DELETE RESTRICT,
    production_type VARCHAR(20) NOT NULL,
    contractor_id BIGINT REFERENCES contractors(id) ON DELETE RESTRICT,
    order_date DATE NOT NULL,
    pieces_quantity INTEGER NOT NULL,
    cost_per_piece DECIMAL(15,2),
    total_cost DECIMAL(15,2),
    status VARCHAR(50) DEFAULT 'pending',
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
**Status values**: `pending`, `in_progress`, `completed`, `returned`, `cancelled`

### 10. production_batches
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
