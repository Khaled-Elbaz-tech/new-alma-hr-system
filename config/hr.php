<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HR Email Notifications
    |--------------------------------------------------------------------------
    |
    | Master switch for leave email notifications. Keep this FALSE until
    | the mail server (MAIL_* settings in .env) is configured and tested.
    | When ready: set HR_NOTIFICATIONS_ENABLED=true in .env and fill the
    | MAIL_MAILER / MAIL_HOST / MAIL_USERNAME / MAIL_PASSWORD settings.
    |
    */

    'notifications_enabled' => env('HR_NOTIFICATIONS_ENABLED', false),
];
