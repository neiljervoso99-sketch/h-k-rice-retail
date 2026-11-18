<?php

namespace Tests\Feature\Admin;

use App\Models\LoginHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use App\Support\AdminModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_blue_glass_admin_theme_is_used_outside_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User Roles &amp; Permissions', false)
            ->assertSee('.admin-main .page-heading', false)
            ->assertSee('backdrop-filter: blur(16px)', false)
            ->assertSee('rgba(56, 189, 248', false)
            ->assertSee('@media (max-width: 700px)', false)
            ->assertSee('prefers-reduced-motion', false)
            ->assertSee('id="sidebarToggle"', false)
            ->assertSee('aria-controls="adminSidebar"', false)
            ->assertSee('sidebar-collapsed', false);
    }

    public function test_staff_can_access_the_dashboard_and_own_profile_only(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Overview')
            ->assertSee('Orders this month')
            ->assertDontSee('Revenue this month')
            ->assertDontSee('Sales Trend')
            ->assertDontSee('Recent Orders')
            ->assertDontSee('₱')
            ->assertDontSee('Manage Orders')
            ->assertSee('Sales (Benta)')
            ->assertDontSee('Users &amp; Roles')
            ->assertDontSee('admin.orders.show', false);

        $this->get(route('admin.manual-transactions.index'))
            ->assertOk()
            ->assertSee('Transaction History');

        $this->get(route('admin.manual-transactions.create'))
            ->assertOk()
            ->assertSee('Open your cash drawer shift');

        $this->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('My Profile');

        $this->get(route('admin.products.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('customer.profile.edit'))->assertForbidden();
        $this->get(route('checkout.create'))->assertForbidden();
    }

    public function test_manual_transaction_form_and_product_catalog_remain_visible_without_an_open_shift(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::query()->create([
            'name' => 'POS Test Rice',
            'inventory_type' => 'generic',
            'stock_unit' => 'piece',
            'base_unit' => 'piece',
            'stock_base_quantity' => 20,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);
        ProductSellingUnit::query()->create([
            'product_id' => $product->id,
            'unit_name' => 'piece',
            'conversion_factor' => 1,
            'selling_price' => 10,
            'cost_price' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.manual-transactions.create'))
            ->assertOk()
            ->assertSee('Open your cash drawer shift')
            ->assertSee('Choose products')
            ->assertSee('POS Test Rice')
            ->assertSee('Shopping cart')
            ->assertSee('Open Cash Drawer to Save')
            ->assertSee('disabled', false);
    }

    public function test_admin_can_create_staff_and_change_user_roles(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User Roles & Permissions')
            ->assertSee('Create an Account')
            ->assertSee('name="role"', false)
            ->assertSee('Choose a role');

        $this->post(route('admin.users.store'), [
            'name' => 'Store Staff',
            'email' => 'staff@example.test',
            'password' => '246810',
            'password_confirmation' => '246810',
            'role' => 'staff',
        ])->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'staff@example.test')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertFalse($staff->is_admin);
        $this->assertTrue(password_verify('246810', $staff->password));

        $this->patch(route('admin.users.role.update', $customer), [
            'role' => 'rider',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('rider', $customer->fresh()->role);
    }

    public function test_admin_can_search_edit_and_delete_unreferenced_accounts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $staff = User::factory()->create([
            'name' => 'Searchable Staff',
            'email' => 'searchable-staff@example.test',
            'role' => 'staff',
        ]);
        LoginHistory::query()->create([
            'user_id' => $staff->id,
            'logged_in_at' => now(),
            'logged_out_at' => now(),
        ]);
        $customer = User::factory()->create([
            'email' => 'unrelated-customer@example.test',
            'role' => 'customer',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Searchable', 'role' => 'staff']))
            ->assertOk()
            ->assertSee('searchable-staff@example.test')
            ->assertSee('Recent sign-in')
            ->assertDontSee('unrelated-customer@example.test');

        $this->patch(route('admin.users.update', $staff), [
            'name' => 'Updated Staff',
            'email' => 'updated-staff@example.test',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('Updated Staff', $staff->fresh()->name);
        $this->assertSame('updated-staff@example.test', $staff->fresh()->email);

        $this->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_delete_an_account_with_order_history(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        Order::query()->create([
            'order_number' => 'USER-ORDER-HISTORY-001',
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '09123456789',
            'status' => 'completed',
            'total' => 100,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_admin_can_assign_staff_modules_and_unselected_modules_are_forbidden(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patch(route('admin.users.permissions.update', $staff), [
                'permissions' => ['inventory'],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame(['inventory'], $staff->fresh()->staff_permissions);
        $staff = $staff->fresh();
        $this->assertTrue($staff->hasStaffPermission('inventory'));
        $this->assertTrue(AdminModuleAccess::allows($staff, 'admin.inventory.index'));

        $this->actingAs($staff)
            ->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee(route('admin.inventory.index'), false)
            ->assertDontSee(route('admin.sales.index'), false)
            ->assertDontSee(route('admin.manual-transactions.index'), false);

        $this->get(route('admin.sales.index'))->assertForbidden();
        $this->get(route('admin.manual-transactions.index'))->assertForbidden();
        $this->get(route('admin.debts.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_can_choose_any_supported_role_when_creating_an_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        foreach (['admin', 'staff', 'customer', 'rider'] as $role) {
            $email = $role.'-choice@example.test';

            $this->actingAs($admin)
                ->post(route('admin.users.store'), [
                    'name' => ucfirst($role).' Account',
                    'email' => $email,
                    'password' => '246810',
                    'password_confirmation' => '246810',
                    'role' => $role,
                ])
                ->assertRedirect(route('admin.users.index'));

            $createdUser = User::query()->where('email', $email)->firstOrFail();
            $this->assertSame($role, $createdUser->role);
            $this->assertSame($role === 'admin', $createdUser->is_admin);
        }
    }

    public function test_admin_must_choose_a_supported_role_when_creating_an_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Invalid Role Account',
                'email' => 'invalid-role@example.test',
                'password' => '246810',
                'password_confirmation' => '246810',
                'role' => 'unknown',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-role@example.test']);
    }

    public function test_staff_account_password_must_be_exactly_six_digits(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Invalid PIN Staff',
                'email' => 'invalid-pin@example.test',
                'password' => 'abc123',
                'password_confirmation' => 'abc123',
                'role' => 'staff',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-pin@example.test']);
    }

    public function test_admin_roles_cannot_be_self_removed_or_leave_no_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $admin), [
                'role' => 'staff',
            ])
            ->assertSessionHasErrors('role');

        $this->patch(route('admin.users.role.update', $otherAdmin), [
            'role' => 'customer',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertSame('customer', $otherAdmin->fresh()->role);

        $this->patch(route('admin.users.role.update', $admin), [
            'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_non_admins_cannot_manage_roles(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->post(route('admin.users.store'), [
                'name' => 'Unauthorized',
                'email' => 'unauthorized@example.test',
                'password' => '246810',
                'password_confirmation' => '246810',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.test']);
    }

    public function test_public_registration_cannot_choose_an_elevated_role(): void
    {
        $this->post(route('register.submit'), [
            'name' => 'Self Registered',
            'email_or_phone' => 'self-registered@example.test',
            'password' => '246810',
            'password_confirmation' => '246810',
            'role' => 'admin',
            'is_admin' => true,
        ])->assertRedirect(route('home'));

        $user = User::where('email', 'self-registered@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertFalse($user->is_admin);
    }

    public function test_customer_registration_requires_exactly_six_digits(): void
    {
        $this->from(route('register'))
            ->post(route('register.submit'), [
                'name' => 'Customer With Invalid PIN',
                'email_or_phone' => 'bad-pin@example.test',
                'password' => '12345',
                'password_confirmation' => '12345',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'bad-pin@example.test']);

        $this->post(route('register.submit'), [
            'name' => 'Customer With PIN',
            'email_or_phone' => 'six-digit@example.test',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', ['email' => 'six-digit@example.test']);
    }
}
