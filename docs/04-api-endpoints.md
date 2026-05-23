# API Endpoints & Routes - Fabriku

> **Last Updated**: February 3, 2026

## Overview

Fabriku menggunakan **Inertia.js** untuk routing dan rendering modern. Ini berarti sebagian besar endpoint mengembalikan JSON (Inertia response) yang dirender oleh frontend, bukan REST API murni. Namun, struktur endpoint mengikuti resource controller Laravel standar.

**Total Routes**: 175+ routes

## Base URL
```
Development: http://localhost:8000
Production: https://app.fabriku.com
```

## Route Groups

### 1. Landing & Authentication (Public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Landing page |
| GET | `/login` | Show login form |
| POST | `/login` | Process login |
| GET | `/register` | Show register form |
| POST | `/register` | Process registration |
| POST | `/logout` | Logout user |

### 2. Tenant Dashboard (Protected)
Prefix: `/dashboard`, `/reports`, `/inventory`, etc.
Middleware: `auth`, `tenant`

#### Core
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | Tenant dashboard home |
| GET | `/settings` | Tenant settings |
| POST | `/settings` | Update settings |
| GET | `/subscription` | Subscription page |
| POST | `/subscription` | Create/Update subscription |

#### Inventory Management
Prefix: `/inventory`
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/inventory/visualization` | Visual warehouse layout |
| GET | `/inventory/locations` | List locations |
| POST | `/inventory/locations` | Create location |
| GET | `/inventory/items` | List inventory items |
| POST | `/inventory/items` | Create new item (from Production/Opening Balance) |
| POST | `/inventory/items/{id}/adjust` | Adjust stock (Correction, Damage, etc) |
| GET | `/inventory/items/{id}/adjustments` | View adjustment history |

#### Materials & Production
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/materials` | List materials |
| POST | `/materials` | Create material |
| POST | `/materials/{material}/archive` | Archive (Soft delete) |
| GET | `/material-receipts` | List entries (Stock In) |
| POST | `/material-receipts` | Create entry |
| GET | `/patterns` | List product templates |
| POST | `/patterns` | Create pattern |
| GET | `/preparation-orders` | Cutting/Prep orders |
| POST | `/preparation-orders` | Create prep order |
| GET | `/production-orders` | Production orders (Sewing/Assembly) |
| POST | `/production-orders` | Create production order |
| POST | `/production-orders/{id}/start` | Start production |
| POST | `/production-orders/{id}/send` | Send to contractor |
| POST | `/production-orders/{id}/mark-complete` | Finish production |

#### Sales
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/sales-orders` | List sales orders |
| POST | `/sales-orders` | Create sales order |
| GET | `/sales-orders/{id}/print` | Print invoice/DO |
| GET | `/sales-orders/{id}/export` | Export to PDF/Excel |
| GET | `/customers` | List customers |
| POST | `/customers` | Create customer |

#### Reports
Prefix: `/reports`
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports/material` | Material usage report |
| GET | `/reports/inventory` | Stock position report |
| GET | `/reports/sales` | Sales analysis |
| GET | `/reports/sales-recap` | Recap view |
| GET | `/reports/production` | Efficiency & defects report |

### 3. Admin Panel (Super Admin)
Prefix: `/admin`
Middleware: `auth:admin`

#### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/login` | Admin login form |
| POST | `/admin/login` | Process admin login |
| POST | `/admin/logout` | Admin logout |

#### Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin` | Admin Dashboard |
| GET | `/admin/tenants` | List all tenants |
| POST | `/admin/tenants` | Create tenant |
| POST | `/admin/tenants/{id}/suspend` | Suspend tenant |
| POST | `/admin/tenants/{id}/activate` | Activate tenant |
| GET | `/admin/users` | Global user lookup |
| POST | `/admin/users/{id}/reset-password` | Force password reset |
| GET | `/admin/roles` | Manage system roles |
| GET | `/admin/payments` | Subscription payment approvals |
| POST | `/admin/payments/{id}/approve` | Approve payment |
| POST | `/admin/payments/{id}/reject` | Reject payment |
| GET | `/admin/settings` | System-wide settings |
| GET | `/admin/audit-logs` | View system audit trail |

## Request & Response

### Headers
- **X-CSRF-TOKEN**: Required for all POST/PUT/DELETE requests (handled automatically by Axios/Inertia).
- **Accept**: `application/json` (for API calls) or `text/html` (for page visits).

### Inertia Response Structure
Success response typically returns a JSON object containing the component name and props:
```json
{
  "component": "Dashboard/Index",
  "props": {
    "auth": { "user": {...} },
    "metrics": {...},
    "errors": {}
  },
  "url": "/dashboard",
  "version": "..."
}
```

### Validation Errors
Laravel validation errors are returned in the `props.errors` object for Inertia, or status 422 for pure JSON requests.
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

## Route Groups (Additional)

### 5. Telegram Integration
Prefix: `/telegram`, `/api/telegram`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/settings/telegram` | Telegram settings page |
| POST | `/telegram/generate-token` | Generate connect token |
| POST | `/telegram/disconnect` | Disconnect Telegram |
| POST | `/telegram/test` | Send test message |
| POST | `/api/telegram/webhook` | Webhook endpoint for bot |

### 6. Email Verification
Prefix: `/verify-email`, `/email`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/verify-email` | Show verification notice |
| GET | `/verify-email/{id}/{hash}` | Verify email (signed URL) |
| POST | `/email/verification-notification` | Resend verification |

### 7. Admin Monitoring
Prefix: `/admin/monitoring`
Middleware: `auth:admin`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/monitoring` | System monitoring dashboard |
| POST | `/admin/monitoring/run-command` | Run artisan command |
| POST | `/admin/monitoring/test-telegram` | Test Telegram notification |
| POST | `/admin/monitoring/jobs/flush` | Flush failed jobs |
| POST | `/admin/monitoring/jobs/retry-all` | Retry all failed jobs |
| DELETE | `/admin/monitoring/jobs/{uuid}` | Delete specific job |
| POST | `/admin/monitoring/jobs/{uuid}/retry` | Retry specific job |
