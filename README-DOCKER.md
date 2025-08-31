# Batrip — Docker

Run the PHP (Apache) app + MySQL with Docker Compose.

## Prerequisites
- Docker Desktop (Windows/macOS) or Docker Engine (Linux)

## Quick start

```bash
# 1) Build and start (first time takes a few minutes)
docker compose up -d --build

# 2) Open the app
# Windows host: http://localhost:8080
# Linux friend: http://<server-ip>:8080

# 3) View logs
docker compose logs -f web

# 4) Stop
docker compose down
```

## Database
- Image: mysql:8.0
- Default creds (dev only):
  - DB: `batrip`
  - User: `batrip_user`
  - Pass: `batrip_pass_2024`
  - Root pass: `root`
- Initialization SQL: `database/batrip.sql` and `database/batrip_alter_users.sql` are automatically loaded on first run.

## App settings
The app auto-detects Docker env via `APP_ENV=docker`. DB env vars are passed by Compose.

If you change ports or credentials, update `docker-compose.yml` and restart.

## Notes
- The Apache doc root is `public/` (configured in `docker/apache-vhost.conf`).
- Volumes mount the repo into the container, so changes on the host reflect live.
- For production, avoid mounting the whole repo and use a multi-stage build.
