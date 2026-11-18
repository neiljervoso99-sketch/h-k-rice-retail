<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_shows_confirmation_and_dashboard_button(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer Name',
            'email' => 'customer@example.test',
        ]);

        $this->actingAs($customer)
            ->patch(route('customer.profile.update'), [
                'name' => 'Updated Customer',
                'email' => 'customer@example.test',
                'phone' => '09123456789',
            ])
            ->assertRedirect(route('customer.profile.edit'))
            ->assertSessionHas('success');

        $this->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertSee('Your profile updated')
            ->assertSee('Go Back to Dashboard')
            ->assertSee('role="dialog"', false)
            ->assertSee('left: 0;', false);
    }
}
