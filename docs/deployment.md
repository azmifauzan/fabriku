# Panduan Deployment Production

Panduan rilis manual ke server production via Docker Hub + Docker Compose. Untuk setup awal/dev, lihat `README.md` bagian Docker.

## Prasyarat

- **Docker Hub login**: `docker login` (akun `azmifauzan`, image `azmifauzan/fabriku`).
- **Credential SSH server** di `.env` lokal (file ini sudah di `.gitignore`, jangan commit). Tambahkan:
  ```bash
  DEPLOYMENT_SERVER_HOST=xxx.xxx.xxx.xxx
  DEPLOYMENT_SERVER_USERNAME=ubuntu
  DEPLOYMENT_SERVER_SSH_KEY=~/.ssh/id_rsa_fabriku
  ```
- Path docker compose di server: `/home/ubuntu/fabriku/docker-compose.yml`.
- Working tree bersih, sudah di branch `main`, sudah lolos test (`php artisan test --compact`) dan lint (`vendor/bin/pint --dirty`, `npm run lint`).

---

## 1. Build Docker image

Tag image pakai format `azmifauzan/fabriku:{ddMMyy}-{counter}` (tanggal build + nomor urut harian, mulai dari `1`).

```bash
DATE_TAG=$(date +%d%m%y)

# Cari counter berikutnya: cek tag lokal yang sudah ada untuk tanggal ini
COUNTER=1
while docker image inspect "azmifauzan/fabriku:${DATE_TAG}-${COUNTER}" >/dev/null 2>&1; do
  COUNTER=$((COUNTER + 1))
done

TAG="${DATE_TAG}-${COUNTER}"
echo "Building azmifauzan/fabriku:${TAG}"

docker build -t "azmifauzan/fabriku:${TAG}" -t azmifauzan/fabriku:latest .
```

Catatan:
- Build dari root repo (`Dockerfile` multi-stage: composer build → npm/Wayfinder build → runtime image dengan Apache + Supervisor + Queue Worker + Scheduler). Scheduler jalan via `schedule:work` (Supervisor, user `www-data`) — **tidak ada root cron**; cron sebagai root akan membuat `storage/logs/laravel.log` jadi `root:root` dan memblok write Apache (www-data) → HTTP 500 di semua perubahan status pesanan.
- Tag `latest` opsional, untuk referensi cepat — server tetap pakai tag versi spesifik (`${TAG}`), bukan `latest`, supaya rollback jelas.

---

## 2. Push ke Docker Hub

```bash
docker push "azmifauzan/fabriku:${TAG}"
docker push azmifauzan/fabriku:latest
```

---

## 3. SSH ke server deployment

Ambil credential dari `.env` lokal:

```bash
set -a; source .env; set +a

ssh -i "${DEPLOYMENT_SERVER_SSH_KEY}" "${DEPLOYMENT_SERVER_USERNAME}@${DEPLOYMENT_SERVER_HOST}"
```

Sisanya (langkah 4-7) dijalankan **di server**, di dalam `/home/ubuntu/fabriku`.

---

## 4. Update docker compose di server

Edit `image:` di `/home/ubuntu/fabriku/docker-compose.yml` ke tag baru:

```bash
cd /home/ubuntu/fabriku

# docker-compose.yml dimiliki root — perlu sudo untuk menulis
sudo sed -i "s|image: azmifauzan/fabriku:.*|image: azmifauzan/fabriku:${TAG}|" docker-compose.yml

grep "image:" docker-compose.yml   # verifikasi
```

`${TAG}` di sini harus sama dengan tag yang di-push di langkah 2 (`ddMMyy-counter`).

---

## 5. Recreate ulang docker compose

```bash
sudo docker compose pull
sudo docker compose up -d --force-recreate
```

Cek container baru jalan dengan image yang benar:

```bash
sudo docker compose ps
sudo docker inspect --format '{{.Config.Image}}' fabriku
```

---

## 6. Jalankan migrasi (jika ada migration baru)

Cek dulu apakah ada migration pending:

```bash
sudo docker compose exec fabriku php artisan migrate:status | grep -i "pending\|no"
```

Jika ada yang pending, jalankan:

```bash
sudo docker compose exec fabriku php artisan migrate --force
```

`--force` wajib karena `APP_ENV=production`. Setelah migrate, clear & rebuild cache:

```bash
sudo docker compose exec fabriku php artisan optimize:clear
sudo docker compose exec fabriku php artisan optimize
```

---

## 7. Pantau aplikasi

```bash
# Container & healthcheck status (healthy/unhealthy)
sudo docker compose ps

# Log realtime — cek error startup, queue worker, scheduler
sudo docker compose logs -f --tail=200 fabriku

# Laravel log (mungkin belum ada file jika belum ada error sejak start)
sudo docker compose exec fabriku tail -100 storage/logs/laravel.log

# Hit endpoint dari server (redirect ke https, 301 itu normal)
curl -sS -o /dev/null -w "%{http_code}\n" http://localhost/

# Hit dari luar via domain (APP_URL)
curl -sS -o /dev/null -w "%{http_code}\n" https://fabriku.web.id/
```

Aplikasi dianggap **aman** bila:
- `docker compose ps` → status `Up (healthy)`.
- `curl` ke `https://fabriku.web.id/` return `200`.
- `storage/logs/laravel.log` tidak ada `ERROR`/exception baru sejak deploy (atau file belum ada sama sekali — berarti belum ada error).
- Queue worker & scheduler (Supervisor di dalam container) tetap jalan — cek `sudo docker compose exec fabriku ps aux` (harus ada proses `artisan queue:work` dan `artisan schedule:work`).

### Rollback

Jika ada masalah, rollback dengan mengganti `image:` ke tag sebelumnya (lihat riwayat di Docker Hub / `docker images` lokal), lalu ulangi langkah 5. Jika migration baru menyebabkan masalah dan butuh rollback schema, jalankan `sudo docker compose exec fabriku php artisan migrate:rollback --force` sebelum downgrade image.
