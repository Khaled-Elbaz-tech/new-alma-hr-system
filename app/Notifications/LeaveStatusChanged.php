<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusChanged extends Notification
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
        $leave = $this->leaveRequest->loadMissing(['leaveType']);
        $statusLabel = ucfirst(str_replace('_', ' ', $leave->status));

        return (new MailMessage)
            ->subject('Your Leave Request was ' . $statusLabel)
            ->view('emails.leave-status-changed', [
                'leave' => $leave,
                'notifiable' => $notifiable,
                'statusLabel' => $statusLabel,
            ]);
    }
}
