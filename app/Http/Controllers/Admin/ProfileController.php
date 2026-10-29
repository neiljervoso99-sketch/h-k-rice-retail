<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
            'loginHistories' => LoginHistory::query()
                ->where('user_id', $request->user()->id)
                ->latest('logged_in_at')
                ->paginate(10, ['*'], 'login_page'),
            'activityLogs' => ActivityLog::query()
                ->with('user:id,name,role')
                ->when(
                    !$request->user()->isAdmin(),
                    fn ($query) => $query->where('user_id', $request->user()->id)
                )
                ->latest()
                ->paginate(10, ['*'], 'activity_page'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_picture' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $before = $user->only(['name', 'email', 'phone', 'profile_picture']);

        if ($request->hasFile('profile_picture')) {
            $newPicture = $request->file('profile_picture')
                ->store('profile-pictures', 'public');

            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->profile_picture = $newPicture;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        $user->save();

        $after = $user->only(['name', 'email', 'phone', 'profile_picture']);
        $changes = [];

        foreach ($after as $field => $value) {
            if ($before[$field] !== $value) {
                $changes[$field] = [
                    'from' => $before[$field],
                    'to' => $value,
                ];
            }
        }

        if ($changes !== []) {
            ActivityLog::record($request, 'profile.updated', $changes);
        }

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Your profile has been updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        $user->password = $validated['password'];
        $user->save();

        ActivityLog::record($request, 'password.updated');

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Your password has been updated successfully.');
    }
}