# Queue Worker Setup for Development

## Problem
Model installations and other background jobs require a queue worker to be running. Without it, jobs get queued but never execute, and the GUI doesn't update.

## Solution

### Option 1: Manual (Temporary - stops when you close terminal)
```bash
./vendor/bin/sail artisan queue:work
```

### Option 2: Background Process (Better - but stops on Docker restart)
```bash
./vendor/bin/sail artisan queue:work --daemon &
```

### Option 3: Add to Sail Container (Best - persistent across restarts)

Add a queue worker service to your `compose.yaml`:

```yaml
services:
    laravel.test:
        # ... existing config ...

    # Add this new service
    queue:
        build:
            context: './vendor/laravel/sail/runtimes/8.4'
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: 'sail-8.4/app'
        extra_hosts:
            - 'host.docker.internal:host-gateway'
            - 'jens-pc.local:192.168.178.74'
            - 'jens.pc.local:192.168.178.74'
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - pgsql
            - laravel.test
        command: php artisan queue:work --verbose --tries=3 --timeout=3600
        restart: unless-stopped

    pgsql:
        # ... existing config ...
```

Then restart:
```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

## Checking Queue Status

### See running workers
```bash
ps aux | grep "queue:work"
```

### View queue jobs
```bash
./vendor/bin/sail artisan queue:monitor
```

### Check for failed jobs
```bash
./vendor/bin/sail artisan queue:failed
```

### Retry failed jobs
```bash
./vendor/bin/sail artisan queue:retry all
```

## Current Setup (Fixed)

✅ **Ollama Connection**: Docker container can now reach `jens.pc.local:11434` via extra_hosts mapping
✅ **Queue Worker**: Running manually (Option 2) - needs to be restarted after container restarts
✅ **Model Detection**: Working - shows 5 text models and 1 vision model

## Installed Models on jens.pc.local

**Text Models:**
- gemma:2b
- qwen3-coder:30b
- **gpt-oss:20b** ✅ (Recommended)
- gemma3:latest
- codestral:latest

**Vision Models:**
- granite3.2-vision:latest

**Recommended but not installed:**
- qwen2.5vl:3b (vision model for document extraction)

You can install it via the Settings → Integrationen page and the progress will now update live! 🎉
