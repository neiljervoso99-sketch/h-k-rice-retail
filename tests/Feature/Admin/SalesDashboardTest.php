<?php

namespace Tests\Feature\Admin;

use App\Models\CashRegisterMovement;
use App\Models\CashRegisterShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_cash_drawer_and_access_combined_sales_dashboard(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee('New Manual Transaction')
            ->assertSee('Manual Transactions');

        $this->actingAs($staff)
            ->post(route('admin.sales.shifts.open'), ['opening_cash' => 250])
            ->assertRedirect(route('admin.sales.index'));

        $this->assertDatabaseHas('cash_register_shifts', [
            'opened_by' => $staff->id,
            'opening_cash' => 250,
            'status' => 'open',
        ]);

        $this->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee('Sales (Benta)')
            ->assertSee('Cash Drawer')
            ->assertSee('Expected Cash')
            ->assertSee("Today's Sales", false)
            ->assertSee('New Manual Transaction')
            ->assertSee('Manual Transactions')
            ->assertSee('Invoices &amp; Receipts', false)
            ->assertSee('SHIFT OPEN');
    }

    public function test_shift_records_cash_in_out_and_expected_closing_variance(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $this->actingAs($admin)
            ->post(route('admin.sales.shifts.open'), ['opening_cash' => 100])
            ->assertRedirect(route('admin.sales.index'));

        $shift = CashRegisterShift::query()->sole();

        $this->post(route('admin.sales.shifts.cash-movements.store'), [
            'movement_type' => 'cash_in',
            'amount' => 50,
            'note' => 'Float top up',
        ])->assertRedirect(route('admin.sales.index'));

        $this->post(route('admin.sales.shifts.cash-movements.store'), [
            'movement_type' => 'cash_out',
            'amount' => 20,
            'note' => 'Petty cash',
        ])->assertRedirect(route('admin.sales.index'));

        $this->assertSame(2, CashRegisterMovement::query()->count());

        $this->post(route('admin.sales.shifts.close'), [
            'closing_cash' => 135,
        ])->assertRedirect(route('admin.sales.index'));

        $this->assertDatabaseHas('cash_register_shifts', [
            'id' => $shift->id,
            'status' => 'closed',
            'opening_cash' => 100,
            'closing_cash' => 135,
            'expected_cash' => 130,
            'cash_variance' => 5,
            'closed_by' => $admin->id,
        ]);
    }

    public function test_cash_out_cannot_exceed_expected_drawer_balance(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)
            ->post(route('admin.sales.shifts.open'), ['opening_cash' => 50]);

        $this->post(route('admin.sales.shifts.cash-movements.store'), [
            'movement_type' => 'cash_out',
            'amount' => 50.01,
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, CashRegisterMovement::query()->count());
    }
}
