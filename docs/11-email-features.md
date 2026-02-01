# Email Features Documentation

## Overview

Aplikasi Fabriku memiliki sistem email yang lengkap dengan desain custom berbahasa Indonesia yang konsisten dengan branding Fabriku untuk meningkatkan user experience dan komunikasi dengan pengguna.

## Custom Email Templates

Semua email menggunakan template custom dengan:
- **Bahasa Indonesia** untuk semua konten
- **Logo dan branding Fabriku** dengan gradient purple (#667eea → #764ba2)
- **Desain responsif** yang konsisten
- **User-friendly layout** dengan CTA button yang jelas

## Fitur-Fitur Email

### 1. Email Verification (Verifikasi Email)

Email verifikasi custom dalam bahasa Indonesia yang dikirim setelah registrasi.

**Karakteristik:**
- Subject: "Verifikasi Alamat Email Anda"
- Gradient header purple dengan logo Fabriku
- Tombol CTA "Verifikasi Email Saya"
- Link alternatif untuk browser yang tidak support button
- Warning tentang expirasi 60 menit
- Tips keamanan

**Flow:**
1. User mendaftar → email verifikasi custom otomatis dikirim (bahasa Indonesia)
2. User klik tombol "Verifikasi Email Saya" di email
3. Email terverifikasi → Welcome email dikirim
4. User dapat akses dashboard

**Template:** 
- Mailable: `app/Mail/VerifyEmail.php`
- Notification: `app/Notifications/VerifyEmailNotification.php`
- View: `resources/views/emails/verify-email.blade.php`

**Routes:**
- `GET /verify-email` - Halaman pemberitahuan verifikasi
- `GET /verify-email/{id}/{hash}` - Endpoint verifikasi (signed URL)
- `POST /email/verification-notification` - Kirim ulang email verifikasi

**Testing:**
```bash
php artisan test --filter=EmailVerification
```

### 2. Welcome Email

Email selamat datang dalam bahasa Indonesia dikirim otomatis setelah user berhasil verifikasi email.

**Konten:**
- Ucapan selamat datang
- Detail trial (30 hari)
- Link ke dashboard
- Panduan langkah selanjutnya

**Template:** 
- Mailable: `app/Mail/WelcomeEmail.php`
- View: `resources/views/emails/welcome.blade.php`

### 3. Reset Password (Lupa Password)

Email reset password custom dalam bahasa Indonesia dengan desain yang user-friendly.

**Karakteristik:**
- Subject: "Reset Password Akun Anda"
- Gradient header purple dengan logo Fabriku
- Tombol CTA "Reset Password"
- Link alternatif untuk browser yang tidak support button
- Warning tentang expirasi 60 menit
- Tips keamanan password
- Pesan jika user tidak request reset

**Flow:**
1. User klik "Lupa Password?"
2. Masukkan email → email reset custom dikirim (bahasa Indonesia)
3. User klik tombol "Reset Password" di email
4. Redirect ke form reset password
5. User masukkan password baru
6. Password berhasil direset → redirect ke login

**Template:**
- Mailable: `app/Mail/ResetPasswordEmail.php`
- Notification: `app/Notifications/ResetPasswordNotification.php`
- View: `resources/views/emails/reset-password.blade.php`

**Routes:**
- `GET /forgot-password` - Form request reset password
- `POST /forgot-password` - Kirim email reset password
- `GET /reset-password/{token}` - Form reset password
- `POST /reset-password` - Proses reset password

**Testing:**
```bash
php artisan test --filter=PasswordReset
```

### 4. Trial Reminder Emails

Email pengingat otomatis untuk akun trial yang akan habis.

**Schedule:**
- **7 hari sebelum** - Reminder pertama
- **3 hari sebelum** - Reminder kedua
- **1 hari sebelum** - Reminder terakhir

**Konten:**
- Informasi sisa waktu trial
- Detail paket langganan (bulanan/tahunan)
- Link upgrade subscription
- Manfaat upgrade

**Template:** `resources/views/emails/trial-reminder.blade.php`

**Command:**
```bash
php artisan trial:send-reminders
```

**Scheduled:** Setiap hari jam 09:00 pagi (otomatis via Laravel Scheduler)

**Testing:**
```bash
php artisan test --filter=SendTrialReminders
```

## Konfigurasi Email

### Environment Variables

Update file `.env` dengan kredensial email:

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@fabriku.my.id"
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration

Semua email dikirim via queue untuk performa yang lebih baik.

**Jalankan queue worker:**
```bash
php artisan queue:work
```

**Di production (via Supervisor):**
Queue worker akan berjalan otomatis melalui Supervisord di dalam Docker container.

## Database Changes

### Migration: `add_trial_notification_fields_to_tenants_table`

Menambahkan field tracking untuk trial reminder emails:
- `trial_reminder_7days_sent_at`
- `trial_reminder_3days_sent_at`
- `trial_reminder_1day_sent_at`

**Run migration:**
```bash
php artisan migrate
```

## Docker Configuration

Docker Compose telah diupdate untuk include email configuration:

**docker-compose.yml & docker-compose.dev.yml:**
- Environment variables untuk SMTP
- Mendukung queue worker via Supervisord

## Laravel Scheduler

Untuk menjalankan scheduled tasks (trial reminders), pastikan cron job berjalan:

**Di server production:**
```cron
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

**Di Docker:**
Scheduler sudah diatur otomatis via Supervisord dalam container.

## Testing Email Development

### Menggunakan Mailpit (Development)

Install Mailpit untuk test email lokal:

```yaml
# docker-compose.dev.yml
mailpit:
  image: axllent/mailpit
  ports:
    - "1025:1025"  # SMTP
    - "8025:8025"  # Web UI
```

Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

Akses web UI: http://localhost:8025

### Manual Testing

**Test email verification:**
```bash
php artisan tinker
$user = User::factory()->unverified()->create();
$user->sendEmailVerificationNotification();
```

**Test password reset:**
```bash
php artisan tinker
Password::sendResetLink(['email' => 'user@example.com']);
```

**Test trial reminder:**
```bash
php artisan trial:send-reminders
```

## Troubleshooting

### Email tidak terkirim

1. **Check queue:** 
   ```bash
   php artisan queue:work
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Test SMTP connection:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```

### Email masuk spam

- Pastikan SPF, DKIM, dan DMARC sudah dikonfigurasi di DNS
- Gunakan email verified sender di Brevo
- Gunakan domain reputation yang baik

### Rate limiting

Brevo free tier memiliki limit:
- 300 emails/hari
- Perlu upgrade jika lebih

## Best Practices

1. **Always queue emails** - Jangan kirim sync untuk performa
2. **Monitor email deliverability** - Check bounce & complaint rate
3. **Test di staging** - Jangan test di production
4. **Use descriptive subjects** - Jelas dan menarik
5. **Mobile-friendly templates** - Responsive design
6. **Include unsubscribe link** - (untuk marketing emails)
7. **Track important metrics** - Open rate, click rate, bounce rate

## Security Considerations

1. **Signed URLs** - Email verification menggunakan signed URLs
2. **Token expiration** - Password reset tokens expire dalam 60 menit
3. **Rate limiting** - Throttling untuk resend verification & password reset
4. **Email verification required** - Middleware `verified` untuk protected routes
5. **Secure SMTP** - Menggunakan TLS encryption

## Future Enhancements

Possible improvements:
- Email templates dengan branding lebih baik
- Analytics tracking (open rate, click rate)
- Email preferences/unsubscribe management
- Multi-language email templates
- Admin notification emails
- Invoice/receipt emails
- Activity digest emails
