# Setup Email Features

## Quick Start

1. **Update .env file:**
```bash
cp .env.example .env
```

Edit `.env` dan set:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=976f25001@smtp-brevo.com
MAIL_PASSWORD=9NXWKUZqj2ypIPb6
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@fabriku.my.id"
MAIL_FROM_NAME="Fabriku"
```

2. **Run migrations:**
```bash
php artisan migrate
```

3. **Start queue worker:**
```bash
php artisan queue:work
```

4. **Setup Laravel Scheduler (Production):**
Add to crontab:
```cron
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Docker Setup

**Using docker-compose:**
```bash
# Development
docker compose -f docker-compose.dev.yml up -d

# Production
docker compose up -d
```

Email configuration is already included in docker-compose files.

## Testing

Run all email tests:
```bash
php artisan test --filter=Email
php artisan test --filter=Password
php artisan test --filter=TrialReminders
```

## Manual Testing Commands

**Test trial reminder command:**
```bash
php artisan trial:send-reminders
```

**Test email in tinker:**
```bash
php artisan tinker

# Test verification email
$user = User::factory()->unverified()->create();
$user->sendEmailVerificationNotification();

# Test password reset
Password::sendResetLink(['email' => 'user@example.com']);
```

## Features Included

✅ Email Verification after registration  
✅ Welcome Email after activation  
✅ Forgot Password functionality  
✅ Trial Reminder Emails (7, 3, 1 days before expiration)  
✅ Responsive email templates  
✅ Queued email sending  
✅ Comprehensive test coverage  

## Documentation

Full documentation: [docs/11-email-features.md](docs/11-email-features.md)

## Troubleshooting

**Emails not sending?**
1. Make sure queue worker is running: `php artisan queue:work`
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify SMTP credentials in `.env`

**Trial reminders not working?**
1. Ensure scheduler is running (cron job configured)
2. Run manually: `php artisan trial:send-reminders`
3. Check tenant has active trial and admin user

## Support

For issues or questions, check the documentation or contact the development team.
