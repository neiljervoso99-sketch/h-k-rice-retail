<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_sign_in_with_phone_number(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '09123456789',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.submit'), [
            'email' => '09123456789',
            'password' => 'correct-password',
        ])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_login_page_identifies_email_and_phone_number_as_sign_in_options(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Email or phone number')
            ->assertSee('type="text"', false)
            ->assertSee('Sign in with your email address or phone number.');
    }

    public function test_customer_can_register_with_phone_number_and_use_it_to_sign_in(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Email or phone number')
            ->assertSee('name="email_or_phone"', false);

        $this->post(route('register.submit'), [
            'name' => 'Phone Customer',
            'email_or_phone' => '09123456789',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertRedirect(route('home'));

        $customer = User::query()->where('phone', '09123456789')->firstOrFail();
        $this->assertNull($customer->email);
        $this->assertAuthenticatedAs($customer);

        $this->post(route('logout'));

        $this->post(route('login.submit'), [
            'email' => '09123456789',
            'password' => '123456',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_can_register_with_email_address(): void
    {
        $this->post(route('register.submit'), [
            'name' => 'Email Customer',
            'email_or_phone' => 'email-customer@example.test',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'email-customer@example.test',
            'phone' => null,
        ]);
    }
}
