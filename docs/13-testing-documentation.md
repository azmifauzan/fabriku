# Testing Documentation - Fabriku

> **Last Updated**: February 10, 2026  
> **Test Framework**: Pest 4.3.1  
> **Total Tests**: 100+

## 🧪 Testing Strategy

Fabriku menggunakan pendekatan testing yang comprehensive dengan focus pada:

1. **Integration Tests**: Memastikan complete user journeys berfungsi dengan baik
2. **Feature Tests**: Menguji setiap fitur secara individual
3. **Unit Tests**: Menguji logic bisnis dan utilities
4. **Browser Tests**: End-to-end testing dengan real browser (Pest 4)

## 📁 Test Structure

```
tests/
├── Pest.php                    # Pest configuration
├── TestCase.php                # Base test case
├── Feature/                    # Feature & Integration Tests
│   ├── Integration/            # Complete user journey tests
│   │   ├── CompleteUserJourneyTest.php
│   │   ├── RegistrationAndAuthenticationTest.php
│   │   ├── MaterialToPreparationFlowTest.php
│   │   ├── InventoryAndQRCodeTest.php
│   │   ├── SubscriptionAndSettingsTest.php
│   │   └── AssistantAndReportsTest.php
│   ├── Auth/                   # Authentication tests
│   ├── MaterialTest.php
│   ├── PatternTest.php
│   ├── PreparationOrderTest.php
│   ├── ProductionOrderTest.php
│   ├── InventoryItemTest.php
│   ├── SalesOrderTest.php
│   ├── ReportTest.php
│   ├── AssistantTest.php
│   ├── TelegramTest.php
│   └── ...
├── Unit/                       # Unit tests
│   └── ...
└── Browser/                    # Browser tests
    └── ...
```

## 🚀 Running Tests

### Basic Commands

```bash
# Run all tests
php artisan test

# Run all tests with compact output
php artisan test --compact

# Run specific test suite
php artisan test --filter Integration

# Run single test file
php artisan test tests/Feature/Integration/CompleteUserJourneyTest.php

# Run single test
php artisan test --filter "it completes full garment workflow"

# Run tests in parallel (faster)
php artisan test --parallel
```

### Coverage & Reporting

```bash
# Run tests with coverage
php artisan test --coverage

# Minimum coverage threshold
php artisan test --coverage --min=80

# Generate HTML coverage report
php artisan test --coverage-html coverage-report
```

## 📋 Integration Test Suites

### 1. Complete User Journey Tests

**File**: `tests/Feature/Integration/CompleteUserJourneyTest.php`

**Coverage**:
- ✅ Complete garment workflow (Material → Pattern → Cutting → Sewing → Inventory → Sales)
- ✅ Complete food workflow (Material → Recipe → Mixing → Baking → Inventory → Sales)
- ✅ Material receipt and stock management
- ✅ Production order lifecycle
- ✅ Inventory creation and management
- ✅ Sales order with stock deduction
- ✅ Report access

**Scenarios Tested**:
1. Login as admin
2. Create material type and material
3. Receive material (100 meters of fabric)
4. Create pattern (Mukena)
5. Create preparation order (cutting)
6. Complete preparation (auto stock deduction)
7. Create contractor and production order
8. Complete production with quality check
9. Add to inventory
10. Create customer and sales order
11. Confirm order (stock deduction)
12. View all reports

### 2. Registration & Authentication Tests

**File**: `tests/Feature/Integration/RegistrationAndAuthenticationTest.php`

**Coverage**:
- ✅ Complete registration flow
- ✅ Email verification
- ✅ Login/logout
- ✅ Password reset
- ✅ Subscription enforcement
- ✅ Suspended tenant prevention
- ✅ Validation testing

**Scenarios Tested**:
1. User registration with business details
2. Tenant and admin user creation
3. Email verification flow
4. Login with credentials
5. Dashboard access after verification
6. Password reset request
7. Subscription expiry check
8. Tenant suspension check

### 3. Material to Preparation Flow Tests

**File**: `tests/Feature/Integration/MaterialToPreparationFlowTest.php`

**Coverage**:
- ✅ Material type creation
- ✅ Material creation and receipt
- ✅ Stock tracking (FIFO)
- ✅ Pattern/recipe creation
- ✅ Preparation order workflow
- ✅ Material usage tracking
- ✅ Auto stock deduction
- ✅ Expiry date tracking (food)

**Scenarios Tested**:
1. Create material type (Fabric/Ingredient)
2. Create material (Kain Katun / Coklat Bubuk)
3. Receive material with attributes
4. Create pattern/recipe
5. Create preparation order
6. Add materials to preparation
7. Complete preparation (stock auto-deduct)
8. Verify stock levels

### 4. Inventory & QR Code Tests

**File**: `tests/Feature/Integration/InventoryAndQRCodeTest.php`

**Coverage**:
- ✅ Inventory location management
- ✅ Inventory item tracking
- ✅ Stock adjustments (damage, lost, found)
- ✅ Adjustment history
- ✅ Negative stock prevention
- ✅ Food expiry tracking
- ✅ QR code generation
- ✅ QR code scanning/lookup
- ✅ Inventory visualization
- ✅ Sales order stock integration

**Scenarios Tested**:
1. Create and manage locations (racks)
2. Add items to inventory
3. Adjust stock (damage, found, lost)
4. View adjustment history
5. Prevent negative stock
6. Track expiry dates (food)
7. Generate QR codes
8. Scan and lookup items
9. Reserve stock for sales
10. Deduct stock on confirmation
11. Release stock on cancellation

### 5. Subscription & Settings Tests

**File**: `tests/Feature/Integration/SubscriptionAndSettingsTest.php`

**Coverage**:
- ✅ Subscription page access
- ✅ Plan upgrade (Membership, Pro)
- ✅ Payment recording
- ✅ Payment amount validation
- ✅ Trial expiry redirection
- ✅ Settings management
- ✅ Multi-user tenant access
- ✅ Data isolation between tenants

**Scenarios Tested**:
1. View subscription page
2. Upgrade from trial to membership
3. Upgrade to pro plan
4. Record payment proof
5. Handle expired subscription
6. Update company settings
7. Manage user roles
8. Verify tenant data isolation

### 6. AI Assistant & Reports Tests

**File**: `tests/Feature/Integration/AssistantAndReportsTest.php`

**Coverage**:
- ✅ AI Assistant conversation
- ✅ Conversation history
- ✅ Usage tracking
- ✅ Pro-only feature enforcement
- ✅ Telegram token generation
- ✅ Telegram connection management
- ✅ All report views (Material, Inventory, Sales, Production)
- ✅ Report exports (Excel/PDF)
- ✅ Date range filtering

**Scenarios Tested**:
1. Start AI conversation
2. Track conversation history
3. View usage statistics
4. Enforce pro-only access
5. Generate Telegram token
6. Connect/disconnect Telegram
7. View material report
8. Export inventory report
9. Filter sales report by date
10. Generate production report

## 🎯 Test Coverage Goals

### Current Coverage (February 2026)

| Module | Feature Tests | Integration Tests | Coverage |
|--------|---------------|-------------------|----------|
| Authentication | ✅ Complete | ✅ Complete | 95% |
| Material Management | ✅ Complete | ✅ Complete | 90% |
| Pattern/Recipe | ✅ Complete | ✅ Complete | 90% |
| Preparation Orders | ✅ Complete | ✅ Complete | 85% |
| Production Orders | ✅ Complete | ✅ Complete | 85% |
| Inventory | ✅ Complete | ✅ Complete | 90% |
| Sales Orders | ✅ Complete | ✅ Complete | 90% |
| Reports | ✅ Complete | ✅ Complete | 85% |
| AI Assistant | ✅ Partial | ✅ Basic | 70% |
| Telegram | ✅ Partial | ✅ Basic | 70% |
| Admin Panel | ✅ Complete | ⏳ Pending | 75% |
| Settings | ✅ Complete | ✅ Complete | 85% |

**Overall Test Coverage**: ~85%

## 🔍 Testing Best Practices

### 1. Test Naming Convention
```php
// Use descriptive test names
it('completes full garment workflow from registration to sale')
it('prevents negative stock adjustments')
it('enforces subscription check on dashboard access')
```

### 2. Test Structure (AAA Pattern)
```php
it('creates material with receipt', function () {
    // Arrange
    $material = Material::factory()->create();
    
    // Act
    $response = $this->actingAs($user)
        ->post(route('materials.store'), $data);
    
    // Assert
    expect($material->stock_quantity)->toBe(100);
    $response->assertRedirect();
});
```

### 3. Use Factories
```php
// Use factories instead of manual creation
$tenant = Tenant::factory()->create();
$user = User::factory()->create(['tenant_id' => $tenant->id]);
$material = Material::factory()->count(5)->create();
```

### 4. Test Data Isolation
```php
// Each test should be independent
beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
});
```

### 5. Meaningful Assertions
```php
// Be specific with assertions
expect($order->status)->toBe('COMPLETED');
expect($material->stock_quantity)->toBe(80.0); // Not just truthy
expect($items)->toHaveCount(10);
```

## 🐛 Debugging Failed Tests

```bash
# Run single failing test
php artisan test --filter "it tracks material from receipt"

# Show full error stack trace
php artisan test --filter "failing test" --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Debug with dd() or dump()
# Add dd($variable) in test code
```

## 📦 Continuous Integration

### GitHub Actions (Example)
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test --parallel
```

## 🎓 Learning Resources

- [Pest Documentation](https://pestphp.com/)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Pest Plugin Laravel](https://pestphp.com/docs/plugins/laravel)
- [Browser Testing with Pest](https://pestphp.com/docs/browser-testing)

## 📌 TODO: Future Test Coverage

- [ ] Browser tests untuk complete UI flows
- [ ] More edge cases untuk validation
- [ ] Performance tests
- [ ] Load testing
- [ ] API endpoint testing
- [ ] Security testing (XSS, CSRF, SQL Injection)
- [ ] Mobile responsiveness tests

---

**Note**: Tests adalah living documentation. Selalu update tests saat menambah atau mengubah fitur.
