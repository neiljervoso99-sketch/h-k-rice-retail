<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLES = ['admin', 'staff', 'customer', 'rider'];

    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->role = 'staff';
        $user->is_admin = false;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', self::ROLES)],
        ]);
        $newRole = $validated['role'];
        $currentUser = $request->user();

        if ($user->is($currentUser) && $newRole !== 'admin') {
            throw ValidationException::withMessages([
                'role' => 'You cannot remove your own administrator access.',
            ]);
        }

        if ($user->isAdmin() && $newRole !== 'admin') {
            $adminCount = User::query()
                ->where(fn ($query) => $query
                    ->where('role', 'admin')
                    ->orWhere('is_admin', true))
                ->count();

            if ($adminCount <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'The last administrator account cannot be changed.',
                ]);
            }
        }

        $user->role = $newRole;
        $user->is_admin = $newRole === 'admin';
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }
}
