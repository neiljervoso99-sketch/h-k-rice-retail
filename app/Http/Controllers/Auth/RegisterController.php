<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route(
                Auth::user()->isAdmin() || Auth::user()->isStaff()
                    ? 'admin.dashboard'
                    : (Auth::user()->isRider() ? 'rider.dashboard' : 'home')
            );
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $identifier = $request->input('email_or_phone');
        if (is_string($identifier)) {
            $identifier = trim($identifier);
            $request->merge(['email_or_phone' => $identifier]);
        }

        $isEmail = is_string($identifier)
            && filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email_or_phone' => [
                'required',
                'string',
                $isEmail ? 'email' : 'regex:/^\+?[0-9][0-9\s()-]{6,29}$/',
                $isEmail ? 'max:255' : 'max:30',
                Rule::unique('users', $isEmail ? 'email' : 'phone'),
            ],
            'password' => [
                'required',
                'confirmed',
                'regex:/^[0-9]{6}$/',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $isEmail ? $validated['email_or_phone'] : null,
            'phone' => $isEmail ? null : $validated['email_or_phone'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Your customer account has been created successfully.');
    }
}
