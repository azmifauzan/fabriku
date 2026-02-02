# Laravel Schedule Monitoring Guide

## Cara Cek Schedule di Production

### 1. Melihat Daftar Scheduled Tasks

```bash
php artisan schedule:list
```

Output akan menampilkan semua scheduled tasks beserta waktu eksekusi berikutnya:
```
0 * * * *  php artisan demo:reset ........................................... Next Due: 38 minutes from now
0 9 * * *  php artisan trial:send-reminders ................................... Next Due: 17 hours from now
```

### 2. Menjalankan Scheduler di Foreground (Development)

Untuk testing, jalankan scheduler di foreground yang akan menjalankan tasks setiap menit:

```bash
php artisan schedule:work
```

**⚠️ Jangan gunakan ini di production!** Gunakan cron job di server.

### 3. Test Run Scheduled Tasks

Untuk test menjalankan semua scheduled tasks sekarang (tanpa menunggu jadwal):

```bash
php artisan schedule:run
```

Atau untuk test satu command spesifik:

```bash
php artisan demo:reset
php artisan trial:send-reminders
```

### 4. Cek Log Eksekusi

Lihat log Laravel untuk melihat apakah scheduled tasks berjalan:

```bash
# Di local (Windows)
Get-Content storage\logs\laravel.log -Tail 100

# Di production (Linux)
tail -f storage/logs/laravel.log
```

### 5. Setup Cron Job di Production

Di server production (Linux), tambahkan cron job ini:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /path/to/fabriku && php artisan schedule:run >> /dev/null 2>&1
```

**Penjelasan:**
- `* * * * *` = Jalankan setiap menit
- `cd /path/to/fabriku` = Masuk ke direktori aplikasi
- `php artisan schedule:run` = Jalankan Laravel scheduler
- `>> /dev/null 2>&1` = Buang output ke null

### 6. Verifikasi Cron Job Berjalan

Cek apakah cron job sudah terdaftar:

```bash
crontab -l
```

Cek log cron:

```bash
# Ubuntu/Debian
grep CRON /var/log/syslog

# CentOS/RHEL
grep CRON /var/log/cron
```

### 7. Monitoring dengan Supervisor (Optional)

Alternatif menggunakan Supervisor untuk menjalankan `schedule:work`:

```ini
[program:fabriku-scheduler]
process_name=%(program_name)s
command=php /path/to/fabriku/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/fabriku/storage/logs/scheduler.log
```

### 8. Cek Demo Reset Berhasil

Setelah scheduler berjalan setiap jam, verifikasi hasilnya:

```bash
php artisan tinker
```

```php
// Cek subscription expires_at semua demo tenant
$demoEmails = ['admin@konveksi.com', 'admin@kuemama.com', 'admin@crafty.com', 'admin@glowbeauty.com'];

$tenants = \App\Models\Tenant::whereHas('users', function ($query) use ($demoEmails) {
    $query->whereIn('email', $demoEmails);
})->get(['id', 'name', 'subscription_expires_at']);

$tenants->each(function($t) {
    $days = now()->diffInDays($t->subscription_expires_at, false);
    echo "{$t->name}: " . ceil($days) . " hari tersisa\n";
});

// Cek material count
\App\Models\Material::whereIn('tenant_id', $tenants->pluck('id'))->count();
```

## Scheduled Tasks di Aplikasi

### 1. Demo Data Reset (Setiap Jam)

```php
Schedule::command('demo:reset')->hourly()->withoutOverlapping();
```

**Fungsi:**
- Reset data transaksional (sales, production, preparation, inventory)
- Reset subscription ke 30 hari penuh
- Reseed material dan master data
- Berjalan untuk tenant: Konveksi Fabriku, Kue Mama Homemade, Crafty Handmade, Glow Beauty Lab

### 2. Trial Reminder Email (Setiap Hari Jam 09:00)

```php
Schedule::command('trial:send-reminders')->dailyAt('09:00')->withoutOverlapping();
```

**Fungsi:**
- Kirim email reminder 7 hari sebelum trial habis
- Kirim email reminder 3 hari sebelum trial habis
- Kirim email reminder 1 hari sebelum trial habis

## Troubleshooting

### Schedule Tidak Berjalan

1. **Cek cron job terdaftar:**
   ```bash
   crontab -l
   ```

2. **Cek permission artisan:**
   ```bash
   chmod +x artisan
   ```

3. **Cek timezone Laravel:**
   ```php
   // config/app.php
   'timezone' => 'Asia/Jakarta',
   ```

4. **Test manual:**
   ```bash
   php artisan schedule:run --verbose
   ```

### Task Berjalan Tapi Gagal

1. **Cek log:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Test command langsung:**
   ```bash
   php artisan demo:reset
   ```

3. **Cek error di log:**
   ```bash
   grep "ERROR" storage/logs/laravel.log | tail -20
   ```

## Docker Environment

Jika menggunakan Docker, pastikan scheduler berjalan di container:

```bash
# Masuk ke container
docker exec -it fabriku-app bash

# Test scheduler
php artisan schedule:list
php artisan schedule:run

# Cek apakah cron service berjalan
ps aux | grep cron

# Cek crontab terdaftar
crontab -l

# Cek log cron
cat /var/log/cron.log
tail -f /var/log/cron.log

# Cek supervisor status (jika menggunakan supervisor)
supervisorctl status

# Restart cron service jika perlu
supervisorctl restart cron

# Cek supervisor log
tail -f /var/log/supervisor/cron.log
tail -f /var/log/supervisor/cron_error.log
```

### Troubleshooting Docker Cron

**Problem: Log cron kosong meskipun crontab sudah terdaftar**

Penyebab umum:
1. Cron service tidak berjalan (check dengan `ps aux | grep cron`)
2. Permission issue pada log file
3. PATH environment tidak lengkap

Solusi:
```bash
# 1. Cek supervisor running cron
supervisorctl status cron

# 2. Restart cron
supervisorctl restart cron

# 3. Test manual schedule
php artisan schedule:run --verbose

# 4. Cek permission log
ls -la /var/log/cron.log
chmod 666 /var/log/cron.log

# 5. Force rebuild container
docker-compose down
docker-compose up -d --build
```

### Alternative: Laravel Scheduler via Supervisor

Jika cron tidak bekerja dengan baik, gunakan Laravel `schedule:work` via supervisor:

Edit `docker/supervisord.conf`, tambahkan:
```ini
[program:laravel-scheduler]
command=php /var/www/html/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/scheduler.log
```

**⚠️ Note:** `schedule:work` akan berjalan forever di foreground, cocok untuk container.

## Monitoring Production

### Email Notification (Optional)

Tambahkan email notification jika task gagal:

```php
Schedule::command('demo:reset')
    ->hourly()
    ->withoutOverlapping()
    ->onFailure(function () {
        // Kirim notifikasi jika gagal
        \Log::error('Demo reset failed!');
    });
```

### Slack Notification (Optional)

```php
Schedule::command('demo:reset')
    ->hourly()
    ->withoutOverlapping()
    ->onSuccess(function () {
        // Kirim ke Slack jika berhasil
    })
    ->onFailure(function () {
        // Kirim ke Slack jika gagal
    });
```
