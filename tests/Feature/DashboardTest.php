<?php

use App\Models\User;
use App\Models\Tenant;

test('guests cannot access dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated users can view dashboard', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('stats')
        ->has('stats.total_materials')
        ->has('stats.total_inventory')
        ->has('stats.total_sales_month')
    );
});

test('dashboard shows correct KPI stats', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('stats.total_materials', 0)
        ->where('stats.total_inventory', 0)
        ->where('stats.total_sales_month', 0)
    );
});

test('dashboard provides sales trend data', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('salesTrend')
        ->has('topProducts')
        ->has('recentActivities')
    );
});
