<?php

namespace App\Observers\Notifications;

use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\TourPlan;
use App\Models\Hr\SalaryAdvance;
use Illuminate\Database\Eloquent\Model;

/**
 * Salesman: a decision on their leave, expense, advance/loan or tour plan.
 */
final class SalesmanRequestNotificationObserver extends NotificationObserver
{
    public function updated(Model $record): void
    {
        if (! $this->statusChanged($record)) {
            return;
        }

        [$event, $replace] = match (true) {
            $record instanceof LeaveApplication => ['leave', [
                'leave_type' => strtolower($this->label($record->leave_type)),
                'from_date' => $this->date($record->from_date),
                'to_date' => $this->date($record->to_date),
            ]],
            $record instanceof Expense => ['expense', [
                'expense_type' => strtolower($this->label($record->expense_type)),
                'amount' => $this->money($record->amount),
            ]],
            $record instanceof SalaryAdvance => ['advance', [
                'advance_type' => $record->advance_type === SalaryAdvance::TYPE_LOAN ? 'loan' : 'advance',
                'amount' => $this->money($record->amount),
            ]],
            $record instanceof TourPlan => ['tour_plan', ['plan_date' => $this->date($record->plan_date)]],
            default => [null, []],
        };

        if ($event === null) {
            return;
        }

        $this->notify(
            $record->salesman_id ? (int) $record->salesman_id : null,
            $event,
            (string) $record->status,
            $replace,
            [$event.'_id' => $record->getKey()],
        );
    }
}
