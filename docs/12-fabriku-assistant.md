# Fabriku Assistant - AI-Powered Chat Assistant

> **Last Updated**: February 3, 2026  
> **Status**: ✅ Implemented & Production Ready

## Overview

Fabriku Assistant adalah fitur chat assistant berbasis AI yang terintegrasi dengan OpenAI GPT-4o, dirancang untuk membantu pengguna UMKM dalam mengelola bisnis mereka melalui percakapan natural. Assistant ini dapat diakses melalui:
- **Web Panel** - Floating chat widget di kanan bawah layar
- **Telegram** - ✅ Terintegrasi via Telegram Bot

## Current Implementation Status

| Feature | Status | Notes |
|---------|--------|-------|
| Web Chat Interface | ✅ Done | Floating widget with history |
| OpenAI Integration | ✅ Done | GPT-4o model |
| Conversation History | ✅ Done | Per-user persistent history |
| Telegram Bot | ✅ Done | Full integration |
| Usage Tracking | ✅ Done | Per tenant/user stats |
| Pending Actions | ✅ Done | Confirmation system |
| WhatsApp Integration | 🔮 Future | Planned enhancement |

## Business Goals

1. **Meningkatkan Produktivitas** - Pengguna dapat melakukan operasi kompleks melalui perintah natural tanpa perlu navigasi manual
2. **Aksesibilitas 24/7** - Assistant tersedia kapan saja untuk membantu pengguna
3. **Data-Driven Insights** - Memberikan analisis dan rekomendasi berdasarkan data bisnis
4. **Multi-Channel Support** - Akses dari berbagai platform (Web, Telegram)
5. **User-Friendly Experience** - Menurunkan learning curve untuk pengguna baru

## Target Users

- Pemilik UMKM yang ingin efisiensi dalam operasional
- Staff dengan berbagai level teknis
- Pengguna mobile yang membutuhkan akses cepat via messaging apps

---

## Core Features

### 1. Chat Interface (Web Panel)

**User Story**: Sebagai pengguna, saya ingin berkomunikasi dengan assistant melalui chat untuk mendapatkan bantuan tanpa meninggalkan halaman kerja.

**Features**:
- Floating chat button di kanan bawah (FAB - Floating Action Button)
- Chat window dengan history conversation
- Typing indicator saat AI sedang memproses
- Support untuk text, quick replies, dan action buttons
- Minimizable dan draggable chat window
- Dark mode support
- Notifikasi untuk pesan baru

### 2. Natural Language Understanding (NLU)

**Capabilities**:
- Memahami perintah dalam Bahasa Indonesia dan English
- Context-aware conversations (mengingat konteks percakapan)
- Intent recognition untuk berbagai operasi bisnis
- Entity extraction (tanggal, jumlah, nama produk, dll)

### 3. Information & Analytics

**User Story**: Sebagai pemilik usaha, saya ingin bertanya tentang kondisi bisnis dan mendapatkan analisis dari data yang ada.

**Query Examples**:
- "Berapa total penjualan bulan ini?"
- "Tunjukkan stok bahan baku yang hampir habis"
- "Material apa yang paling banyak digunakan minggu ini?"
- "Siapa pelanggan dengan pembelian terbesar?"
- "Produksi mana yang sedang dalam progress?"
- "Berapa outstanding payment yang belum lunas?"
- "Bandingkan penjualan bulan ini dengan bulan lalu"
- "Rata-rata waktu produksi untuk pattern mukena?"

**Available Data Access**:

| Module | Read | Analytics |
|--------|------|-----------|
| Dashboard KPIs | ✅ | ✅ |
| Materials | ✅ | ✅ |
| Material Receipts | ✅ | ✅ |
| Patterns | ✅ | ✅ |
| Preparation Orders | ✅ | ✅ |
| Production Orders | ✅ | ✅ |
| Inventory | ✅ | ✅ |
| Sales Orders | ✅ | ✅ |
| Customers | ✅ | ✅ |
| Contractors | ✅ | ✅ |
| Staff | ✅ | ✅ |

### 4. Action Execution (with Confirmation)

**User Story**: Sebagai staff, saya ingin melakukan operasi seperti membuat order atau update status melalui chat dengan konfirmasi.

**Supported Actions**:

#### Materials & Inventory
- "Tambahkan material baru: kain katun, 100 meter, supplier ABC"
- "Catat penerimaan kain 50 meter dari supplier XYZ"
- "Update stok benang jadi 25 kg"
- "Cek stok material yang di bawah minimum"

#### Preparation & Production
- "Buat preparation order untuk mukena 100 pcs"
- "Update status preparation PRP-2026-001 menjadi completed"
- "Buat production order dari preparation PRP-2026-001"
- "Tandai produksi PRD-2026-015 selesai"

#### Sales & Customers
- "Buat sales order untuk customer Toko Berkah"
- "Tambahkan customer baru: Ibu Ani, HP 081234567890"
- "Update status pembayaran order SO-2026-020 menjadi paid"
- "Generate invoice untuk order SO-2026-020"

#### Reports
- "Buatkan laporan penjualan bulan ini"
- "Export data inventory ke Excel"
- "Kirimkan ringkasan produksi minggu ini ke email saya"

### 5. Confirmation Flow

**PENTING**: Semua action yang mengubah data WAJIB melalui konfirmasi.

**Example Confirmation Flow**:

User: "Tambahkan material benang putih 50 kg dari supplier Jaya"

Assistant Response:
```json
{
  "intent": "create_material",
  "confirmation_required": true,
  "data": {
    "name": "Benang Putih",
    "quantity": 50,
    "unit": "kg",
    "supplier": "Jaya"
  },
  "message": "Saya akan menambahkan material baru dengan detail berikut:\n- Nama: Benang Putih\n- Jumlah: 50 kg\n- Supplier: Jaya\n\nApakah Anda yakin ingin melanjutkan?",
  "actions": [
    {"label": "Ya, Tambahkan", "action": "confirm"},
    {"label": "Batal", "action": "cancel"},
    {"label": "Edit", "action": "edit"}
  ]
}
```

**Confirmation UI**:
- Quick action buttons (Confirm/Cancel/Edit)
- Summary card dengan detail action
- Option untuk mengedit sebelum konfirmasi

### 6. Smart Suggestions

**Proactive Assistance**:
- Reminder untuk stok yang hampir habis
- Alert untuk payment yang jatuh tempo
- Suggestion untuk produksi berdasarkan trend penjualan
- Tips optimasi berdasarkan data historis

---

## Technical Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────────────┐
│                         User Channels                                │
├──────────────┬──────────────┬──────────────┬───────────────────────┤
│  Web Panel   │   Telegram   │   WhatsApp   │   Future Channels     │
│  (Vue Chat)  │    (Bot)     │   (WABA)     │   (Slack, Line, etc)  │
└──────┬───────┴──────┬───────┴──────┬───────┴───────────┬───────────┘
       │              │              │                    │
       └──────────────┴──────────────┴────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Message Gateway Layer                             │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  AssistantController (Laravel)                               │   │
│  │  - Route messages from all channels                          │   │
│  │  - Authenticate & authorize requests                         │   │
│  │  - Rate limiting per user/tenant                             │   │
│  └─────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    AI Processing Layer                               │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │  Conversation    │  │  OpenAI          │  │  Function        │  │
│  │  Manager         │  │  Integration     │  │  Calling         │  │
│  │  - Context mgmt  │  │  - GPT-4/4o API  │  │  - Tool registry │  │
│  │  - History       │  │  - Embeddings    │  │  - Action exec   │  │
│  │  - Session       │  │  - Assistants    │  │  - Confirmations │  │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Business Logic Layer                              │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Assistant Tools (Function Definitions)                       │  │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │  - MaterialTool: CRUD materials, receipts, stock queries     │  │
│  │  - PreparationTool: Create/update prep orders, status        │  │
│  │  - ProductionTool: Manage production orders                  │  │
│  │  - InventoryTool: Stock levels, locations, adjustments       │  │
│  │  - SalesTool: Orders, customers, payments                    │  │
│  │  - AnalyticsTool: Reports, dashboards, trends                │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                              │                                       │
│                              ▼                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Existing Services & Repositories                             │  │
│  │  (Reuse existing business logic)                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Data Layer                                        │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────────┐    │
│  │   PostgreSQL   │  │     Redis      │  │    File Storage    │    │
│  │   (Primary)    │  │   (Sessions,   │  │   (Attachments)    │    │
│  │                │  │    Cache)      │  │                    │    │
│  └────────────────┘  └────────────────┘  └────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
```

### Database Schema (New Tables)

```sql
-- Conversation sessions
CREATE TABLE assistant_conversations (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    channel VARCHAR(50) NOT NULL DEFAULT 'web', -- web, telegram, whatsapp
    external_chat_id VARCHAR(255), -- For Telegram/WhatsApp
    status VARCHAR(50) DEFAULT 'active', -- active, archived
    context JSONB, -- Conversation context/memory
    last_message_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Individual messages
CREATE TABLE assistant_messages (
    id BIGSERIAL PRIMARY KEY,
    conversation_id BIGINT NOT NULL REFERENCES assistant_conversations(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL, -- user, assistant, system
    content TEXT NOT NULL,
    tokens_used INTEGER,
    metadata JSONB, -- Intent, entities, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pending actions awaiting confirmation
CREATE TABLE assistant_pending_actions (
    id BIGSERIAL PRIMARY KEY,
    conversation_id BIGINT NOT NULL REFERENCES assistant_conversations(id) ON DELETE CASCADE,
    message_id BIGINT REFERENCES assistant_messages(id) ON DELETE CASCADE,
    action_type VARCHAR(100) NOT NULL,
    action_data JSONB NOT NULL,
    status VARCHAR(50) DEFAULT 'pending', -- pending, confirmed, cancelled, expired
    expires_at TIMESTAMP,
    confirmed_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usage tracking for billing/limits
CREATE TABLE assistant_usage (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    date DATE NOT NULL,
    message_count INTEGER DEFAULT 0,
    tokens_input INTEGER DEFAULT 0,
    tokens_output INTEGER DEFAULT 0,
    actions_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tenant_id, user_id, date)
);

-- Indexes
CREATE INDEX idx_conversations_tenant_user ON assistant_conversations(tenant_id, user_id);
CREATE INDEX idx_conversations_channel ON assistant_conversations(channel);
CREATE INDEX idx_messages_conversation ON assistant_messages(conversation_id);
CREATE INDEX idx_pending_actions_status ON assistant_pending_actions(status);
CREATE INDEX idx_usage_tenant_date ON assistant_usage(tenant_id, date);
```

### OpenAI Integration

**Model Selection**:
- **GPT-4o** for complex queries and actions (primary)
- **GPT-4o-mini** for simple Q&A (cost optimization)
- **Embeddings** for semantic search in conversation history

**Function Calling Schema**:
```json
{
  "name": "get_sales_summary",
  "description": "Get sales summary for a specific period",
  "parameters": {
    "type": "object",
    "properties": {
      "start_date": {
        "type": "string",
        "description": "Start date in YYYY-MM-DD format"
      },
      "end_date": {
        "type": "string",
        "description": "End date in YYYY-MM-DD format"
      },
      "group_by": {
        "type": "string",
        "enum": ["day", "week", "month"],
        "description": "Grouping period"
      }
    },
    "required": ["start_date", "end_date"]
  }
}
```

### API Endpoints

```
POST   /api/assistant/message          # Send message
GET    /api/assistant/conversations    # List conversations
GET    /api/assistant/conversations/{id} # Get conversation with messages
DELETE /api/assistant/conversations/{id} # Archive conversation
POST   /api/assistant/actions/{id}/confirm  # Confirm pending action
POST   /api/assistant/actions/{id}/cancel   # Cancel pending action

# Webhook endpoints for messaging platforms
POST   /webhooks/telegram/assistant    # Telegram webhook
POST   /webhooks/whatsapp/assistant    # WhatsApp webhook
```

---

## Security & Permissions

### Authorization

- Assistant respects existing RBAC (Role-Based Access Control)
- Users can only query/modify data they have access to
- Admin actions require admin role
- Sensitive operations require additional confirmation

### Rate Limiting

| Plan | Messages/Day | Tokens/Day | Actions/Day |
|------|-------------|------------|-------------|
| Trial | 50 | 10,000 | 10 |
| Basic | 200 | 50,000 | 50 |
| Pro | Unlimited | 200,000 | 200 |
| Enterprise | Unlimited | Unlimited | Unlimited |

### Data Privacy

- Conversation data encrypted at rest
- No training on user data (OpenAI API policy compliant)
- Data retention configurable per tenant
- GDPR compliant data export/deletion

---

## Multi-Channel Integration

### Phase 1: Web Chat (MVP)

**Implementation**:
- Vue 3 component for chat interface
- WebSocket for real-time messaging
- LocalStorage for draft messages
- Service Worker for offline support

### Phase 2: Telegram Integration (Next Feature)

**Setup**:
1. Create Telegram Bot via BotFather
2. Configure webhook URL
3. Link Telegram account to Fabriku user

**Features**:
- Natural conversation via Telegram
- Inline keyboards for confirmations
- Photo upload for receipts
- Location sharing for delivery

**Telegram-Specific Commands**:
```
/start - Link account
/status - Quick status check
/sales - Today's sales summary
/stock - Low stock alerts
/help - Available commands
```

### Phase 3: WhatsApp Integration (Future)

**Requirements**:
- WhatsApp Business API (WABA) account
- Meta Business verification
- Approved message templates

**Features**:
- Template messages for notifications
- Interactive buttons and lists
- Media support (images, documents)
- Catalog integration

---

## User Interface Design

### Web Chat Widget

```
┌────────────────────────────────────────────┐
│ ┌────────────────────────────────────────┐ │
│ │  🤖 Fabriku Assistant              ─ × │ │ <- Header with minimize/close
│ ├────────────────────────────────────────┤ │
│ │                                        │ │
│ │  ┌──────────────────────────────────┐  │ │
│ │  │ 👤 Berapa penjualan hari ini?   │  │ │ <- User message
│ │  └──────────────────────────────────┘  │ │
│ │                                        │ │
│ │  ┌──────────────────────────────────┐  │ │
│ │  │ 🤖 Total penjualan hari ini:    │  │ │ <- Assistant message
│ │  │                                  │  │ │
│ │  │ 💰 Rp 15.500.000                │  │ │
│ │  │ 📦 23 orders                    │  │ │
│ │  │ 📈 +12% dari kemarin            │  │ │
│ │  │                                  │  │ │
│ │  │ [Lihat Detail] [Export]         │  │ │ <- Quick actions
│ │  └──────────────────────────────────┘  │ │
│ │                                        │ │
│ ├────────────────────────────────────────┤ │
│ │  Quick Actions:                        │ │
│ │  [📊 Dashboard] [📦 Stok] [💰 Sales]  │ │ <- Suggestions
│ ├────────────────────────────────────────┤ │
│ │ ┌──────────────────────────────┐  [➤] │ │
│ │ │ Ketik pesan...               │      │ │ <- Input area
│ │ └──────────────────────────────┘      │ │
│ └────────────────────────────────────────┘ │
│                                            │
│                                   [💬]     │ <- FAB (collapsed state)
└────────────────────────────────────────────┘
```

### Confirmation Modal

```
┌─────────────────────────────────────────────┐
│       ⚠️ Konfirmasi Aksi                    │
├─────────────────────────────────────────────┤
│                                             │
│  Anda akan menambahkan material baru:       │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ Nama     : Benang Putih             │   │
│  │ Jumlah   : 50 kg                    │   │
│  │ Supplier : Jaya                     │   │
│  │ Harga    : Rp 25.000/kg             │   │
│  │ Total    : Rp 1.250.000             │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [Batal]  [Edit]  [✓ Konfirmasi]           │
│                                             │
└─────────────────────────────────────────────┘
```

---

## Implementation Phases

### Phase 1: Foundation (MVP) - 4 Weeks

**Week 1-2: Backend Infrastructure**
- [ ] Database migrations for assistant tables
- [ ] OpenAI integration service
- [ ] Basic conversation management
- [ ] Rate limiting implementation
- [ ] API endpoints for chat

**Week 3-4: Frontend & Basic Features**
- [ ] Vue chat widget component
- [ ] WebSocket setup for real-time
- [ ] Basic Q&A functionality (read-only)
- [ ] Dark mode support
- [ ] Mobile responsive design

**Deliverables**:
- Working chat widget in user panel
- Basic information queries (sales, inventory, etc.)
- Conversation history
- Rate limiting

### Phase 2: Actions & Intelligence - 3 Weeks

**Week 5-6: Action System**
- [ ] Function calling implementation
- [ ] Confirmation flow for actions
- [ ] CRUD operations via chat
- [ ] Action logging and audit trail

**Week 7: Smart Features**
- [ ] Context-aware conversations
- [ ] Smart suggestions engine
- [ ] Proactive alerts
- [ ] Error handling improvements

**Deliverables**:
- Full CRUD via chat with confirmations
- Smart suggestions
- Proactive notifications

### Phase 3: Telegram Integration - 3 Weeks

**Week 8-9: Telegram Bot Setup**
- [ ] Bot creation and configuration
- [ ] Webhook handler
- [ ] Account linking flow
- [ ] Basic commands

**Week 10: Telegram Features**
- [ ] Inline keyboards for confirmations
- [ ] Media handling
- [ ] Notification preferences
- [ ] Testing and polish

**Deliverables**:
- Full Telegram bot integration
- Account linking
- Same features as web chat

### Phase 4: WhatsApp Integration - 4 Weeks

**Week 11-12: WABA Setup**
- [ ] Business verification
- [ ] API integration
- [ ] Message templates approval
- [ ] Webhook handler

**Week 13-14: WhatsApp Features**
- [ ] Interactive messages
- [ ] Media support
- [ ] Catalog integration
- [ ] Testing and compliance

**Deliverables**:
- WhatsApp Business integration
- Template messages
- Full feature parity

### Phase 5: Advanced Features - 2 Weeks

**Week 15-16: Enhancements**
- [ ] Voice message support (speech-to-text)
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Custom training data per tenant
- [ ] A/B testing framework

**Deliverables**:
- Voice support
- Enhanced analytics
- Performance optimizations

---

## Success Metrics

### User Engagement
- Daily Active Users (DAU) using assistant
- Messages per user per day
- Feature adoption rate

### Efficiency Metrics
- Time saved per operation
- Reduction in support tickets
- User satisfaction score (CSAT)

### Technical Metrics
- Response time (p50, p95, p99)
- API error rate
- Token efficiency (tokens per useful response)

### Business Metrics
- Conversion rate (trial to paid)
- Feature usage correlation with retention
- Revenue impact

---

## Cost Estimation

### OpenAI API Costs (Estimated)

| Model | Input (per 1M tokens) | Output (per 1M tokens) |
|-------|----------------------|------------------------|
| GPT-4o | $2.50 | $10.00 |
| GPT-4o-mini | $0.15 | $0.60 |

**Monthly Estimate per Tenant (Basic Plan)**:
- ~50,000 input tokens × $0.15/1M = $0.0075
- ~25,000 output tokens × $0.60/1M = $0.015
- **Total: ~$0.02/tenant/month** (using GPT-4o-mini for most queries)

### Infrastructure Costs
- Redis for sessions: Included in existing infra
- Additional storage: ~$5/month for 100 tenants
- WebSocket server: Included (Laravel Reverb/Soketi)

---

## Risk Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| OpenAI API downtime | High | Fallback to cached responses, graceful degradation |
| Cost overrun | Medium | Rate limiting, model selection optimization |
| Data privacy concerns | High | Clear privacy policy, no training on user data |
| Inaccurate responses | Medium | Confidence scoring, human escalation |
| User adoption low | Medium | Onboarding tutorial, proactive suggestions |

---

## Future Enhancements

1. **Voice Assistant** - Hands-free operation
2. **Predictive Analytics** - AI-driven business insights
3. **Custom AI Training** - Per-tenant fine-tuning
4. **Multi-tenant Admin Assistant** - For platform admins
5. **Integration Marketplace** - Third-party tool integrations
6. **Offline Mode** - Basic functionality without internet

---

## Appendix

### A. Sample Conversation Flows

**Flow 1: Simple Query**
```
User: Berapa stok kain katun?
Assistant: Stok kain katun saat ini:
- Kain Katun Putih: 150 meter (Min: 50m) ✅
- Kain Katun Hitam: 45 meter (Min: 50m) ⚠️ Hampir habis
- Kain Katun Merah: 80 meter (Min: 30m) ✅

Ada yang perlu diorder?
```

**Flow 2: Action with Confirmation**
```
User: Buat sales order untuk Toko Berkah, 10 pcs mukena putih harga 150rb
Assistant: Saya akan membuat sales order dengan detail:
- Customer: Toko Berkah
- Produk: Mukena Putih
- Qty: 10 pcs
- Harga: Rp 150.000/pcs
- Total: Rp 1.500.000

[Konfirmasi] [Batal] [Edit]
```

**Flow 3: Complex Analysis**
```
User: Bandingkan penjualan bulan ini dengan bulan lalu
Assistant: Perbandingan Penjualan:

Bulan Ini (Jan 2026):
- Total: Rp 45.500.000
- Orders: 156
- Avg Order: Rp 291.667

Bulan Lalu (Des 2025):
- Total: Rp 52.300.000
- Orders: 189
- Avg Order: Rp 276.720

Analisis:
- Penurunan revenue: 13% (-Rp 6.8jt)
- Penurunan orders: 17% (-33 orders)
- Peningkatan nilai per order: 5%

Rekomendasi: Fokus pada akuisisi customer baru untuk meningkatkan jumlah order.
```

### B. Error Handling Examples

**Invalid Request**
```
User: Delete semua data
Assistant: Maaf, saya tidak dapat melakukan penghapusan data secara massal. Untuk keamanan, silakan lakukan penghapusan melalui panel admin dengan approval yang sesuai.
```

**Insufficient Permission**
```
User: Lihat laporan keuangan detail
Assistant: Maaf, Anda tidak memiliki akses ke laporan keuangan detail. Fitur ini hanya tersedia untuk role Admin dan Manager. Silakan hubungi administrator untuk akses.
```

**Ambiguous Request**
```
User: Tambah material baru
Assistant: Untuk menambahkan material baru, saya butuh informasi berikut:
- Nama material
- Jumlah
- Satuan (kg/meter/pcs/dll)
- Supplier (opsional)
- Harga per unit (opsional)

Contoh: 'Tambahkan material kain katun 100 meter dari supplier ABC harga 25rb per meter'
```

### C. Supported Languages

- Bahasa Indonesia (Primary)
- English (Secondary)
- Javanese (Future)
- Sundanese (Future)

---

*Document Version: 1.0*
*Last Updated: February 2026*
*Author: Fabriku Development Team*
