<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #8B251E, #6B1D18); color: #fff; padding: 24px;">
            <h2 style="margin: 0;">Leave Request {{ $statusLabel }}</h2>
            <p style="margin: 4px 0 0; opacity: 0.85;">Alma Travel — HR System</p>
        </div>
        <div style="padding: 24px;">
            <p>Hello {{ $notifiable->name }},</p>
            <p>Your leave request has been <strong>{{ strtolower($statusLabel) }}</strong>:</p>
            <table style="width: 100%; border-collapse: collapse;">
                <tr><td style="padding: 8px; background: #f8f9fa;"><strong>Leave Type</strong></td><td style="padding: 8px;">{{ $leave->leaveType->name ?? '-' }}</td></tr>
                <tr><td style="padding: 8px; background: #f8f9fa;"><strong>From</strong></td><td style="padding: 8px;">{{ $leave->start_date->format('d M Y') }}</td></tr>
                <tr><td style="padding: 8px; background: #f8f9fa;"><strong>To</strong></td><td style="padding: 8px;">{{ $leave->end_date->format('d M Y') }}</td></tr>
                <tr><td style="padding: 8px; background: #f8f9fa;"><strong>Days</strong></td><td style="padding: 8px;">{{ $leave->days_requested }}</td></tr>
                <tr><td style="padding: 8px; background: #f8f9fa;"><strong>Status</strong></td><td style="padding: 8px;">{{ $statusLabel }}</td></tr>
            </table>
            <p style="margin-top: 20px;">
                <a href="{{ url('/my-leaves') }}" style="background: #8B251E; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">View My Leaves</a>
            </p>
        </div>
        <div style="background: #2c3e50; color: #fff; padding: 16px 24px; font-size: 13px;">
            Alma Travel — HR System &bull; MORE THAN A JOURNEY
        </div>
    </div>
</body>
</html>
