<?php

use App\Models\Material;
use App\Models\Pattern;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create tenant and user for browser tests
    $this->tenant = Tenant::factory()->create([
        'name' => 'Test Konveksi',
        'slug' => 'test-konveksi',
        'business_category' => 'garment',
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@konveksi.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    // Create some test materials
    $this->materials = Material::factory()->count(3)->create([
        'tenant_id' => $this->tenant->id,
        'is_active' => true,
    ]);
});

it('can login and see dashboard', function () {
    $page = visit('/login');

    $page->assertSee('Login')
        ->fill('email', 'test@konveksi.com')
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertSee('Fabriku')
        ->assertNoJavascriptErrors();
});

it('can navigate through all menu items', function () {
    actingAs($this->user);

    $page = visit('/dashboard');

    $page->assertSee('Dashboard')
        ->assertSee('Bahan Baku')
        ->assertSee('Pattern')
        ->assertSee('Cutting Order')
        ->assertNoJavascriptErrors();

    // Test navigation to Materials
    $page->click('a[href="/materials"]')
        ->assertPathIs('/materials')
        ->assertSee('Data Bahan Baku');

    // Test navigation to Patterns
    $page->click('a[href="/patterns"]')
        ->assertPathIs('/patterns')
        ->assertSee('Data Pattern Produk');

    // Test navigation to Preparation Orders
    $page->click('a[href="/preparation-orders"]')
        ->assertPathIs('/preparation-orders')
        ->assertSee('Data Preparation Order');

    // Test navigation back to Dashboard
    $page->click('a[href="/dashboard"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Selamat Datang');
});

it('can create a new material', function () {
    actingAs($this->user);

    $page = visit('/materials');

    $page->assertSee('Data Bahan Baku')
        ->click('a[href="/materials/create"]')
        ->assertPathIs('/materials/create')
        ->assertSee('Tambah Bahan Baku')
        ->fill('code', 'TEST-001')
        ->fill('name', 'Test Material Browser')
        ->select('type', 'kain')
        ->fill('description', 'Material untuk browser test')
        ->select('unit', 'meter')
        ->fill('standard_price', '50000')
        ->fill('reorder_point', '10')
        ->click('button[type="submit"]')
        ->assertPathIs('/materials')
        ->assertSee('TEST-001')
        ->assertSee('Test Material Browser')
        ->assertNoJavascriptErrors();
});

it('can search and filter materials', function () {
    actingAs($this->user);

    // Create a specific material for search
    Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'SEARCH-TEST',
        'name' => 'Searchable Material',
        'type' => 'kain',
        'is_active' => true,
    ]);

    $page = visit('/materials');

    // Test search functionality
    $page->fill('input[name="search"]', 'SEARCH-TEST')
        ->press('Enter')
        ->wait(1000) // Wait for search results
        ->assertSee('SEARCH-TEST')
        ->assertSee('Searchable Material')
        ->assertNoJavascriptErrors();
});

it('can create a pattern with BOM', function () {
    actingAs($this->user);

    $page = visit('/patterns');

    $page->assertSee('Data Pattern Produk')
        ->click('a[href="/patterns/create"]')
        ->assertPathIs('/patterns/create')
        ->assertSee('Buat Pattern Baru')
        ->assertDontSee('dark') // Ensure no dark theme
        ->fill('code', 'PTN-TEST-001')
        ->fill('name', 'Pattern Browser Test')
        ->select('product_type', 'mukena')
        ->select('size', 'M')
        ->fill('description', 'Test pattern dari browser test')
        ->assertNoJavascriptErrors();

    // Add material to BOM
    $page->click('button:has-text("+ Tambah Bahan")')
        ->wait(500);

    // Select first material in BOM builder
    $firstMaterial = $this->materials->first();
    $page->select('select[id^="material_id"]', (string) $firstMaterial->id)
        ->fill('input[placeholder="Contoh: 2.5"]', '2')
        ->fill('textarea[placeholder="Catatan tambahan"]', 'Material utama')
        ->assertNoJavascriptErrors();

    // Submit the form
    $page->click('button[type="submit"]:has-text("Simpan")')
        ->wait(2000) // Wait for redirect
        ->assertPathIs('/patterns')
        ->assertSee('PTN-TEST-001')
        ->assertSee('Pattern Browser Test')
        ->assertNoJavascriptErrors();
});

it('can create a cutting order', function () {
    actingAs($this->user);

    // Create a pattern first
    $pattern = Pattern::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'PTN-CUT-001',
        'name' => 'Pattern for Cutting',
        'product_type' => 'mukena',
        'size' => 'L',
        'is_active' => true,
    ]);

    // Attach materials to pattern (BOM)
    $material = $this->materials->first();
    $pattern->materials()->attach($material->id, [
        'quantity_needed' => 2.5,
        'notes' => 'Main fabric',
    ]);

    $page = visit('/preparation-orders');

    $page->assertSee('Data Preparation Order')
        ->click('a[href="/preparation-orders/create"]')
        ->assertPathIs('/preparation-orders/create')
        ->assertSee('Buat Cutting Order Baru')
        ->assertDontSee('dark') // Ensure no dark theme
        ->assertNoJavascriptErrors();

    // Fill cutting order form
    $page->select('pattern_id', (string) $pattern->id)
        ->wait(500) // Wait for material requirements to load
        ->assertSee('Kebutuhan Bahan') // Should show material requirements
        ->fill('planned_quantity', '10')
        ->select('priority', 'normal')
        ->fill('notes', 'Test cutting order from browser')
        ->assertNoJavascriptErrors();

    // Submit the form
    $page->click('button[type="submit"]:has-text("Simpan")')
        ->wait(2000) // Wait for redirect
        ->assertPathIs('/preparation-orders')
        ->assertSee('Pattern for Cutting')
        ->assertNoJavascriptErrors();
});

it('displays material stock sufficiency warnings in cutting order form', function () {
    actingAs($this->user);

    // Create pattern with material
    $pattern = Pattern::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'PTN-STOCK-001',
        'name' => 'Pattern Stock Test',
        'product_type' => 'daster',
        'is_active' => true,
    ]);

    // Create material with low stock
    $lowStockMaterial = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'LOW-STOCK',
        'name' => 'Low Stock Material',
        'current_stock' => 5, // Low stock
        'is_active' => true,
    ]);

    // Attach material that requires more than available
    $pattern->materials()->attach($lowStockMaterial->id, [
        'quantity_needed' => 2, // Needs 2 per unit
        'notes' => 'Main fabric',
    ]);

    $page = visit('/preparation-orders/create');

    $page->select('pattern_id', (string) $pattern->id)
        ->wait(500)
        ->fill('planned_quantity', '10') // 10 units * 2 = 20 needed, but only 5 available
        ->wait(500)
        ->assertSee('Kebutuhan Bahan')
        ->assertSee('Low Stock Material')
        ->assertSee('Stok tidak cukup') // Should show insufficient stock warning
        ->assertNoJavascriptErrors();
});

it('can logout successfully', function () {
    actingAs($this->user);

    $page = visit('/dashboard');

    $page->assertSee('Dashboard')
        ->click('button:has-text("Logout")')
        ->wait(1000)
        ->assertPathIs('/login')
        ->assertSee('Login')
        ->assertNoJavascriptErrors();
});

it('prevents access to protected routes when not logged in', function () {
    $page = visit('/dashboard');

    $page->assertPathIs('/login')
        ->assertSee('Login')
        ->assertNoJavascriptErrors();
});

it('shows validation errors for invalid material data', function () {
    actingAs($this->user);

    $page = visit('/materials/create');

    // Submit empty form
    $page->click('button[type="submit"]')
        ->wait(500)
        ->assertSee('Tambah Bahan Baku') // Should stay on form
        ->assertNoJavascriptErrors();
});

it('can view pattern details with BOM cost calculation', function () {
    actingAs($this->user);

    // Create pattern with materials
    $pattern = Pattern::factory()->create([
        'tenant_id' => $this->tenant->id,
        'code' => 'PTN-VIEW-001',
        'name' => 'Pattern View Test',
        'product_type' => 'mukena',
        'is_active' => true,
    ]);

    // Attach multiple materials with known prices
    $material1 = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Fabric A',
        'standard_price' => 50000,
        'is_active' => true,
    ]);

    $material2 = Material::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Fabric B',
        'standard_price' => 30000,
        'is_active' => true,
    ]);

    $pattern->materials()->attach([
        $material1->id => ['quantity_needed' => 2, 'notes' => 'Main'],
        $material2->id => ['quantity_needed' => 1, 'notes' => 'Lining'],
    ]);

    $page = visit('/patterns/'.$pattern->id.'/edit');

    $page->assertSee('Edit Pattern')
        ->assertSee('PTN-VIEW-001')
        ->assertSee('Pattern View Test')
        ->assertSee('Fabric A')
        ->assertSee('Fabric B')
        ->assertSee('Total Estimasi Biaya') // Should show cost calculation
        ->assertNoJavascriptErrors();
});

it('maintains tenant isolation - cannot see other tenant data', function () {
    // Create another tenant with materials
    $otherTenant = Tenant::factory()->create([
        'name' => 'Other Konveksi',
        'slug' => 'other-konveksi',
        'business_category' => 'garment',
    ]);

    Material::factory()->create([
        'tenant_id' => $otherTenant->id,
        'code' => 'OTHER-TENANT-MAT',
        'name' => 'Other Tenant Material',
        'is_active' => true,
    ]);

    actingAs($this->user); // Login as first tenant user

    $page = visit('/materials');

    $page->assertSee('Data Bahan Baku')
        ->assertDontSee('OTHER-TENANT-MAT') // Should NOT see other tenant's material
        ->assertDontSee('Other Tenant Material')
        ->assertNoJavascriptErrors();
});
