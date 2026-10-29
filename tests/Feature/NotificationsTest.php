<?php

namespace Tests\Feature;

use App\Events\UserAlertCreated;
use App\Models\User;
use App\Notifications\SystemAlert;
use App\Support\UserAlertNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_only_receive_and_mark_their_own_notifications_read(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $otherUser = User::factory()->create(['role' => 'customer']);

        $user->notify(new SystemAlert('Order update', 'Your order was accepted.'));
        $otherUser->notify(new SystemAlert('Private update', 'This belongs to another user.'));

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.title', 'Order update');

        $notification = $user->unreadNotifications()->firstOrFail();

        $this->postJson(route('notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($notification->fresh()->read_at);

        $this->postJson(route('notifications.read', $otherUser->notifications()->firstOrFail()->id))
            ->assertNotFound();
    }

    public function test_sending_a_user_alert_stores_it_and_broadcasts_it(): void
    {
        Event::fake([UserAlertCreated::class]);
        $user = User::factory()->create(['role' => 'rider']);

        UserAlertNotifier::send(
            $user,
            'New delivery assignment',
            'Order HK-123 was assigned to you.',
            '/rider/orders/123',
        );

        $notification = $user->notifications()->firstOrFail();

        $this->assertSame('New delivery assignment', $notification->data['title']);
        $this->assertSame('/rider/orders/123', $notification->data['url']);
        Event::assertDispatched(UserAlertCreated::class, fn (UserAlertCreated $event) =>
            $event->userId === $user->id
            && $event->notificationId === $notification->id
        );
    }

    public function test_notifications_require_authentication(): void
    {
        $this->getJson(route('notifications.index'))->assertUnauthorized();
        $this->postJson(route('notifications.read-all'))->assertUnauthorized();
    }
}
