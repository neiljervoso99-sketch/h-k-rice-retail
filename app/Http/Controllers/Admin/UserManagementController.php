<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Staff',
        'customer' => 'Customer',
        'rider' => 'Rider',
    ];

    private const STAFF_MODULES = [
        'sales' => 'Sales (Benta)',
        'manual_transactions' => 'Manual Transactions',
        'debts' => 'Utang',
        'inventory' => 'Inventory',
        'purchase_orders' => 'Purchase Orders',
        'suppliers' => 'Suppliers',
        'customer_orders' => 'Customer Orders',
        'products' => 'Products, Reviews & Bundles',
        'profit_pricing' => 'Profit & Pricing',
        'expenses' => 'Expenses',
        'reports' => 'Reports',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(array_keys(self::ROLES))],
        ]);
        $users = User::query()
            ->withMax('loginHistories', 'logged_in_at')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($validated['role'] ?? null, function ($query, $role) {
                if ($role === 'admin') {
                    $query->where(fn ($query) => $query
                        ->where('role', 'admin')
                        ->orWhere('is_admin', true));
                } else {
                    $query->where('role', $role)->where('is_admin', false);
                }
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'roleLabels' => self::ROLES,
            'staffModules' => self::STAFF_MODULES,
            'filters' => $validated,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'regex:/^[0-9]{6}$/'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:'.implode(',', array_keys(self::STAFF_MODULES))],
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->role = $validated['role'];
        $user->is_admin = $validated['role'] === 'admin';
        $user->staff_permissions = $validated['role'] === 'staff'
            ? array_values(array_unique($validated['permissions'] ?? ['sales', 'manual_transactions']))
            : null;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', self::ROLES[$validated['role']].' account created successfully.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
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

        $wasStaff = $user->isStaff();
        $user->role = $newRole;
        $user->is_admin = $newRole === 'admin';
        $user->staff_permissions = $newRole === 'staff'
            ? ($wasStaff
                ? ($user->staff_permissions ?? ['sales', 'manual_transactions'])
                : ['sales', 'manual_transactions'])
            : null;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Account details updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages([
                'account' => 'You cannot delete your own account.',
            ]);
        }

        DB::transaction(function () use ($user) {
            if ($user->isAdmin()) {
                $admins = User::query()
                    ->where(fn ($query) => $query
                        ->where('role', 'admin')
                        ->orWhere('is_admin', true))
                    ->lockForUpdate()
                    ->get(['id']);

                if ($admins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'account' => 'The last administrator account cannot be deleted.',
                    ]);
                }
            }

            $hasProtectedRecords = $user->orders()->exists()
                || $user->cashRegisterShifts()->exists()
                || DB::table('cash_register_movements')
                    ->where('created_by', $user->id)
                    ->exists();

            if ($hasProtectedRecords) {
                throw ValidationException::withMessages([
                    'account' => 'This account has order or cash-register records and cannot be deleted.',
                ]);
            }

            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Account deleted successfully.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        if (! $user->isStaff()) {
            throw ValidationException::withMessages([
                'permissions' => 'Module access can only be assigned to staff accounts.',
            ]);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', 'in:'.implode(',', array_keys(self::STAFF_MODULES))],
        ]);

        $user->staff_permissions = array_values(array_unique($validated['permissions'] ?? []));
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Staff module access updated.');
    }
}
