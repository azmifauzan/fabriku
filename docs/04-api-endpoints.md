# API Endpoints - Fabriku

## API Overview

Fabriku menggunakan RESTful API design dengan Inertia.js untuk server-side rendering. API endpoints mengikuti konvensi Laravel resource controllers.

## Base URL
```
Development: http://localhost:8000
Production: https://app.fabriku.com
```

## Authentication
- Session-based authentication (Inertia.js)
- API token authentication (Laravel Sanctum) untuk mobile/external clients
- CSRF protection untuk web requests

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

## API Endpoints

### Authentication & Users

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response: 200 OK
{
  "user": { ... },
  "redirect": "/dashboard"
}
```

#### Logout
```http
POST /logout

Response: 302 Redirect to /login
```

#### Get Current User
```http
GET /api/user

Response: 200 OK
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "admin",
  "tenant_id": 1
}
```

---

### Materials Management

#### List Materials
```http
GET /materials
Query Parameters:
  - search: string (optional)
  - category: string (optional)
  - is_active: boolean (optional)
  - page: integer (default: 1)
  - per_page: integer (default: 15)

Response: 200 OK (Inertia)
{
  "materials": {
    "data": [...],
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### Create Material
```http
POST /materials
Content-Type: application/json

{
  "code": "KTN001",
  "name": "Katun Premium",
  "category": "Katun",
  "unit": "meter",
  "description": "Katun premium untuk mukena",
  "standard_price": 50000,
  "reorder_point": 100
}

Response: 302 Redirect
```

#### Show Material
```http
GET /materials/{id}

Response: 200 OK (Inertia)
{
  "material": {
    "id": 1,
    "code": "KTN001",
    "name": "Katun Premium",
    ...
  },
  "receipts": [...],
  "stock_summary": { ... }
}
```

#### Update Material
```http
PUT /materials/{id}
Content-Type: application/json

{
  "name": "Katun Premium Grade A",
  "standard_price": 55000
}

Response: 302 Redirect
```

#### Delete Material
```http
DELETE /materials/{id}

Response: 302 Redirect
```

---

### Material Receipts

#### List Material Receipts
```http
GET /material-receipts
Query Parameters:
  - material_id: integer (optional)
  - supplier_name: string (optional)
  - date_from: date (optional)
  - date_to: date (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Material Receipt
```http
POST /material-receipts
Content-Type: multipart/form-data

{
  "receipt_number": "MR-2026-001",
  "material_id": 1,
  "supplier_name": "PT Textile Indo",
  "receipt_date": "2026-01-10",
  "quantity": 500,
  "unit_price": 50000,
  "rolls_count": 10,
  "length_per_roll": 50,
  "batch_number": "BATCH-001",
  "notes": "Quality grade A",
  "attachments[]": [file1, file2]
}

Response: 302 Redirect
```

#### Show Material Receipt
```http
GET /material-receipts/{id}

Response: 200 OK (Inertia)
```

---

### Patterns

#### List Patterns
```http
GET /patterns
Query Parameters:
  - product_type: string (optional)
  - is_active: boolean (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Pattern
```http
POST /patterns
Content-Type: multipart/form-data

{
  "code": "MUK-001",
  "name": "Mukena Dewasa",
  "product_type": "Mukena",
  "description": "Mukena ukuran dewasa standar",
  "material_requirement": 2.5,
  "estimated_pieces_per_meter": 0.4,
  "image": file
}

Response: 302 Redirect
```

---

### Cutting Orders

#### List Cutting Orders
```http
GET /cutting-orders
Query Parameters:
  - status: string (optional)
  - date_from: date (optional)
  - date_to: date (optional)
  - assigned_to: integer (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Cutting Order
```http
POST /cutting-orders
Content-Type: application/json

{
  "order_number": "CO-2026-001",
  "material_receipt_id": 1,
  "pattern_id": 1,
  "order_date": "2026-01-10",
  "material_used": 100,
  "target_pieces": 40,
  "assigned_to": 5,
  "notes": "Potong dengan hati-hati"
}

Response: 302 Redirect
```

#### Show Cutting Order
```http
GET /cutting-orders/{id}

Response: 200 OK (Inertia)
{
  "order": { ... },
  "result": { ... }
}
```

#### Start Cutting Order
```http
POST /cutting-orders/{id}/start

Response: 302 Redirect
```

#### Complete Cutting Order
```http
POST /cutting-orders/{id}/complete
Content-Type: application/json

{
  "actual_pieces": 38,
  "good_pieces": 37,
  "defect_pieces": 1,
  "waste_material": 5,
  "quality_grade": "A",
  "notes": "1 piece cacat jahitan"
}

Response: 302 Redirect
```

---

### Production Orders

#### List Production Orders
```http
GET /production-orders
Query Parameters:
  - production_type: string (internal|external)
  - status: string (optional)
  - contractor_id: integer (optional)
  - date_from: date (optional)
  - date_to: date (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Production Order
```http
POST /production-orders
Content-Type: application/json

{
  "order_number": "PO-2026-001",
  "cutting_result_id": 1,
  "production_type": "external",
  "contractor_id": 1,
  "order_date": "2026-01-10",
  "pieces_quantity": 37,
  "cost_per_piece": 15000,
  "expected_return_date": "2026-01-17",
  "notes": "Prioritas tinggi"
}

Response: 302 Redirect
```

#### Send to Contractor
```http
POST /production-orders/{id}/send

Response: 302 Redirect
```

#### Receive Production Batch
```http
POST /production-orders/{id}/receive
Content-Type: application/json

{
  "batch_number": "BATCH-001",
  "return_date": "2026-01-17",
  "pieces_received": 37,
  "pieces_good": 35,
  "pieces_defect": 2,
  "quality_grade": "A",
  "qc_notes": "2 pieces jahitan kendur"
}

Response: 302 Redirect
```

---

### Contractors

#### List Contractors
```http
GET /contractors
Query Parameters:
  - is_active: boolean (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Contractor
```http
POST /contractors
Content-Type: application/json

{
  "code": "CNT-001",
  "name": "CV Jahit Jaya",
  "contact_person": "Ibu Siti",
  "phone": "081234567890",
  "address": "Jl. Raya No. 123",
  "price_per_piece": 15000,
  "notes": "Kualitas bagus, tepat waktu"
}

Response: 302 Redirect
```

---

### Inventory

#### List Inventory Items
```http
GET /inventory
Query Parameters:
  - pattern_id: integer (optional)
  - location_id: integer (optional)
  - status: string (optional)
  - search: string (optional)
  - page: integer

Response: 200 OK (Inertia)
{
  "items": { ... },
  "summary": {
    "total_items": 150,
    "total_value": 15000000,
    "low_stock_items": 5
  }
}
```

#### Show Inventory Item
```http
GET /inventory/{id}

Response: 200 OK (Inertia)
{
  "item": { ... },
  "transaction_history": [...],
  "related_sales": [...]
}
```

#### Update Inventory Location
```http
PATCH /inventory/{id}/location
Content-Type: application/json

{
  "location_id": 5
}

Response: 302 Redirect
```

#### Adjust Inventory
```http
POST /inventory/{id}/adjust
Content-Type: application/json

{
  "adjustment_type": "increase|decrease",
  "quantity": 5,
  "reason": "Stock opname correction",
  "notes": "Ditemukan 5 pieces di rak lama"
}

Response: 302 Redirect
```

---

### Inventory Locations

#### List Locations
```http
GET /inventory-locations

Response: 200 OK (Inertia)
```

#### Create Location
```http
POST /inventory-locations
Content-Type: application/json

{
  "code": "RAK-A01",
  "name": "Rak A Baris 1",
  "type": "rack",
  "capacity": 100
}

Response: 302 Redirect
```

---

### Customers

#### List Customers
```http
GET /customers
Query Parameters:
  - type: string (optional)
  - search: string (optional)
  - is_active: boolean (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Customer
```http
POST /customers
Content-Type: application/json

{
  "code": "CUST-001",
  "name": "Toko Berkah",
  "type": "wholesale",
  "email": "tokoberkah@example.com",
  "phone": "081234567890",
  "address": "Jl. Pasar No. 45",
  "city": "Jakarta",
  "province": "DKI Jakarta",
  "postal_code": "12345"
}

Response: 302 Redirect
```

---

### Sales Orders

#### List Sales Orders
```http
GET /sales
Query Parameters:
  - status: string (optional)
  - payment_status: string (optional)
  - customer_id: integer (optional)
  - sales_channel: string (optional)
  - date_from: date (optional)
  - date_to: date (optional)
  - page: integer

Response: 200 OK (Inertia)
```

#### Create Sales Order
```http
POST /sales
Content-Type: application/json

{
  "order_number": "SO-2026-001",
  "customer_id": 1,
  "order_date": "2026-01-10",
  "sales_channel": "offline",
  "items": [
    {
      "inventory_item_id": 1,
      "quantity": 10,
      "unit_price": 150000,
      "discount": 0
    },
    {
      "inventory_item_id": 2,
      "quantity": 5,
      "unit_price": 200000,
      "discount": 10000
    }
  ],
  "discount": 50000,
  "tax": 0,
  "shipping_cost": 25000,
  "payment_method": "transfer",
  "notes": "Kirim besok pagi"
}

Response: 302 Redirect
```

#### Show Sales Order
```http
GET /sales/{id}

Response: 200 OK (Inertia)
{
  "order": { ... },
  "items": [...],
  "customer": { ... },
  "payment_history": [...]
}
```

#### Update Payment
```http
POST /sales/{id}/payment
Content-Type: application/json

{
  "amount": 500000,
  "payment_method": "transfer",
  "payment_date": "2026-01-10",
  "notes": "Transfer BCA"
}

Response: 302 Redirect
```

#### Cancel Sales Order
```http
POST /sales/{id}/cancel
Content-Type: application/json

{
  "reason": "Customer membatalkan order"
}

Response: 302 Redirect
```

#### Print Invoice
```http
GET /sales/{id}/invoice

Response: 200 OK (PDF)
```

---

### Reports

#### Dashboard Analytics
```http
GET /dashboard

Response: 200 OK (Inertia)
{
  "metrics": {
    "total_sales_today": 5000000,
    "total_sales_month": 150000000,
    "inventory_value": 50000000,
    "low_stock_items": 5,
    "pending_orders": 10
  },
  "charts": {
    "sales_trend": [...],
    "top_products": [...],
    "production_efficiency": [...]
  }
}
```

#### Material Report
```http
GET /reports/materials
Query Parameters:
  - date_from: date (required)
  - date_to: date (required)
  - material_id: integer (optional)
  - format: string (html|pdf|excel)

Response: 200 OK (Inertia/PDF/Excel)
{
  "summary": {
    "total_received": 1000,
    "total_used": 800,
    "remaining_stock": 200
  },
  "details": [...]
}
```

#### Production Report
```http
GET /reports/production
Query Parameters:
  - date_from: date (required)
  - date_to: date (required)
  - production_type: string (optional)
  - format: string (html|pdf|excel)

Response: 200 OK
{
  "summary": {
    "total_orders": 50,
    "total_pieces": 2000,
    "avg_efficiency": 95.5,
    "total_waste": 50
  },
  "details": [...]
}
```

#### Inventory Report
```http
GET /reports/inventory
Query Parameters:
  - as_of_date: date (optional, default: today)
  - product_type: string (optional)
  - format: string (html|pdf|excel)

Response: 200 OK
{
  "summary": {
    "total_items": 150,
    "total_pieces": 5000,
    "total_value": 150000000
  },
  "by_product": [...],
  "aging_analysis": [...]
}
```

#### Sales Report
```http
GET /reports/sales
Query Parameters:
  - date_from: date (required)
  - date_to: date (required)
  - customer_id: integer (optional)
  - sales_channel: string (optional)
  - format: string (html|pdf|excel)

Response: 200 OK
{
  "summary": {
    "total_orders": 100,
    "total_revenue": 50000000,
    "total_profit": 15000000,
    "avg_order_value": 500000
  },
  "by_product": [...],
  "by_channel": [...],
  "by_customer": [...]
}
```

#### Profit & Loss Report
```http
GET /reports/profit-loss
Query Parameters:
  - date_from: date (required)
  - date_to: date (required)
  - format: string (html|pdf|excel)

Response: 200 OK
{
  "revenue": {
    "sales": 50000000,
    "other_income": 0,
    "total": 50000000
  },
  "cost_of_goods_sold": {
    "materials": 20000000,
    "labor": 10000000,
    "overhead": 3000000,
    "total": 33000000
  },
  "gross_profit": 17000000,
  "operating_expenses": {
    "salaries": 5000000,
    "utilities": 1000000,
    "other": 1000000,
    "total": 7000000
  },
  "net_profit": 10000000,
  "profit_margin": 20.0
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 400  | Bad Request - Invalid input |
| 401  | Unauthorized - Authentication required |
| 403  | Forbidden - Insufficient permissions |
| 404  | Not Found - Resource not found |
| 422  | Unprocessable Entity - Validation failed |
| 429  | Too Many Requests - Rate limit exceeded |
| 500  | Internal Server Error |

## Rate Limiting

- 60 requests per minute per user
- 1000 requests per hour per tenant
- Rate limit headers included in response:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`

## Pagination

Default pagination: 15 items per page
Maximum per_page: 100

Response includes:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 10,
  "per_page": 15,
  "total": 150,
  "from": 1,
  "to": 15
}
```

## Filtering & Sorting

Most list endpoints support:
- `?sort=field` - Sort by field (ascending)
- `?sort=-field` - Sort by field (descending)
- `?filter[field]=value` - Filter by field value

## Webhooks (Future Enhancement)

Webhook events untuk integrasi external:
- `material.received`
- `production.completed`
- `inventory.low_stock`
- `sales.created`
- `sales.paid`
