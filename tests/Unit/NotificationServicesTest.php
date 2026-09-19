<?php

namespace Tests\Unit;

use App\Contracts\Notifications\NotificationSenderContract;
use App\Contracts\Notifications\NotificationTemplateContract;
use App\Contracts\Notifications\PushGatewayContract;
use App\Data\Notifications\NotificationMessage;
use App\Models\Field\LeaveApplication;
use App\Models\Sales\Order;
use App\Models\User;
use App\Observers\Notifications\DealerAccountNotificationObserver;
use App\Observers\Notifications\OrderNotificationObserver;
use App\Observers\Notifications\SalesmanRequestNotificationObserver;
use App\Services\Notifications\Push\FcmAccessTokenProvider;
use App\Services\Notifications\Push\FcmPushGateway;
use App\Services\Notifications\Push\LogPushGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NotificationServicesTest extends TestCase
{
    /** @var list<array{int, NotificationMessage}> */
    private array $sent = [];

    public function test_push_driver_defaults_to_log(): void
    {
        $this->assertInstanceOf(LogPushGateway::class, $this->app->make(PushGatewayContract::class));
    }

    public function test_template_fills_placeholders_and_tap_data(): void
    {
        $message = $this->app->make(NotificationTemplateContract::class)
            ->make('order', 'dispatched', ['order_no' => 'ORD-7'], ['order_id' => 7, 'skip' => null]);

        $this->assertSame('Order dispatched', $message->title);
        $this->assertStringContainsString('ORD-7', $message->message);
        $this->assertSame(['type' => 'order', 'order_id' => '7', 'status' => 'dispatched'], $message->payload());
    }

    public function test_status_without_template_sends_nothing(): void
    {
        $this->assertNull($this->app->make(NotificationTemplateContract::class)->make('order', 'admin_review'));
        $this->assertNull($this->app->make(NotificationTemplateContract::class)->make('unknown', 'approved'));
    }

    public function test_fcm_body_carries_notification_and_string_data(): void
    {
        $gateway = new FcmPushGateway($this->createMock(FcmAccessTokenProvider::class));
        $body = $gateway->body(['topic' => 'role_dealer'], new NotificationMessage('Hi', 'There', 'order', ['order_id' => '5']));

        $this->assertSame('role_dealer', $body['message']['topic']);
        $this->assertSame(['title' => 'Hi', 'body' => 'There'], $body['message']['notification']);
        $this->assertSame(['type' => 'order', 'order_id' => '5'], $body['message']['data']);
    }

    public function test_order_status_change_notifies_the_dealer(): void
    {
        $order = $this->changed(new Order, ['id' => 9, 'order_no' => 'ORD-9', 'dealer_id' => 44, 'status' => 'approved'], ['status' => 'dispatched']);

        $this->observer(OrderNotificationObserver::class)->updated($order);

        $this->assertCount(1, $this->sent);
        $this->assertSame(44, $this->sent[0][0]);
        $this->assertSame('order', $this->sent[0][1]->type);
    }

    public function test_order_saved_without_status_change_sends_nothing(): void
    {
        $order = $this->changed(new Order, ['id' => 9, 'order_no' => 'ORD-9', 'customer_id' => 3, 'status' => 'approved'], ['notes' => 'x']);

        $this->observer(OrderNotificationObserver::class)->updated($order);

        $this->assertSame([], $this->sent);
    }

    public function test_leave_decision_notifies_the_salesman(): void
    {
        $leave = $this->changed(new LeaveApplication, ['id' => 2, 'salesman_id' => 12, 'leave_type' => 'sick', 'from_date' => '2026-09-20', 'to_date' => '2026-09-21', 'status' => 'pending'], ['status' => 'approved']);

        $this->observer(SalesmanRequestNotificationObserver::class)->updated($leave);

        $this->assertSame(12, $this->sent[0][0]);
        $this->assertSame('Leave approved', $this->sent[0][1]->title);
        $this->assertStringContainsString('20 Sep 2026', $this->sent[0][1]->message);
    }

    public function test_dealer_activation_notifies_only_dealers(): void
    {
        $observer = $this->observer(DealerAccountNotificationObserver::class);

        $observer->updated($this->changed(new User, ['id' => 5, 'role' => User::ROLE_CUSTOMER, 'status' => 'pending_approval'], ['status' => 'active']));
        $this->assertSame([], $this->sent);

        $observer->updated($this->changed(new User, ['id' => 6, 'role' => User::ROLE_DEALER, 'status' => 'pending_approval'], ['status' => 'active']));
        $this->assertSame(6, $this->sent[0][0]);
    }

    public function test_device_token_and_unread_routes_exist_for_every_app(): void
    {
        $uris = collect(Route::getRoutes()->getRoutes())->map->uri()->all();

        foreach (['customer', 'dealer', 'salesman'] as $app) {
            $this->assertContains("api/v1/{$app}/device-token", $uris);
            $this->assertContains("api/v1/{$app}/device-token/remove", $uris);
            $this->assertContains("api/v1/{$app}/notifications/unread-count", $uris);
        }
    }

    /**
     * A model as it looks inside an `updated` event: saved with $before, then $after changed.
     */
    private function changed(Model $model, array $before, array $after): Model
    {
        $model->forceFill($before)->syncOriginal();
        $model->forceFill($after)->syncChanges();

        return $model;
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function observer(string $class): object
    {
        $sender = new class($this->sent) implements NotificationSenderContract
        {
            public function __construct(private array &$sent) {}

            public function toUser(int $userId, NotificationMessage $message): void
            {
                $this->sent[] = [$userId, $message];
            }

            public function toAudience(string $audience, NotificationMessage $message): int
            {
                return 0;
            }
        };

        return new $class($sender, $this->app->make(NotificationTemplateContract::class));
    }
}
