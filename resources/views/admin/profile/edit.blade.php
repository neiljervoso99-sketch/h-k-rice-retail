@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="page-heading">
        <h1>My Profile &amp; Activity</h1>
        <p>Manage your account, security, and recent activity.</p>
    </div>

    @php
        $tabs = [
            'profile' => 'Profile',
            'security' => 'Security',
            'logins' => 'Login History',
            'activity' => 'Activity Log',
        ];
        $requestedTab = request('tab');
        $activeTab = array_key_exists($requestedTab, $tabs)
            ? $requestedTab
            : ($errors->has('current_password') || $errors->has('password') ? 'security' : 'profile');
    @endphp

    <style>
        .profile-tabs {
            display: flex;
            gap: 4px;
            width: fit-content;
            max-width: 100%;
            padding: 4px;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 16px;
            background: var(--green-light);
            overflow-x: auto;
        }

        .profile-tab {
            min-height: 38px;
            padding: 8px 14px;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .profile-tab[aria-selected="true"] {
            background: var(--green);
            color: #fff;
        }

        .profile-tab:focus-visible {
            outline: 2px solid var(--green);
            outline-offset: 2px;
        }

        .profile-tab-panel[hidden] {
            display: none;
        }

        @media (max-width: 560px) {
            .profile-tabs {
                width: 100%;
            }

            .profile-tab {
                flex: 1 0 auto;
                padding-inline: 10px;
            }
        }
    </style>

    <div class="profile-tabs" role="tablist" aria-label="Profile settings">
        @foreach ($tabs as $tabId => $tabLabel)
            <button
                class="profile-tab"
                id="tab-{{ $tabId }}"
                type="button"
                role="tab"
                aria-controls="panel-{{ $tabId }}"
                aria-selected="{{ $activeTab === $tabId ? 'true' : 'false' }}"
                tabindex="{{ $activeTab === $tabId ? '0' : '-1' }}"
                data-profile-tab="{{ $tabId }}"
            >{{ $tabLabel }}</button>
        @endforeach
    </div>

    <section class="panel profile-tab-panel" id="panel-profile" role="tabpanel" aria-labelledby="tab-profile" data-profile-panel="profile" @if ($activeTab !== 'profile') hidden @endif>
        <div style="display:flex; align-items:center; gap:18px; margin-bottom:24px; flex-wrap:wrap;">
            <div style="width:100px; height:100px; border-radius:50%; overflow:hidden; border:3px solid #245333; background:#e8f1e5; display:flex; align-items:center; justify-content:center; color:#245333; font-size:30px; font-weight:bold; flex-shrink:0;">
                @if ($user->profile_picture)
                    <img
                        src="{{ asset('storage/' . $user->profile_picture) }}"
                        alt="Admin profile picture"
                        style="width:100%; height:100%; object-fit:cover;"
                    >
                @else
                    {{ collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'A' }}
                @endif
            </div>

            <div>
                <h2 style="margin:0 0 7px;">{{ $user->name }}</h2>
                <p class="muted" style="margin:0;">{{ ucfirst($user->role ?? 'User') }} account</p>
                <p class="muted" style="margin:5px 0 0;">{{ $user->email }}</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.profile.update') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')

            <div class="field" style="margin-bottom:20px;">
                <label for="profile_picture">Profile Picture</label>
                <input
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                <p class="muted" style="margin:7px 0 0;">
                    Accepted formats: JPG, JPEG, PNG, and WebP. Maximum size: 2 MB.
                </p>

                @error('profile_picture')
                    <p style="color:#a52b24; margin-top:7px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="fields">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        required
                    >
                    @error('name')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        required
                    >
                    @error('email')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="phone">Contact Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        maxlength="30"
                        placeholder="Enter your contact number"
                    >
                    @error('phone')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:24px;">
                <button type="submit" class="button">
                    Save Changes
                </button>

                <a href="{{ route('admin.dashboard') }}"
                   class="button"
                   style="background:#657267;">
                    Cancel
                </a>
            </div>
        </form>
    </section>

    <section class="panel profile-tab-panel" id="panel-security" role="tabpanel" aria-labelledby="tab-security" data-profile-panel="security" @if ($activeTab !== 'security') hidden @endif>
        <h2 style="margin-top:0;">Change Password</h2>
        <form method="POST" action="{{ route('admin.profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="fields">
                <div class="field">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    @error('current_password')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required>
                    @error('password')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" title="Enter exactly 6 digits." required>
                </div>
            </div>

            <p class="muted">Use exactly 6 digits.</p>
            <button type="submit" class="button" style="margin-top:20px;">Update Password</button>
        </form>
    </section>

    <section class="panel profile-tab-panel" id="panel-logins" role="tabpanel" aria-labelledby="tab-logins" data-profile-panel="logins" @if ($activeTab !== 'logins') hidden @endif style="overflow-x:auto;">
        <h2 style="margin-top:0;">Login History</h2>
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr>
                    <th style="padding:10px;">Signed In</th>
                    <th style="padding:10px;">Signed Out</th>
                    <th style="padding:10px;">IP Address</th>
                    <th style="padding:10px;">Device</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($loginHistories as $loginHistory)
                    <tr>
                        <td style="padding:10px;">{{ $loginHistory->logged_in_at->format('M j, Y g:i A') }}</td>
                        <td style="padding:10px;">{{ $loginHistory->logged_out_at?->format('M j, Y g:i A') ?? 'Current session' }}</td>
                        <td style="padding:10px;">{{ $loginHistory->ip_address ?? 'Unavailable' }}</td>
                        <td style="padding:10px; max-width:280px; overflow-wrap:anywhere;">{{ $loginHistory->user_agent ?? 'Unavailable' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:10px;">No login history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $loginHistories->appends(['tab' => 'logins'])->links() }}
    </section>

    <section class="panel profile-tab-panel" id="panel-activity" role="tabpanel" aria-labelledby="tab-activity" data-profile-panel="activity" @if ($activeTab !== 'activity') hidden @endif style="overflow-x:auto;">
        <h2 style="margin-top:0;">{{ $user->isAdmin() ? 'Admin Activity Log' : 'My Activity' }}</h2>
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr>
                    <th style="padding:10px;">When</th>
                    <th style="padding:10px;">User</th>
                    <th style="padding:10px;">Activity</th>
                    <th style="padding:10px;">Changes</th>
                    <th style="padding:10px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activityLogs as $activityLog)
                    <tr>
                        <td style="padding:10px; white-space:nowrap;">{{ $activityLog->created_at->format('M j, Y g:i A') }}</td>
                        <td style="padding:10px;">{{ $activityLog->user?->name ?? 'Deleted user' }}</td>
                        <td style="padding:10px;">{{ str($activityLog->event)->replace('.', ' ')->title() }}</td>
                        <td style="padding:10px;">
                            @if ($activityLog->changes)
                                @foreach ($activityLog->changes as $field => $change)
                                    @if (is_array($change) && (array_key_exists('from', $change) || array_key_exists('to', $change)))
                                        <div><strong>{{ str($field)->replace('_', ' ')->title() }}:</strong> {{ $change['from'] ?? 'empty' }} to {{ $change['to'] ?? 'empty' }}</div>
                                    @else
                                        <pre style="margin:0; max-width:620px; white-space:pre-wrap; overflow-wrap:anywhere;">{{ json_encode($change, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @endif
                                @endforeach
                            @else
                                No field details
                            @endif
                        </td>
                        <td style="padding:10px;">{{ $activityLog->ip_address ?? 'Unavailable' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:10px;">No account activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $activityLogs->appends(['tab' => 'activity'])->links() }}
    </section>

    <script>
        (() => {
            const tabs = Array.from(document.querySelectorAll('[data-profile-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-profile-panel]'));

            const activateTab = (tab, moveFocus = false) => {
                const selectedTab = tab.dataset.profileTab;

                tabs.forEach((item) => {
                    const selected = item === tab;
                    item.setAttribute('aria-selected', String(selected));
                    item.tabIndex = selected ? 0 : -1;
                });

                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.profilePanel !== selectedTab;
                });

                const url = new URL(window.location.href);
                url.searchParams.set('tab', selectedTab);
                url.searchParams.delete('login_page');
                url.searchParams.delete('activity_page');
                window.history.replaceState(null, '', url);

                if (moveFocus) {
                    tab.focus();
                }
            };

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => activateTab(tab));
                tab.addEventListener('keydown', (event) => {
                    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                        return;
                    }

                    event.preventDefault();
                    const nextIndex = event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? tabs.length - 1
                            : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                    activateTab(tabs[nextIndex], true);
                });
            });
        })();
    </script>
@endsection