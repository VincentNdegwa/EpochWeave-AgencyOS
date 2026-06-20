<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);
        $this->actingAs($this->user);
    }

    public function test_user_can_mark_notification_as_read()
    {
        $notification = DatabaseNotification::create([
            'id' => 'test-notification-1',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_id' => $this->user->id,
            'notifiable_type' => User::class,
            'data' => ['title' => 'Test', 'message' => 'Test message'],
            'read_at' => null,
        ]);

        $response = $this->patch(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        DatabaseNotification::create([
            'id' => 'test-notification-1',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_id' => $this->user->id,
            'notifiable_type' => User::class,
            'data' => ['title' => 'Test'],
            'read_at' => null,
        ]);

        DatabaseNotification::create([
            'id' => 'test-notification-2',
            'type' => 'App\Notifications\TestNotification',
            'notifiable_id' => $this->user->id,
            'notifiable_type' => User::class,
            'data' => ['title' => 'Test 2'],
            'read_at' => null,
        ]);

        $response = $this->patch(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }
}
