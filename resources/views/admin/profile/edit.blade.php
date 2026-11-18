@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="page-heading">
        <h1>My Profile</h1>
        <p>Manage your profile, security, and account history.</p>
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
        $profileInitials = collect(preg_split('/\s+/', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'A';
    @endphp

    <style>
        .profile-page {
            max-width: 1180px;
        }

        .profile-tabs {
            display: flex;
            gap: 6px;
            width: 100%;
            max-width: 100%;
            padding: 0 0 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 18px;
            overflow-x: auto;
        }

        .profile-tab {
            min-height: 42px;
            padding: 9px 16px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: var(--muted);
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .profile-tab[aria-selected="true"] {
            background: var(--green);
            color: #fff;
        }

        .profile-summary {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 22px;
        }

        .profile-picture-picker {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 104px;
            height: 104px;
            flex: 0 0 104px;
            overflow: hidden;
            border: 3px solid var(--green);
            border-radius: 50%;
            background: var(--green-light);
            color: var(--green);
            font-size: 30px;
            font-weight: 700;
            cursor: pointer;
        }

        .profile-picture-picker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-picture-picker > span:not([data-profile-initials]) {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            padding: 5px 2px;
            background: rgba(25, 61, 37, .88);
            color: #fff;
            font-size: 10px;
            text-align: center;
        }

        .profile-photo-hint {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .profile-fields {
            margin-top: 20px;
        }

        .profile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .profile-table-wrap {
            overflow-x: auto;
        }

        .profile-pagination .pagination {
            display: flex;
            gap: 6px;
            padding: 0;
            margin: 18px 0 0;
            list-style: none;
        }

        .profile-pagination .page-link {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--panel, #fff);
            color: var(--text);
            text-decoration: none;
        }

        .profile-pagination .page-item.active .page-link {
            border-color: var(--green);
            background: var(--green);
            color: #fff;
        }

        .profile-pagination .page-item.disabled .page-link {
            color: var(--muted);
            opacity: .7;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .profile-table th,
        .profile-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .profile-table th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .activity-event {
            font-weight: 700;
        }

        .activity-meta {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .activity-change {
            margin: 0 0 4px;
        }

        .profile-picture-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            clip-path: inset(50%);
        }

        .profile-picture-input:focus-visible + .profile-picture-picker {
            outline: 3px solid #78a7ff;
            outline-offset: 3px;
        }

        .profile-tab:focus-visible {
            outline: 2px solid var(--green);
            outline-offset: 2px;
        }

        .profile-tab-panel[hidden] {
            display: none;
        }

        @media (max-width: 560px) {
            .profile-tab {
                flex: 1 0 auto;
                padding-inline: 12px;
            }

            .profile-summary {
                align-items: flex-start;
                gap: 14px;
            }

            .profile-picture-picker {
                width: 84px;
                height: 84px;
                flex-basis: 84px;
                font-size: 24px;
            }
        }
    </style>

    <div class="profile-page">
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
        <div class="profile-summary">
            <div>
                <input
                    class="profile-picture-input"
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    form="profile-update-form"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    aria-label="Choose a profile picture"
                >
                <label class="profile-picture-picker" for="profile_picture" title="Click to choose a profile picture">
                @if ($user->profile_picture)
                    <img
                        data-profile-picture-preview
                        src="{{ url('/storage/' . ltrim($user->profile_picture, '/')) }}"
                        alt="Your profile picture"
                    >
                @else
                    <span data-profile-initials>{{ $profileInitials }}</span>
                @endif
                    <span>Change photo</span>
                </label>
            </div>

            <div>
                <h2 style="margin:0 0 7px;">{{ $user->name }}</h2>
                <p class="muted" style="margin:0;">{{ ucfirst($user->role ?? 'User') }} account</p>
                <p class="muted" style="margin:5px 0 0;">{{ $user->email }}</p>
                <p class="profile-photo-hint" style="margin-top:10px;">Click your photo to choose or replace it. The preview updates immediately.</p>
            </div>
        </div>

        <form
            id="profile-update-form"
            method="POST"
            action="{{ route('admin.profile.update') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')

            <div class="fields profile-fields">
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

            <p class="profile-photo-hint" style="margin-top:18px;">Photo formats: JPG, PNG, or WebP. Maximum size: 2 MB.</p>
            @error('profile_picture')
                <p style="color:#a52b24; margin-top:7px;">{{ $message }}</p>
            @enderror

            <div class="profile-actions">
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

    <section class="panel profile-tab-panel" id="panel-logins" role="tabpanel" aria-labelledby="tab-logins" data-profile-panel="logins" @if ($activeTab !== 'logins') hidden @endif>
        <h2 style="margin-top:0;">Login History</h2>
        <div class="profile-table-wrap">
        <table class="profile-table">
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
                        <td>{{ $loginHistory->logged_in_at->format('M j, Y g:i A') }}</td>
                        <td>{{ $loginHistory->logged_out_at?->format('M j, Y g:i A') ?? 'Current session' }}</td>
                        <td>{{ $loginHistory->ip_address ?? 'Unavailable' }}</td>
                        <td style="max-width:280px; overflow-wrap:anywhere;">{{ $loginHistory->user_agent ?? 'Unavailable' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:10px;">No login history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="profile-pagination">
            {{ $loginHistories->appends(['tab' => 'logins'])->links('pagination::simple-bootstrap-5') }}
        </div>
    </section>

    <section class="panel profile-tab-panel" id="panel-activity" role="tabpanel" aria-labelledby="tab-activity" data-profile-panel="activity" @if ($activeTab !== 'activity') hidden @endif>
        <h2 style="margin-top:0;">{{ $user->isAdmin() ? 'Admin Activity Log' : 'My Activity' }}</h2>
        <div class="profile-table-wrap">
        <table class="profile-table">
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
                        <td style="white-space:nowrap;">{{ $activityLog->created_at->format('M j, Y g:i A') }}</td>
                        <td>{{ $activityLog->user?->name ?? 'Deleted user' }}</td>
                        <td>
                            <span class="activity-event">{{ str($activityLog->event)->replace('.', ' ')->title() }}</span>
                            @if (isset($activityLog->changes['status_code']))
                                <span class="activity-meta">Status {{ $activityLog->changes['status_code'] }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $fieldChanges = collect($activityLog->changes ?? [])
                                    ->filter(fn ($change) => is_array($change) && (array_key_exists('from', $change) || array_key_exists('to', $change)));
                            @endphp
                            @forelse ($fieldChanges as $field => $change)
                                <p class="activity-change"><strong>{{ str($field)->replace('_', ' ')->title() }}:</strong> {{ $change['from'] ?? 'empty' }} → {{ $change['to'] ?? 'empty' }}</p>
                            @empty
                                <span class="activity-meta">No field changes</span>
                            @endforelse
                        </td>
                        <td>{{ $activityLog->ip_address ?? 'Unavailable' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:10px;">No account activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="profile-pagination">
            {{ $activityLogs->appends(['tab' => 'activity'])->links('pagination::simple-bootstrap-5') }}
        </div>
    </section>
    </div>

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

            const pictureInput = document.getElementById('profile_picture');
            const picturePicker = document.querySelector('.profile-picture-picker');
            let previewUrl = null;

            pictureInput?.addEventListener('change', () => {
                const file = pictureInput.files?.[0];
                if (!file || !picturePicker) {
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
                previewUrl = URL.createObjectURL(file);

                let image = picturePicker.querySelector('[data-profile-picture-preview]');
                if (!image) {
                    image = document.createElement('img');
                    image.dataset.profilePicturePreview = '';
                    image.alt = 'Selected profile picture preview';
                    picturePicker.prepend(image);
                    picturePicker.querySelector('[data-profile-initials]')?.remove();
                }

                image.src = previewUrl;
            });

            document.querySelectorAll('img[data-profile-picture-preview]').forEach((image) => {
                image.addEventListener('error', () => {
                    image.remove();
                    const initials = document.createElement('span');
                    initials.textContent = @json($profileInitials);
                    initials.dataset.profileInitials = '';
                    picturePicker?.prepend(initials);
                }, { once: true });
            });

            window.addEventListener('beforeunload', () => {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
            });
        })();
    </script>
@endsection