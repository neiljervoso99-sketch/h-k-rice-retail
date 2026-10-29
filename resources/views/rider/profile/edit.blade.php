
@extends('rider.layouts.app')

@section('title', 'Rider Profile')

@push('styles')
<style>
    .page-heading {
        margin-bottom: 24px;
    }

    .page-heading h2 {
        margin: 0 0 8px;
        color: #263b2b;
        font-size: 27px;
    }

    .page-heading p {
        margin: 0;
        color: #657267;
        font-size: 14px;
    }

    .profile-panel {
        padding: 24px 22px;
        border: 1px solid #dfe6dc;
        border-radius: 10px;
        background: #fff;
    }

    .profile-summary {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 26px;
        flex-wrap: wrap;
    }

    .profile-photo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 108px;
        height: 108px;
        overflow: hidden;
        flex-shrink: 0;
        border: 3px solid #245333;
        border-radius: 50%;
        background: #e8f1e5;
        color: #245333;
        font-size: 30px;
        font-weight: bold;
    }

    .profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-summary h3 {
        margin: 0 0 8px;
        font-size: 20px;
        overflow-wrap: anywhere;
    }

    .profile-summary p {
        margin: 5px 0 0;
        color: #657267;
        overflow-wrap: anywhere;
    }

    .profile-field {
        min-width: 0;
        margin-bottom: 20px;
    }

    .profile-field label {
        display: block;
        margin-bottom: 8px;
        color: #263b2b;
        font-weight: 600;
    }

    .profile-field input {
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 10px;
        border: 1px solid #cfd9cb;
        border-radius: 5px;
        background: #fff;
        color: #263b2b;
    }

    .profile-field input:focus {
        outline: 2px solid #b7d2b5;
        border-color: #245333;
    }

    .field-help {
        margin: 7px 0 0;
        color: #657267;
        font-size: 13px;
        line-height: 1.5;
    }

    .field-error {
        margin: 7px 0 0;
        color: #a52b24;
        font-size: 13px;
    }

    .profile-fields-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 16px;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .profile-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 11px 17px;
        border: 0;
        border-radius: 5px;
        background: #245333;
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .profile-button:hover {
        background: #193d25;
    }

    .profile-button.cancel {
        background: #657267;
    }

    .profile-button.cancel:hover {
        background: #4f5c51;
    }

    @media (max-width: 650px) {
        .profile-panel {
            padding: 18px 14px;
        }

        .profile-fields-grid {
            grid-template-columns: 1fr;
        }

        .page-heading h2 {
            font-size: 23px;
        }

        .profile-fields-grid input,
        .profile-fields-grid select,
        .profile-fields-grid textarea {
            min-height: 44px;
            font-size: 16px;
        }

        .profile-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .profile-actions .profile-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
    <div class="page-heading">
        <h2>Rider Profile</h2>
        <p>Manage your profile picture and personal information.</p>
    </div>

    <section class="profile-panel">
        <div class="profile-summary">
            <div class="profile-photo">
                @if ($user->profile_picture)
                    <img
                        src="{{ asset('storage/' . $user->profile_picture) }}"
                        alt="Rider profile picture"
                    >
                @else
                    {{ collect(preg_split('/\s+/', trim($user->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('') ?: 'R' }}
                @endif
            </div>

            <div>
                <h3>{{ $user->name }}</h3>
                <p>Delivery Rider account</p>
                <p>{{ $user->email }}</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('rider.profile.update') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')

            <div class="profile-field">
                <label for="profile_picture">Profile Picture</label>

                <input
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

                <p class="field-help">
                    Accepted formats: JPG, JPEG, PNG, and WebP.
                    Maximum size: 2 MB.
                </p>

                @error('profile_picture')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="profile-fields-grid">
                <div class="profile-field">
                    <label for="name">Full Name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >

                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >

                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="phone">Contact Number</label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        maxlength="30"
                        autocomplete="tel"
                        placeholder="Enter your contact number"
                    >

                    @error('phone')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="profile-actions">
                <button type="submit" class="profile-button">
                    Save Changes
                </button>

                <a
                    href="{{ route('rider.dashboard') }}"
                    class="profile-button cancel"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
