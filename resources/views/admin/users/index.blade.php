@extends('admin.layouts.app')

@section('title', 'User Roles & Permissions')

@section('content')
<div class="page-heading">
    <h1>User Roles &amp; Permissions</h1>
    <p>Manage account access for administrators, staff, customers, and riders.</p>
</div>

@if ($errors->any())
    <div class="admin-form-errors" role="alert">
        <strong>Please correct the following:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="panel">
    <h2>Create an Account</h2>
    <p class="muted">Choose the role and access level for the new account.</p>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="fields">
            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="255">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255">
            </div>
            <div class="field">
                <label for="new-user-role">Role</label>
                <select id="new-user-role" name="role" required>
                    <option value="" disabled @selected(old('role') === null)>Choose a role</option>
                    @foreach ($roleLabels as $role => $label)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <fieldset class="staff-module-picker" id="new-staff-modules" @if (old('role') !== 'staff') hidden @endif>
                <legend>Staff module access</legend>
                <p class="muted">Dashboard and profile remain available. Select the modules this staff account may use.</p>
                <div class="module-checkbox-grid">
                    @foreach ($staffModules as $module => $label)
                        <label><input type="checkbox" name="permissions[]" value="{{ $module }}" @checked(in_array($module, old('permissions', ['sales', 'manual_transactions']), true))> {{ $label }}</label>
                    @endforeach
                </div>
            </fieldset>
            <div class="field">
                <label for="password">Temporary password</label>
                <input id="password" name="password" type="password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required autocomplete="new-password">
            </div>
        </div>
        <p class="muted">Use exactly 6 digits. Share the temporary password with the account holder securely.</p>
        <button class="button" type="submit">Create Account</button>
    </form>
</section>

<section class="panel">
    <h2>Accounts</h2>
    @if (session('success'))
        <p class="muted" role="status">{{ session('success') }}</p>
    @endif
    <form method="GET" action="{{ route('admin.users.index') }}" class="user-filters">
        <div class="field">
            <label for="account-search">Search accounts</label>
            <input id="account-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
        </div>
        <div class="field">
            <label for="account-role-filter">Filter by role</label>
            <select id="account-role-filter" name="role">
                <option value="">All roles</option>
                @foreach ($roleLabels as $role => $label)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="user-filter-actions">
            <button class="button" type="submit">Search</button>
            <a class="button" href="{{ route('admin.users.index') }}">Clear</a>
        </div>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Sign-in activity</th>
                    <th>Access</th>
                    <th>Update role</th>
                    <th>Account actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php
                        $currentRole = $user->isAdmin() ? 'admin' : $user->role;
                        $accessLabels = [
                            'admin' => 'All admin sections and role management',
                            'customer' => 'Store, checkout, and own orders',
                            'rider' => 'Assigned delivery tasks',
                        ];
                        $lastSignIn = $user->login_histories_max_logged_in_at
                            ? \Illuminate\Support\Carbon::parse($user->login_histories_max_logged_in_at)
                            : null;
                        $recentlySignedIn = $lastSignIn?->greaterThanOrEqualTo(now()->subDays(30)) ?? false;
                    @endphp
                    <tr>
                        <td>{{ $user->name }} @if ($user->is(auth()->user())) <span class="muted">(you)</span> @endif</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $roleLabels[$currentRole] ?? ucfirst($currentRole) }}</td>
                        <td>
                            <span class="account-login-status {{ $recentlySignedIn ? 'recent' : 'inactive' }}">
                                <span aria-hidden="true"></span>
                                @if ($recentlySignedIn)
                                    Recent sign-in
                                @elseif ($lastSignIn)
                                    No recent sign-in
                                @else
                                    Never signed in
                                @endif
                            </span>
                            @if ($lastSignIn)
                                <small class="account-login-date">{{ $lastSignIn->timezone(config('app.timezone'))->format('M d, Y g:i A') }}</small>
                            @endif
                        </td>
                        <td>
                            @if ($currentRole === 'staff')
                                <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="permission-form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="module-checkbox-grid">
                                        @foreach ($staffModules as $module => $label)
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="{{ $module }}" @checked(in_array($module, $user->staff_permissions ?? ['sales', 'manual_transactions'], true))>
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <button class="button" type="submit">Save Access</button>
                                </form>
                                <small class="muted">Dashboard and profile remain available.</small>
                            @else
                                {{ $accessLabels[$currentRole] ?? 'No configured access' }}
                            @endif
                        </td>
                        <td>
                            @if ($user->is(auth()->user()))
                                <span class="muted">Your administrator role is protected.</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="role-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="role-{{ $user->id }}">Role for {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role" required>
                                        @foreach ($roleLabels as $role => $label)
                                            <option value="{{ $role }}" @selected($currentRole === $role)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="button" type="submit">Save</button>
                                </form>
                            @endif
                        </td>
                        <td>
                            <details class="account-edit">
                                <summary>Edit details</summary>
                                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="account-name-{{ $user->id }}">Name</label>
                                    <input id="account-name-{{ $user->id }}" name="name" value="{{ $user->name }}" required maxlength="255">
                                    <label for="account-email-{{ $user->id }}">Email</label>
                                    <input id="account-email-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" required maxlength="255">
                                    <button class="button" type="submit">Save details</button>
                                </form>
                            </details>
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button danger" type="submit">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No user accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->withQueryString()->links() }}
</section>

<style>
    .staff-module-picker,
    .permission-form {
        min-width: 260px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
    }

    .staff-module-picker[hidden] { display: none; }
    .staff-module-picker legend { padding: 0 5px; font-weight: 700; }

    .module-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(130px, 1fr));
        gap: 7px 12px;
        margin-bottom: 10px;
    }

    .module-checkbox-grid label {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin: 0;
        font-size: 12px;
        font-weight: 500;
    }

    .module-checkbox-grid input { width: auto; margin: 2px 0 0; }

    .role-form {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 190px;
    }

    .role-form select {
        min-width: 120px;
    }

    .role-form .button {
        padding: 9px 12px;
    }

    .admin-form-errors {
        margin-bottom: 14px;
        padding: 12px 14px;
        border: 1px solid rgba(220, 38, 38, .32);
        border-radius: 10px;
        background: rgba(220, 38, 38, .08);
        color: #b91c1c;
    }

    .admin-form-errors ul {
        margin: 6px 0 0;
        padding-left: 20px;
    }

    .user-filters {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        margin: 16px 0;
    }

    .user-filters .field {
        width: min(100%, 300px);
        margin: 0;
    }

    .user-filter-actions {
        display: flex;
        gap: 8px;
    }

    .account-edit {
        min-width: 180px;
        margin-bottom: 8px;
    }

    .account-login-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 700;
    }

    .account-login-status > span {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
    }

    .account-login-status.recent > span {
        background: #16a34a;
        box-shadow: 0 0 8px rgba(22, 163, 74, .35);
    }

    .account-login-status.inactive > span {
        background: #94a3b8;
    }

    .account-login-date {
        display: block;
        margin-top: 5px;
        white-space: nowrap;
    }

    .account-edit summary {
        cursor: pointer;
        color: var(--primary);
        font-weight: 700;
    }

    .account-edit form {
        display: grid;
        gap: 7px;
        min-width: 210px;
        margin-top: 8px;
    }

    .account-edit label {
        margin: 0;
        font-size: 11px;
    }

    .account-edit input {
        min-width: 190px;
    }

    .button.danger {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    @media (max-width: 560px) {
        .module-checkbox-grid { grid-template-columns: 1fr; }
        .staff-module-picker,
        .permission-form { min-width: 0; }

        .user-filters {
            align-items: stretch;
            flex-direction: column;
        }

        .user-filters .field {
            width: 100%;
        }

        .role-form {
            min-width: 0;
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('new-user-role');
    const modulePicker = document.getElementById('new-staff-modules');

    roleSelect?.addEventListener('change', () => {
        modulePicker.hidden = roleSelect.value !== 'staff';
    });
});
</script>
@endsection
