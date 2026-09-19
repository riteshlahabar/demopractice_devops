<?php

namespace App\Providers;

use App\Contracts\Notifications\DeviceTokenRegistryContract;
use App\Contracts\Notifications\NotificationSenderContract;
use App\Contracts\Notifications\NotificationTemplateContract;
use App\Contracts\Notifications\PushGatewayContract;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\TourPlan;
use App\Models\Finance\Payment;
use App\Models\Hr\Announcement;
use App\Models\Hr\SalaryAdvance;
use App\Models\Sales\Order;
use App\Models\Sales\ReturnRequest;
use App\Models\User;
use App\Observers\Notifications\AnnouncementNotificationObserver;
use App\Observers\Notifications\DealerAccountNotificationObserver;
use App\Observers\Notifications\OrderNotificationObserver;
use App\Observers\Notifications\PaymentNotificationObserver;
use App\Observers\Notifications\ReturnRequestNotificationObserver;
use App\Observers\Notifications\SalesmanRequestNotificationObserver;
use App\Repositories\Notifications\EloquentDeviceTokenRegistry;
use App\Services\Notifications\NotificationSender;
use App\Services\Notifications\NotificationTemplateService;
use App\Services\Notifications\Push\FcmPushGateway;
use App\Services\Notifications\Push\LogPushGateway;
use Illuminate\Support\ServiceProvider;

/**
 * App notifications: inbox + push wiring, and the model observers that send
 * the automatic ones. PUSH_DRIVER picks the push provider (log | fcm).
 */
class NotificationServiceProvider extends ServiceProvider
{
    public array $singletons = [
        NotificationSenderContract::class => NotificationSender::class,
        NotificationTemplateContract::class => NotificationTemplateService::class,
        DeviceTokenRegistryContract::class => EloquentDeviceTokenRegistry::class,
    ];

    public function register(): void
    {
        $this->app->singleton(PushGatewayContract::class, fn ($app): PushGatewayContract => config('notifications.push.driver') === 'fcm'
            ? $app->make(FcmPushGateway::class)
            : $app->make(LogPushGateway::class));
    }

    public function boot(): void
    {
        Order::observe(OrderNotificationObserver::class);
        ReturnRequest::observe(ReturnRequestNotificationObserver::class);
        Payment::observe(PaymentNotificationObserver::class);
        User::observe(DealerAccountNotificationObserver::class);
        Announcement::observe(AnnouncementNotificationObserver::class);

        foreach ([LeaveApplication::class, Expense::class, SalaryAdvance::class, TourPlan::class] as $model) {
            $model::observe(SalesmanRequestNotificationObserver::class);
        }
    }
}
