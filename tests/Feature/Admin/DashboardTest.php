<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Order;
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
            ->assertSee('Dashboard V28')
            ->assertSee('Team &amp; Access', false)
            ->assertSee('Manage team')
            ->assertSee(route('admin.users.index'), false)
            ->assertSee('Total Revenue')
            ->assertSee('Total Sales')
            ->assertSee('Growth Percentage')
            ->assertSee('Monthly Performance')
            ->assertSee('Sales Trend')
            ->assertSee('Sales by Category')
            ->assertSee('Sales Count')
            ->assertSee('Top Products')
            ->assertSee('Recent Orders')
            ->assertSee('Low Stock Alerts')
            ->assertSee('salesTrendsChart')
            ->assertSee('salesCountChart')
            ->assertSee('analytics-period')
            ->assertSee('team-member-grid')
            ->assertSee('Drag to move.')
            ->assertSee('assistant-position-'.$admin->id, false)
            ->assertSee('data-assistant-open', false)
            ->assertSee("addEventListener('pointermove'", false)
            ->assertSee('accentPickerToggle')
            ->assertSee('accentPickerPanel')
            ->assertSee('linear-gradient(145deg, rgba(255, 255, 255, .34)', false)
            ->assertSee('backdrop-filter: blur(18px)', false)
            ->assertSee('background-attachment: fixed', false)
            ->assertSee('admin-dashboard-page', false)
            ->assertSee('dashboard-light-drift', false)
            ->assertSee('dashboard-rise-in', false)
            ->assertSee('dashboard-card-float', false)
            ->assertSee('--dashboard-tilt-x', false)
            ->assertSee('const defaultAdminTheme = "light";', false)
            ->assertSee('rgba(235, 247, 253, .78)', false)
            ->assertSee('animation: { duration: reduceMotion ? 0 : 650', false)
            ->assertSee('@media (prefers-reduced-motion: reduce)', false)
            ->assertSee('data-accent="amber"', false)
            ->assertSee('data-theme-choice="dark"', false)
            ->assertSee('data-theme-choice="light"', false)
            ->assertSee('themeModeLabel')
            ->assertSee('data-theme="dark"', false);
    }

    public function test_dashboard_period_filter_uses_completed_order_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        Order::query()->create([
            'order_number' => 'DASHBOARD-COMPLETED-001',
            'user_id' => $admin->id,
            'recipient_name' => $admin->name,
            'phone' => '09123456789',
            'status' => 'completed',
            'total' => 100,
            'completed_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 'month']))
            ->assertOk()
            ->assertSee('Total Revenue')
            ->assertSee('₱100.00')
            ->assertSee('Month completed orders')
            ->assertSee('value="month" selected', false);
    }

    public function test_dashboard_rejects_an_unsupported_analytics_period(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 'decade']))
            ->assertSessionHasErrors('period');
    }
}
