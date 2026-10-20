<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequested;
use App\Notifications\LeaveStatusChanged;
use Illuminate\Support\Facades\Log;

/**
 * Sends leave email notifications.
 *
 * READY BUT NOT CONNECTED: every method checks the master switch
 * (config/hr.php -> notifications_enabled, env HR_NOTIFICATIONS_ENABLED).
 * While the switch is off, nothing is sent and nothing is queued.
 */
class LeaveNotificationService
{
    public function enabled(): bool
    {
        return (bool) config('hr.notifications_enabled', false);
    }

    /** Notify approvers (HR managers + the employee's department manager) about a new request. */
    public function notifyNewRequest(LeaveRequest $leave): void
    {
        if (!$this->enabled()) {
            return;
        }

        try {
            $recipients = collect();

            // HR managers
            $recipients = $recipients->merge(
                User::whereHas('role', fn ($q) => $q->where('name', 'hr_manager'))->where('is_active', true)->get()
            );

            // Department manager
            $manager = $leave->employee?->department?->manager;
            if ($manager) {
                $recipients->push($manager);
            }

            foreach ($recipients->unique('id') as $user) {
                $user->notify(new LeaveRequested($leave));
            }
        } catch (\Throwable $e) {
            Log::warning('Leave notification (new request) failed: ' . $e->getMessage());
        }
    }

    /** Notify the employee that their request status changed. */
    public function notifyStatusChanged(LeaveRequest $leave): void
    {
        if (!$this->enabled()) {
            return;
        }

        try {
            $user = $leave->employee?->user;
            if ($user) {
                $user->notify(new LeaveStatusChanged($leave));
            }
        } catch (\Throwable $e) {
            Log::warning('Leave notification (status changed) failed: ' . $e->getMessage());
        }
    }
}
