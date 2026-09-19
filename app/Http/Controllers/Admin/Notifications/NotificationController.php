<?php

namespace App\Http\Controllers\Admin\Notifications;

use App\Contracts\Notifications\NotificationSenderContract;
use App\Data\Notifications\NotificationMessage;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Adding a notification sends it: to one user, or one row per active account
 * in the chosen group so every person has their own read state. Editing an
 * existing row only changes the stored text and pushes nothing.
 */
class NotificationController extends AdminModuleController
{
    protected string $moduleKey = 'notifications';

    public function __construct(AdminModuleServices $modules, private readonly NotificationSenderContract $sender)
    {
        parent::__construct($modules);
    }

    public function store(Request $request): RedirectResponse
    {
        $module = $this->module();
        $data = $this->validateRequest($request, $module);
        $audience = (string) ($data['audience'] ?? '') ?: 'user';

        $message = new NotificationMessage(
            title: (string) $data['title'],
            message: (string) $data['message'],
            type: 'general',
            channel: (string) ($data['channel'] ?? 'push'),
        );

        if ($audience === 'user') {
            if (empty($data['user_id'])) {
                throw ValidationException::withMessages(['user_id' => 'Choose the recipient, or pick a group in Send To.']);
            }

            $this->sender->toUser((int) $data['user_id'], $message);
            $sent = 1;
        } else {
            $sent = $this->sender->toAudience($audience, $message);
        }

        return redirect()
            ->route($module['route'].'.index')
            ->with('success', 'Notification sent to '.$sent.' '.($sent === 1 ? 'account' : 'accounts').'.');
    }
}
