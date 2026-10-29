@extends('admin.layouts.app')

@section('title', 'User Roles & Permissions')

@section('content')
<div class="page-heading">
    <h1>User Roles &amp; Permissions</h1>
    <p>Manage account access for administrators, staff, customers, and riders.</p>
</div>

<section class="panel">
    <h2>Create a Staff Account</h2>
    <p class="muted">New staff accounts can access the dashboard only. You can change their role below.</p>

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
                <label for="password">Temporary password</label>
                <input id="password" name="password" type="password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required autocomplete="new-password">
            </div>
        </div>
        <p class="muted">Use exactly 6 digits. Share the temporary password with the staff member securely.</p>
        <button class="button" type="submit">Create Staff Account</button>
    </form>
</section>

<section class="panel">
    <h2>Accounts</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Access</th>
                    <th>Update role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php
                        $currentRole = $user->isAdmin() ? 'admin' : $user->role;
                        $roleLabels = [
                            'admin' => 'Administrator',
                            'staff' => 'Staff',
                            'customer' => 'Customer',
                            'rider' => 'Rider',
                        ];
                        $accessLabels = [
                            'admin' => 'All admin sections and role management',
                            'staff' => 'Dashboard only',
                            'customer' => 'Store, checkout, and own orders',
                            'rider' => 'Assigned delivery tasks',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $user->name }} @if ($user->is(auth()->user())) <span class="muted">(you)</span> @endif</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $roleLabels[$currentRole] ?? ucfirst($currentRole) }}</td>
                        <td>{{ $accessLabels[$currentRole] ?? 'No configured access' }}</td>
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
                    </tr>
                @empty
                    <tr><td colspan="5">No user accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</section>

<style>
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
        .role-form {
            min-width: 0;
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endsection
