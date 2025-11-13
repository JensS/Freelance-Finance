# Deployment Guide - Freelance Finance Hub

This guide covers deploying Freelance Finance Hub to production using Docker and Dokploy with an external PostgreSQL database.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Architecture Overview](#architecture-overview)
3. [Environment Configuration](#environment-configuration)
4. [Building the Docker Image](#building-the-docker-image)
5. [Deploying with Dokploy](#deploying-with-dokploy)
6. [External PostgreSQL Setup](#external-postgresql-setup)
7. [Post-Deployment Tasks](#post-deployment-tasks)
8. [Health Checks](#health-checks)
9. [Troubleshooting](#troubleshooting)

## Prerequisites

- Docker 20.10 or higher
- Dokploy server with Docker support
- PostgreSQL 16+ database (can be external/shared)
- Access to Paperless-ngx server (optional but recommended)
- AI provider credentials (Ollama, OpenAI, Anthropic, or OpenRouter)

## Architecture Overview

The application is packaged as a multi-stage Docker image that includes:

- **Nginx** - Web server (port 80)
- **PHP-FPM** - PHP application server
- **Supervisor** - Process manager that runs:
  - Nginx
  - PHP-FPM
  - 2x Queue Workers
  - Laravel Scheduler (cron alternative)

### Container Services

```
┌─────────────────────────────────────┐
│         Supervisor                  │
│  ┌──────────────────────────────┐  │
│  │ Nginx (port 80)              │  │
│  └──────────────────────────────┘  │
│  ┌──────────────────────────────┐  │
│  │ PHP-FPM (port 9000)          │  │
│  └──────────────────────────────┘  │
│  ┌──────────────────────────────┐  │
│  │ Queue Worker 1 & 2           │  │
│  └──────────────────────────────┘  │
│  ┌──────────────────────────────┐  │
│  │ Scheduler (every 60s)        │  │
│  └──────────────────────────────┘  │
└─────────────────────────────────────┘
           ↓ connects to
┌─────────────────────────────────────┐
│   External PostgreSQL Database      │
└─────────────────────────────────────┘
```

## Environment Configuration

### Required Environment Variables

Create a `.env` file based on `.env.example` with the following **required** settings:

```bash
# Application
APP_NAME="Freelance Finance Hub"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY  # Generate with: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_PASSWORD=your_secure_password

# Database (External PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=postgres_container_name  # Or IP address of PostgreSQL server
DB_PORT=5432
DB_DATABASE=freelance_finance
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Paperless Integration
PAPERLESS_URL=http://your-paperless-server:8000/
PAPERLESS_API_TOKEN=your_paperless_api_token
```

### AI Provider Configuration

Choose **one primary provider** and optionally configure a fallback:

#### Option 1: Ollama (Local/Self-Hosted)

```bash
AI_PROVIDER=ollama
OLLAMA_API_URL=http://your-ollama-server:11434
# For Docker internal: http://host.docker.internal:11434
```

#### Option 2: OpenAI

```bash
AI_PROVIDER=openai
OPENAI_API_KEY=sk-your-openai-api-key
OPENAI_MODEL=gpt-4o  # or gpt-4o-mini, gpt-4-turbo
```

#### Option 3: Anthropic (Claude)

```bash
AI_PROVIDER=anthropic
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
```

#### Option 4: OpenRouter

```bash
AI_PROVIDER=openrouter
OPENROUTER_API_KEY=sk-or-your-openrouter-key
OPENROUTER_MODEL=anthropic/claude-3.5-sonnet
```

#### Fallback Provider (Optional)

Set a fallback provider in case the primary fails:

```bash
AI_FALLBACK_PROVIDER=openai  # or anthropic, openrouter, none
```

### Optional Settings

```bash
# Caching (recommended for production)
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

## Building the Docker Image

### Local Build

```bash
# Build the image
docker build -t freelance-finance-hub:latest .

# Test locally
docker run -p 8080:80 \
  --env-file .env \
  freelance-finance-hub:latest
```

### Multi-Architecture Build (Optional)

For ARM-based servers (e.g., AWS Graviton):

```bash
docker buildx create --use
docker buildx build --platform linux/amd64,linux/arm64 \
  -t freelance-finance-hub:latest \
  --push .
```

## Deploying with Dokploy

### Method 1: Using Dokploy UI

1. **Create New Application**
   - Go to Dokploy dashboard
   - Click "New Application"
   - Select "Docker Compose" or "Dockerfile"

2. **Configure Git Repository**
   - Connect your Git repository
   - Set branch to `master` or `main`
   - Set Dockerfile path: `./Dockerfile`

3. **Set Environment Variables**
   - Add all required environment variables from `.env.example`
   - Make sure to set `APP_KEY`, `APP_PASSWORD`, database credentials

4. **Configure Networking**
   - **If using shared PostgreSQL:**
     - Add app to PostgreSQL network
     - Set `DB_HOST` to PostgreSQL container name
   - **If using external PostgreSQL:**
     - Set `DB_HOST` to server IP/hostname

5. **Set Volumes** (Important for data persistence)
   ```
   /app/storage/app -> storage_data
   /app/storage/logs -> storage_logs
   /app/storage/framework -> storage_framework
   ```

6. **Deploy**
   - Click "Deploy"
   - Monitor logs for initialization messages

### Method 2: Using docker-compose.yml

1. **Edit docker-compose.yml** for your environment:

```yaml
# If using Dokploy's shared PostgreSQL, edit the networks section:
networks:
  postgres_network:
    external: true
    name: your_postgres_network_name  # Get this from Dokploy

# And comment out the postgres service:
# postgres:
#   image: postgres:16-alpine
#   ...
```

2. **Remove depends_on** if using external database:

```yaml
services:
  app:
    # Remove this line if using external database:
    # depends_on:
    #   - postgres
```

3. **Deploy with Dokploy**:
   - Upload `docker-compose.yml` to Dokploy
   - Set environment variables
   - Deploy

## External PostgreSQL Setup

### Using Dokploy's Shared PostgreSQL

1. **Find PostgreSQL Network Name**
   ```bash
   docker network ls | grep postgres
   ```

2. **Update docker-compose.yml**
   ```yaml
   networks:
     postgres_network:
       external: true
       name: dokploy_postgres_network  # Use actual network name
   ```

3. **Set Database Environment Variables**
   ```bash
   DB_HOST=postgres  # Or PostgreSQL container name
   DB_PORT=5432
   DB_DATABASE=freelance_finance
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

### Using External PostgreSQL Server

1. **Create Database**
   ```sql
   CREATE DATABASE freelance_finance;
   CREATE USER freelance_user WITH PASSWORD 'secure_password';
   GRANT ALL PRIVILEGES ON DATABASE freelance_finance TO freelance_user;
   ```

2. **Allow Remote Connections** (if needed)

   Edit `postgresql.conf`:
   ```
   listen_addresses = '*'
   ```

   Edit `pg_hba.conf`:
   ```
   host    freelance_finance    freelance_user    172.16.0.0/12    md5
   ```

3. **Set Environment Variables**
   ```bash
   DB_HOST=your-postgres-server.com
   DB_PORT=5432
   DB_DATABASE=freelance_finance
   DB_USERNAME=freelance_user
   DB_PASSWORD=secure_password
   ```

## Post-Deployment Tasks

### 1. Verify Health Check

The application includes a health check endpoint:

```bash
curl http://your-domain.com/up
# Should return: OK
```

### 2. Check Logs

Monitor application startup:

```bash
docker logs -f container_name
```

Look for:
- ✅ PostgreSQL is ready!
- ✅ Migrations completed!
- ✅ Storage setup completed!
- ✨ Initialization completed successfully!

### 3. Access Application

Navigate to `https://your-domain.com` and log in with `APP_PASSWORD`.

### 4. Configure Settings

1. Go to **Settings** (gear icon)
2. Configure:
   - Company information
   - Bank details
   - Tax numbers
   - Paperless storage path
   - AI provider (if not set in .env)

### 5. Seed Initial Data (Optional)

If starting fresh:

```bash
docker exec -it container_name php /app/artisan db:seed
```

## Health Checks

The Docker image includes built-in health checks:

- **Endpoint**: `GET /up`
- **Interval**: Every 30 seconds
- **Timeout**: 3 seconds
- **Start Period**: 40 seconds (allows for initialization)

Check health status:

```bash
docker ps  # Look for "healthy" status
```

## Troubleshooting

### Database Connection Issues

**Symptom**: "SQLSTATE[08006] connection refused"

**Solutions**:
1. Verify PostgreSQL is running:
   ```bash
   docker ps | grep postgres
   ```

2. Check network connectivity:
   ```bash
   docker exec -it app_container ping postgres_container
   ```

3. Verify database credentials in environment variables

4. Check PostgreSQL logs:
   ```bash
   docker logs postgres_container
   ```

### Permission Errors

**Symptom**: "Permission denied" errors in logs

**Solution**: Recreate storage directories:
```bash
docker exec -it container_name sh -c "
  chown -R www-data:www-data /app/storage /app/bootstrap/cache
  chmod -R 775 /app/storage /app/bootstrap/cache
"
```

### Queue Not Processing

**Symptom**: Queued jobs not running

**Solution**: Check queue workers are running:
```bash
docker exec -it container_name supervisorctl status
```

Should show:
- `queue-worker:queue-worker_00 RUNNING`
- `queue-worker:queue-worker_01 RUNNING`

Restart workers if needed:
```bash
docker exec -it container_name supervisorctl restart queue-worker:*
```

### AI Vision Not Working

**Symptom**: AI extraction fails or returns errors

**Solutions**:

1. **For Ollama**:
   - Verify Ollama server is accessible:
     ```bash
     curl http://your-ollama-server:11434/api/tags
     ```
   - Install required vision model:
     ```bash
     docker exec -it ollama_container ollama pull llama3.2-vision
     ```

2. **For OpenAI/Anthropic/OpenRouter**:
   - Verify API key is correct
   - Check API quota/billing
   - Review logs for specific error messages

### Scheduler Not Running

**Symptom**: Scheduled tasks not executing

**Solution**: Verify scheduler process:
```bash
docker exec -it container_name supervisorctl status schedule
```

Should show `RUNNING`. Check logs:
```bash
docker exec -it container_name tail -f /app/storage/logs/scheduler.log
```

### Cache Issues After Deployment

**Symptom**: Configuration changes not reflecting

**Solution**: Clear all caches:
```bash
docker exec -it container_name php /app/artisan optimize:clear
docker exec -it container_name php /app/artisan config:cache
docker exec -it container_name php /app/artisan route:cache
```

### Container Keeps Restarting

**Symptom**: Container status shows "Restarting"

**Solutions**:
1. Check container logs:
   ```bash
   docker logs container_name
   ```

2. Common causes:
   - Missing `APP_KEY` (auto-generated on first run)
   - Database connection failure
   - Missing required environment variables
   - Port 80 already in use

3. Disable auto-restart to debug:
   ```bash
   docker update --restart=no container_name
   ```

## Security Considerations

### Production Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_PASSWORD` set
- [ ] `APP_KEY` generated and set
- [ ] Database uses non-default credentials
- [ ] SSL/TLS certificate configured (use Dokploy's built-in Let's Encrypt)
- [ ] Paperless API token kept secure
- [ ] AI provider API keys kept secure
- [ ] Regular database backups configured
- [ ] Log monitoring enabled

### Backup Strategy

1. **Database Backups**:
   ```bash
   docker exec postgres_container pg_dump -U user dbname > backup.sql
   ```

2. **Storage Backups**:
   ```bash
   docker cp container_name:/app/storage/app ./storage-backup
   ```

3. **Automated Backups**: Configure Dokploy or cron jobs for regular backups

## Updating the Application

### Rolling Update

1. Pull latest code from Git (Dokploy auto-builds)
2. Or build new image:
   ```bash
   docker build -t freelance-finance-hub:v2 .
   ```

3. Deploy new version:
   - Dokploy: Click "Redeploy"
   - Manual: Update docker-compose.yml and `docker-compose up -d`

4. Run migrations if needed:
   ```bash
   docker exec -it container_name php /app/artisan migrate --force
   ```

### Zero-Downtime Update

Use Dokploy's blue-green deployment feature or manually:

1. Start new container
2. Run health checks
3. Switch traffic to new container
4. Stop old container

## Support

For issues or questions:

- Check logs: `docker logs container_name`
- Review this troubleshooting guide
- Check Laravel logs: `/app/storage/logs/laravel.log`
- Supervisor logs: `/var/log/supervisor/`

## Monitoring

Recommended monitoring:

- **Health endpoint**: Monitor `GET /up` (should return 200 OK)
- **Log aggregation**: Ship logs to external service (e.g., Papertrail, Logtail)
- **Resource monitoring**: Monitor CPU, memory, disk usage
- **Database monitoring**: Monitor PostgreSQL connections, query performance
- **Backup verification**: Regular restore tests

## Performance Tuning

### PHP-FPM Optimization

Edit `docker/php-fpm.conf` based on server resources:

```ini
pm.max_children = 50      # Adjust based on available RAM
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
```

### OPcache Settings

Edit `docker/php.ini`:

```ini
opcache.memory_consumption = 256  # Increase if needed
opcache.max_accelerated_files = 20000
```

### Database Connection Pool

For high traffic, consider using PgBouncer as a connection pooler.

---

**Deployment Complete!** 🚀

Your Freelance Finance Hub should now be running in production. Access it at your configured `APP_URL` and log in with your `APP_PASSWORD`.
