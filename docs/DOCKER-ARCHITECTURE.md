# 🐳 Single Container Architecture

> **Last Updated**: February 3, 2026

## Mengapa Single Container?

✅ **Hemat Resource** - 1 container vs 3 containers  
✅ **Lebih Sederhana** - Satu image, satu build  
✅ **Efficient** - Supervisor mengelola semua proses  
✅ **Production Ready** - Tested dan optimized  

---

## Container Architecture

```
┌─────────────────────────────────────────┐
│     Fabriku Container (fabriku)         │
│                                         │
│  ┌───────────────────────────────────┐ │
│  │       Supervisor (PID 1)          │ │
│  └───────────────────────────────────┘ │
│           │        │         │          │
│           ▼        ▼         ▼          │
│      ┌──────┐ ┌──────┐ ┌──────────┐   │
│      │Apache│ │ Cron │ │  Queue   │   │
│      │:80   │ │Worker│ │  Worker  │   │
│      └──────┘ └──────┘ └──────────┘   │
│                                         │
│  Services:                              │
│  • Apache 2.4 (Web Server)              │
│  • Cron (Laravel Scheduler)             │
│  • Queue Worker (Background Jobs)       │
│                                         │
│  Stack:                                 │
│  • PHP 8.4.11                           │
│  • Laravel 12.47                        │
│  • Node.js 20 (build only)              │
│                                         │
└─────────────────────────────────────────┘
              │
              ▼
     ┌─────────────────┐
     │  External Deps  │
     ├─────────────────┤
     │ PostgreSQL:5432 │
     │ Redis:6379      │
     └─────────────────┘
```

---

## Production Setup (1 Container)

```yaml
services:
  app:  # Single container
    - Apache (Web Server)
    - Cron (Scheduler)
    - Queue Worker (Jobs)
```

**External:**
- PostgreSQL (host)
- Redis (host)

**Resource:** ~200-300MB RAM

---

## Development Setup (3 Containers)

```yaml
services:
  app:      # Application (Apache + Cron + Queue)
  db:       # PostgreSQL 16
  redis:    # Redis 7
```

**Resource:** ~400-500MB RAM (all included)

---

## Process Management

Supervisor configuration handles all processes:

```ini
[supervisord]
nodaemon=true

[program:apache2]      # Priority 10
command=/usr/sbin/apache2ctl -D FOREGROUND

[program:cron]         # Priority 20  
command=/usr/sbin/cron -f

[program:laravel-worker]  # Priority 30
command=php artisan queue:work
numprocs=1
```

---

## Comparison: Single vs Multiple Containers

### Single Container (Kami Gunakan)
```
✅ 1 image build
✅ 1 container running
✅ 200-300MB RAM
✅ Simple management
✅ Supervisor handles all
```

### Multiple Containers (Alternatif)
```
❌ 3 image builds (same image)
❌ 3 containers running
❌ 400-500MB RAM
❌ Complex orchestration
❌ More overhead
```

---

## Commands

```bash
# Production (1 container)
docker compose up -d                    # Start
docker compose logs -f app              # Logs (all services)
docker compose exec app bash            # Shell access

# Check processes inside container
docker compose exec app supervisorctl status

# Restart specific service
docker compose exec app supervisorctl restart apache2
docker compose exec app supervisorctl restart laravel-worker

# View cron logs
docker compose exec app tail -f /var/log/cron.log

# View supervisor logs
docker compose exec app tail -f /var/log/supervisor/apache2.log
docker compose exec app tail -f /var/log/supervisor/laravel-worker.log
```

---

## Resource Monitoring

```bash
# Check container resource usage
docker stats fabriku

# Expected usage:
# - CPU: 1-5%
# - RAM: 200-300MB
# - Network: depends on traffic
```

---

## Benefits

1. **Cost Effective** - Hemat RAM dan CPU
2. **Simple Deployment** - Satu docker compose up
3. **Easy Monitoring** - Satu container untuk di-monitor
4. **Faster Startup** - Tidak perlu wait for multiple containers
5. **Production Proven** - Pattern yang umum digunakan

---

## Files

```
fabriku/
├── Dockerfile                    # Single image definition
├── docker-compose.yml            # 1 container (production)
├── docker-compose.dev.yml        # 3 containers (development)
├── docker/
│   ├── supervisord.conf          # Process management
│   └── entrypoint.sh             # Startup script
└── .env.docker.example           # Configuration
```

---

## Quick Start

```bash
# Production
docker compose up -d

# Development  
docker compose -f docker-compose.dev.yml up -d
```

Both configurations use the same Dockerfile and single container approach for the app! 🚀
