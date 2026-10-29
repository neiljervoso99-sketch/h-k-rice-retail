<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_analytics_and_recent_activity(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Overview')
            ->assertSee('Revenue this month')
            ->assertSee('Sales Trend')
            ->assertSee('Sales by Category')
            ->assertSee('Top Products')
            ->assertSee('Recent Orders')
            ->assertSee('Low Stock Alerts')
            ->assertSee('salesTrendsChart')
            ->assertSee('themeToggle')
            ->assertSee('data-theme="dark"', false);
    }
}
