<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_staff_can_view_their_profile_and_activity(): void
    {
        foreach (['admin', 'staff'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'is_admin' => $role === 'admin',
            ]);

            $this->actingAs($user)
                ->get(route('admin.profile.edit'))
                ->assertOk()
                ->assertSee('My Profile')
                ->assertSee('Security')
                ->assertSee('Login History')
                ->assertSee('Activity Log')
                ->assertSee('role="tablist"', false);
        }
    }

    public function test_profile_changes_are_recorded_in_the_activity_trail(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'name' => 'Old Name',
            'phone' => '111111',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.profile.update'), [
                'name' => 'New Name',
                'email' => $user->email,
                'phone' => '222222',
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $activity = ActivityLog::query()->where('event', 'profile.updated')->firstOrFail();
        $this->assertSame('Old Name', $activity->changes['name']['from']);
        $this->assertSame('New Name', $activity->changes['name']['to']);
        $this->assertSame('111111', $activity->changes['phone']['from']);
        $this->assertSame('222222', $activity->changes['phone']['to']);
    }

    public function test_password_change_requires_current_password_and_is_audited(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)
            ->put(route('admin.profile.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => '123456',
                'password_confirmation' => '123456',
            ])
            ->assertSessionHasErrors('current_password');

        $this->put(route('admin.profile.password.update'), [
            'current_password' => 'current-password-123',
            'password' => '12345',
            'password_confirmation' => '12345',
        ])->assertSessionHasErrors('password');

        $this->put(route('admin.profile.password.update'), [
            'current_password' => 'current-password-123',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('123456', $user->fresh()->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event' => 'password.updated',
            'changes' => null,
        ]);
    }

    public function test_login_and_logout_are_saved_to_login_history_and_activity(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'email' => 'admin@example.test',
            'password' => 'current-password-123',
        ]);

        $this->post(route('login.submit'), [
            'email' => 'admin@example.test',
            'password' => 'current-password-123',
        ])->assertRedirect(route('admin.dashboard'));

        $history = LoginHistory::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($history->logged_in_at);
        $this->assertNull($history->logged_out_at);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event' => 'auth.login',
        ]);

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertNotNull($history->fresh()->logged_out_at);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event' => 'auth.logout',
        ]);
    }

    public function test_admin_mutations_are_audited_without_sensitive_input(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role.update', $staff), [
                'role' => 'rider',
                'password' => 'must-not-be-recorded',
                'email' => 'private@example.test',
            ])
            ->assertRedirect(route('admin.users.index'));

        $activity = ActivityLog::query()
            ->where('user_id', $admin->id)
            ->where('event', 'admin.users.role.update')
            ->firstOrFail();

        $this->assertSame($staff->id, $activity->changes['route_parameters']['user']);
        $this->assertSame('rider', $activity->changes['input']['role']);
        $this->assertArrayNotHasKey('password', $activity->changes['input']);
        $this->assertArrayNotHasKey('email', $activity->changes['input']);
        $this->assertSame(302, $activity->changes['status_code']);
    }

    public function test_admin_page_views_and_failed_mutations_are_audited(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->patch(route('admin.users.role.update', $staff), [
            'role' => 'invalid-role',
        ])->assertSessionHasErrors('role');

        $this->assertSame(200, ActivityLog::query()
            ->where('user_id', $admin->id)
            ->where('event', 'admin.users.index')
            ->firstOrFail()
            ->changes['status_code']);

        $this->assertSame(302, ActivityLog::query()
            ->where('user_id', $admin->id)
            ->where('event', 'admin.users.role.update')
            ->firstOrFail()
            ->changes['status_code']);

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee($staff->name);
    }
}