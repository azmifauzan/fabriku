# Architecture Update: Multi-Category Business Support

**Date**: January 11, 2026  
**Status**: ✅ Documentation Updated  
**Impact**: Major - Fundamental shift in application scope

---

## 🎯 Change Overview

Fabriku has been redesigned from a **garment-specific** application to a **multi-category production management platform** supporting various types of UMKM businesses.

### Before
- Single category: Garment production only
- Fixed terminology (fabric, cutting, sewing)
- Garment-specific business rules

### After
- Multi-category platform with 2 MVP categories:
  1. **Garment** - Pakaian (mukena, daster, gamis)
  2. **Kue Rumahan** - Makanan/kue (cake, brownies, cookies)
- Generic terminology adaptable per category
- Flexible business rules per category
- Future expandable to craft, cosmetics, etc.

---

## 🏗️ Architectural Changes

### 1. Category-Agnostic Database Schema

**Design Philosophy**: Tables use generic names that work for all categories

| Old (Garment-only) | New (Generic) | Applies To |
|-------------------|---------------|------------|
| fabrics | materials | Raw materials/ingredients |
| fabric_colors | material_attributes | Dynamic attributes (color, expired date, etc) |
| cutting_patterns | patterns | Product templates (pattern/recipe) |
| cutting_orders | cutting_orders* | Preparation process (cutting/mixing) |
| sewing_orders | production_orders | Main production (sewing/baking) |

*Note: `cutting_orders` kept generic to represent "preparation phase" for any category

### 2. Dynamic Material Attributes

**Problem**: Different categories need different material attributes
- Garment: warna, lebar kain, gramasi
- Kue: expired date, storage temp, batch

**Solution**: Separate `material_attributes` table
```sql
CREATE TABLE material_attributes (
    material_id BIGINT REFERENCES materials,
    attribute_name VARCHAR(100),  -- flexible key
    attribute_value TEXT,         -- flexible value
    ...
);
```

**Benefits**:
- ✅ Unlimited attributes per material
- ✅ No schema changes untuk add new attributes
- ✅ Category-specific attributes coexist

### 3. Workflow Abstraction

**Universal Workflow** (all categories):
```
Raw Materials → Recipe/Pattern → Preparation → Production → Inventory → Sales
```

**Category Implementations**:

**Garment**:
```
Kain → Pattern → Cutting → Sewing → Inventory → Sales
```

**Kue**:
```
Bahan Mentah → Resep → Mixing → Baking → Inventory → Sales
```

### 4. Business Rules Engine

**Category-Specific Rules** stored in application logic:

```php
// Garment rules
- Pattern sizes: XS, S, M, L, XL, XXL, all_size
- Quality grades: Grade A, B, Reject
- Waste percentage: 3-10% standard

// Kue rules  
- Expired date tracking (CRITICAL!)
- Storage temp requirements
- Shelf life alerts
- Food safety compliance
```

---

## 📋 Updated Components

### Documentation Files Updated
1. ✅ `docs/01-business-requirements.md`
   - Added multi-category overview
   - Category-specific user stories
   - Separate business rules per category

2. ✅ `docs/03-database-schema.md`
   - Added category-agnostic design notes
   - Updated materials table definition
   - Added material_attributes table
   - Added pattern_materials (BOM) table

3. ✅ `docs/06-mvp-development-plan.md`
   - Updated MVP scope untuk 2 categories
   - Category-specific feature list
   - Future expansion categories

4. ✅ `.github/copilot-instructions.md`
   - Updated project overview dengan multi-category
   - Added category-specific terminology
   - Category-aware business rules

5. ✅ `docs/README.md` (NEW)
   - Comprehensive project overview
   - Target industries & workflows
   - Tech stack & development progress

---

## 🎨 Implementation Strategy

### Phase 3 (Current) Already Category-Agnostic ✅

**Database Schema**:
- ✅ `patterns` table dengan generic `product_type` enum
- ✅ `pattern_materials` for flexible BOM
- ✅ `cutting_orders` bisa untuk cutting atau mixing
- ✅ `cutting_results` tracks efficiency untuk any prep process

**Models**:
- ✅ Pattern model tidak hardcode garment terms
- ✅ CuttingOrder bisa dipakai untuk prep phase any category
- ✅ Relationships generic (belongsTo Pattern, hasMany Results)

**No Code Changes Needed!** 🎉

### Future Phases - Category Support

**Phase 4: Production Management**
- `contractors` → mitra produksi (penjahit/dapur sharing)
- `production_orders` → proses produksi (sewing/baking)
- Quality control disesuaikan kategori

**Phase 5: Inventory**
- Expired date tracking untuk food
- Storage location requirements
- FIFO vs FEFO based on category

**Phase 6: Sales**
- Same for all categories (already generic)

---

## 🔧 Frontend Adaptations

### Category-Specific UI (To Implement)

**Terminology Adaptation**:
```vue
// Conditional labels based on tenant category
<label>
  {{ tenantCategory === 'garment' ? 'Pattern' : 'Resep' }}
</label>

<label>
  {{ tenantCategory === 'garment' ? 'Cutting Order' : 'Prep Order' }}
</label>
```

**Dynamic Forms**:
```vue
// Show different fields per category
<div v-if="tenantCategory === 'garment'">
  <input label="Warna" />
  <input label="Lebar Kain" />
</div>

<div v-if="tenantCategory === 'food'">
  <input label="Expired Date" type="date" />
  <input label="Storage Temp" />
</div>
```

---

## 📊 Database Impact

### Current Schema Status

**Already Multi-Category** ✅:
- `materials` - generic enough
- `material_attributes` - fully flexible
- `patterns` - has `product_type` for any category
- `pattern_materials` - BOM works universally
- `cutting_orders` - generic "preparation"
- `cutting_results` - generic output tracking

**Needs Category Column** (Future):
- `tenants` table - add `business_category` enum
  - Values: 'garment', 'food', 'craft', 'cosmetics'
  - Determines UI terminology & business rules

**No Breaking Changes!** All existing garment data still valid.

---

## 🧪 Testing Strategy

### Test Coverage by Category

**Unit Tests**:
- Test business rules per category separately
- Example: `PatternTest` with garment AND food fixtures

**Feature Tests**:
- Generic workflow tests (work for all categories)
- Category-specific validation tests

**Browser Tests**:
- Test UI adapts to tenant category
- Verify terminology changes correctly

---

## 🎯 Benefits of This Architecture

### 1. Code Reusability
- ✅ Same controllers, services, models
- ✅ Less duplication
- ✅ Easier maintenance

### 2. Scalability
- ✅ Add new categories without major refactor
- ✅ Just add enum values + business rules
- ✅ Database schema stays stable

### 3. User Experience
- ✅ Familiar terminology per industry
- ✅ Context-relevant features
- ✅ Industry-specific best practices

### 4. Market Expansion
- ✅ Appeal to multiple UMKM segments
- ✅ Larger addressable market
- ✅ Cross-category learnings

---

## 📝 Action Items

### Immediate (Before Phase 4)
- [ ] Add `business_category` to tenants table
- [ ] Implement tenant category selection during onboarding
- [ ] Add category-based terminology helper functions
- [ ] Update seeders to include food category examples

### Short Term (Phase 4-6)
- [ ] Category-specific form fields in PatternForm
- [ ] Conditional validation rules based on category
- [ ] Category-aware reports & analytics
- [ ] Help documentation per category

### Long Term (Post-MVP)
- [ ] Add craft category
- [ ] Add cosmetics category
- [ ] Category-specific dashboard widgets
- [ ] Industry benchmarking per category

---

## 🚀 Migration Path

### For Existing Garment Users
1. Existing data remains unchanged
2. System auto-assigns `business_category = 'garment'`
3. UI terminology stays the same
4. Zero downtime migration

### For New Users
1. Choose category during onboarding
2. See category-appropriate terminology
3. Get industry-specific templates/patterns
4. Receive category-relevant guidance

---

## 📚 Developer Guidelines

### When Adding New Features

**Ask These Questions**:
1. Is this feature universal or category-specific?
2. Should terminology change per category?
3. Do validation rules differ by category?
4. Are there category-specific attributes needed?

**Implementation Checklist**:
- [ ] Use generic naming in database/backend
- [ ] Add category conditions in frontend
- [ ] Write tests for all supported categories
- [ ] Update documentation with category notes
- [ ] Add category-specific examples

### Code Patterns

**❌ Avoid**:
```php
// Hardcoded garment terms
$cuttingOrder->fabric_used
$pattern->fabric_requirement
```

**✅ Prefer**:
```php
// Generic terms
$preparationOrder->material_used
$pattern->materials()->sum('quantity_needed')
```

---

## 🎓 Learning Resources

### Understanding Multi-Tenancy
- Each tenant has isolated data
- Tenant chooses their category
- Category determines UI terminology & business rules

### Category Examples in Code

**Material Attributes** (Garment):
```php
$material->attributes()->create([
    'attribute_name' => 'warna',
    'attribute_value' => 'Merah'
]);
```

**Material Attributes** (Kue):
```php
$material->attributes()->create([
    'attribute_name' => 'expired_date',
    'attribute_value' => '2026-02-01'
]);
```

---

**Summary**: Architecture successfully evolved to support multi-category businesses while maintaining code simplicity and database efficiency. Current Phase 3 implementation already category-agnostic. Future phases will add category-specific UI adaptations and business rule engines.

**Status**: ✅ Documentation Complete | 🚧 Implementation Ongoing
