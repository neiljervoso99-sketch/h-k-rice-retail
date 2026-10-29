<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee('Manual Transactions')
            ->assertDontSee('Users &amp; Roles')
            ->assertDontSee('admin.orders.show', false);

        $this->get(route('admin.manual-transactions.index'))
            ->assertOk()
            ->assertSee('Transaction History');

        $this->get(route('admin.manual-transactions.create'))
            ->assertOk()
            ->assertSee('New Manual Transaction');

        $this->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('My Profile');

        $this->get(route('admin.products.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('customer.profile.edit'))->assertForbidden();
        $this->get(route('checkout.create'))->assertForbidden();
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
            ->assertSee('Create a Staff Account');

        $this->post(route('admin.users.store'), [
            'name' => 'Store Staff',
            'email' => 'staff@example.test',
            'password' => '246810',
            'password_confirmation' => '246810',
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
            'email' => 'self-registered@example.test',
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
                'email' => 'bad-pin@example.test',
                'password' => '12345',
                'password_confirmation' => '12345',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'bad-pin@example.test']);

        $this->post(route('register.submit'), [
            'name' => 'Customer With PIN',
            'email' => 'six-digit@example.test',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', ['email' => 'six-digit@example.test']);
    }
}
