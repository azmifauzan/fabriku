# Production Server Access & Diagnostics

How to reach the production host, inspect the Fabriku container, and reproduce
web-tier behaviour. Written 2026-06-13 while diagnosing the "cancel order → 500"
report from tenant *Ockta Collection* (user `ninaherlina979@gmail.com`).

## 1. Credentials

Stored in the repo `.env` under the `INSPECT_` prefix:

```
INSPECT_SSH_HOST=43.129.52.206
INSPECT_SSH_PORT=22
INSPECT_SSH_USER=ubuntu
INSPECT_SSH_KEY=C:/Users/User/.ssh/fabriku_inspect   # git-bash: /c/Users/User/.ssh/fabriku_inspect
```

Password auth was retired on 2026-06-13 in favour of the ed25519 key
`fabriku_inspect` (public key is in the host's `~/.ssh/authorized_keys`).

## 2. SSH in (key auth)

```bash
ssh -i /c/Users/User/.ssh/fabriku_inspect \
    -o IdentitiesOnly=yes \
    ubuntu@43.129.52.206
```

The host runs several containers behind one nginx (`docker ps`). The relevant
one is **`fabriku`** (image `azmifauzan/fabriku:<tag>`).

## 3. Container layout (important!)

- App server is **Apache + mod_php**, processes run as **`www-data`**.
- Managed by **supervisord**; programs in `/etc/supervisor/conf.d/`.
- A second **root cron** also runs the scheduler — so some log lines are written
  by root and some paths run as www-data. This matters (see §6).
- Laravel app root: `/var/www/html`.
- App env at runtime is driven by the **cached config** (`bootstrap/cache/config.php`),
  NOT `.env`. At time of writing `.env` said `APP_ENV=local/APP_DEBUG=true` but the
  cached config resolved `APP_ENV=production / APP_DEBUG=false`. Always confirm with
  `config('app.debug')`, not the `.env` file.

## 4. Logs — where to actually look

| Source | Path (inside container) |
| --- | --- |
| Laravel | `storage/logs/laravel.log` (single channel, `LOG_STACK=single`) |
| Apache access | `/var/log/supervisor/apache2.log` (timestamps are **+0700 / WIB**) |
| Apache error | `/var/log/supervisor/apache2_error.log` |
| Scheduler | `/var/log/supervisor/scheduler.log` |
| Container stdout/stderr | `docker logs fabriku` |

```bash
# tail laravel log
sudo docker exec fabriku sh -c "tail -120 storage/logs/laravel.log"

# find HTTP 500s for a route (access log is WIB)
sudo docker exec fabriku sh -c "grep 'update-status' /var/log/supervisor/apache2.log | grep ' 500 '"
```

**Timezone trap:** Laravel logs in **UTC**; Apache access log is **WIB (+0700)**.
A 500 at `12:39 WIB` in the access log is `05:39 UTC` in `laravel.log`.

## 5. Running probes / inspecting data

Tinker over SSH mangles quotes badly. Instead, base64 a PHP script in and run it:

```bash
cat > /tmp/probe.php <<'PHP'
<?php
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// ... your code, use ::withoutGlobalScopes() to bypass TenantScope ...
PHP
B64=$(base64 -w0 /tmp/probe.php)
ssh ... "echo $B64 | base64 -d | sudo docker exec -i fabriku sh -c 'cat > /tmp/probe.php && php /tmp/probe.php'"
```

DB queries: bypass the tenant global scope with
`Model::withoutGlobalScopes()` and `->withTrashed()` for soft-deletes.

## 6. Reproducing WEB behaviour (the critical gotcha)

`docker exec fabriku php ...` runs as **root**. Apache runs as **www-data**.
Bugs that depend on filesystem permissions (e.g. log writes) **only reproduce
as www-data**:

```bash
sudo docker exec -u www-data fabriku php /tmp/probe.php
```

To exercise the full HTTP stack (middleware + Inertia) from a probe: build a
`Request`, `$app->instance('request',$req)`, `Auth::guard('web')->setUser($user)`,
swap `ValidateCsrfToken` for a no-op, then `$kernel->handle($req)`.
**Wrap mutations in `DB::beginTransaction()` … `DB::rollBack()`** — a probe with
no rollback WILL commit to production data.

## 7. Root cause found (2026-06-13)

`storage/logs/laravel.log` was owned **`root:root` mode 644**. Apache (www-data)
**cannot append** to it:

```
UnexpectedValueException: The stream or file ".../laravel.log" could not be
opened in append mode: Failed to open stream: Permission denied
```

`SalesOrderObserver::updated()` calls `Log::info(...)` on every status change.
Under www-data that throw propagates out of `$salesOrder->update()`, the
controller's `catch` does `DB::rollBack()` and rethrows → **HTTP 500**, and the
500 itself can't be logged (same permission) → invisible in `laravel.log`.
Hourly low-stock warnings DO appear only because the **root** cron writes them.

This breaks **all** status transitions web-side, not just cancel.

### Fix
```bash
# immediate
sudo docker exec fabriku chown -R www-data:www-data storage/logs
sudo docker exec fabriku chmod -R ug+rw storage/logs
# durable: chown storage in the Dockerfile/entrypoint, and run the scheduler
# as www-data only (drop the duplicate root cron) so the log is never recreated
# root-owned.
```
