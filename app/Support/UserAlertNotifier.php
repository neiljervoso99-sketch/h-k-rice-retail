<?php

namespace App\Support;

use App\Events\UserAlertCreated;
use App\Models\User;
use App\Notifications\SystemAlert;
use Illuminate\Support\Str;

class UserAlertNotifier
{
    public static function send(
        User $user,
        string $title,
        string $message,
        ?string $url = null,
    ): void {
        $notification = new SystemAlert($title, $message, $url);
        $notificationId = (string) Str::uuid();

        $user->notifications()->create([
            'id' => $notificationId,
            'type' => $notification::class,
            'data' => $notification->toArray($user),
        ]);

        broadcast(new UserAlertCreated(
            (int) $user->getKey(),
            $notificationId,
            $title,
            $message,
            $url,
        ));
    }

    public static function admins(
        string $title,
        string $message,
        ?string $url = null,
    ): void {
        User::query()
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhere('is_admin', true);
            })
            ->each(fn (User $admin) => self::send(
                $admin,
                $title,
                $message,
                $url,
            ));
    }
}
