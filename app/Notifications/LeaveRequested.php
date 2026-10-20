<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequested extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $leave = $this->leaveRequest->loadMissing(['employee.user', 'leaveType']);
        $employeeName = $leave->employee->user->name ?? 'An employee';

        return (new MailMessage)
            ->subject('New Leave Request — ' . $employeeName)
            ->view('emails.leave-requested', [
                'leave' => $leave,
                'notifiable' => $notifiable,
            ]);
    }
}
