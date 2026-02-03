<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram bot's access token from @BotFather.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Username
    |--------------------------------------------------------------------------
    |
    | Your Telegram bot's username (without @).
    |
    */
    'bot_username' => env('TELEGRAM_BOT_USERNAME', 'FabrikuBot'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL where Telegram will send updates.
    | This should be your public URL + /api/telegram/webhook
    |
    */
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL', env('APP_URL').'/api/telegram/webhook'),

    /*
    |--------------------------------------------------------------------------
    | Admin Telegram ID
    |--------------------------------------------------------------------------
    |
    | Telegram chat ID for admin notifications.
    |
    */
    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Secret Token for Webhook
    |--------------------------------------------------------------------------
    |
    | A secret token to verify that incoming requests are from Telegram.
    |
    */
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
];
