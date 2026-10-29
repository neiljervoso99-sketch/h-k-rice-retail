<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetStoreOwner extends Command
{
    protected $signature = 'store:owner';

    protected $description = 'Safely create or promote the store owner account';

    public function handle(): int
    {
        $email = strtolower(trim($this->ask('Owner email address')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please enter a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->is_admin) {
                $this->info('This account is already an administrator.');

                return self::SUCCESS;
            }

            $this->warn('An account with this email already exists.');
            $this->line('Name: '.$user->name);

            if (!$this->confirm(
                'Promote this existing account to store owner? Only continue if this is your account.',
                false
            )) {
                $this->info('No changes made.');

                return self::SUCCESS;
            }

            $user->is_admin = true;
            $user->save();

            $this->info('Existing account promoted to store owner.');
            $this->line('Sign in using its existing password.');

            return self::SUCCESS;
        }

        $name = trim($this->ask('Owner name'));

        if ($name === '') {
            $this->error('Name cannot be empty.');

            return self::FAILURE;
        }

        $password = $this->secret('Create a 6-digit password');
        $confirmation = $this->secret('Confirm password');

        if (
            !is_string($password) ||
            !preg_match('/^[0-9]{6}$/', $password) ||
            $password !== $confirmation
        ) {
            $this->error(
                'Passwords must match and contain exactly 6 digits. No account created.'
            );

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->is_admin = true;
        $user->save();

        $this->info('Store owner account created successfully.');
        $this->line('You can now sign in at /login.');

        return self::SUCCESS;
    }
}