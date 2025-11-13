#!/bin/sh
set -e

echo "🚀 Starting Freelance Finance Hub..."

# Function to wait for PostgreSQL
wait_for_postgres() {
    echo "⏳ Waiting for PostgreSQL to be ready..."

    until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q' 2>/dev/null; do
        echo "   PostgreSQL is unavailable - sleeping"
        sleep 2
    done

    echo "✅ PostgreSQL is ready!"
}

# Function to run database migrations
run_migrations() {
    echo "🔄 Running database migrations..."
    php /app/artisan migrate --force --no-interaction
    echo "✅ Migrations completed!"
}

# Function to set up storage directories and permissions
setup_storage() {
    echo "📁 Setting up storage directories..."

    # Ensure all required directories exist
    mkdir -p /app/storage/app/temp
    mkdir -p /app/storage/app/public
    mkdir -p /app/storage/framework/cache
    mkdir -p /app/storage/framework/sessions
    mkdir -p /app/storage/framework/views
    mkdir -p /app/storage/logs
    mkdir -p /app/bootstrap/cache

    # Set proper permissions
    chown -R www-data:www-data /app/storage
    chown -R www-data:www-data /app/bootstrap/cache
    chmod -R 775 /app/storage
    chmod -R 775 /app/bootstrap/cache

    echo "✅ Storage setup completed!"
}

# Function to warm up caches
warm_caches() {
    echo "🔥 Warming up application caches..."

    # Only cache config and routes in production
    if [ "$APP_ENV" = "production" ]; then
        php /app/artisan config:cache
        php /app/artisan route:cache
        php /app/artisan view:cache
        echo "✅ Caches warmed!"
    else
        echo "ℹ️  Skipping cache warming (not in production)"
    fi
}

# Function to generate APP_KEY if not set
generate_app_key() {
    if [ -z "$APP_KEY" ]; then
        echo "🔑 Generating application key..."
        php /app/artisan key:generate --force
        echo "✅ Application key generated!"
    else
        echo "✅ Application key already set"
    fi
}

# Function to create storage link
create_storage_link() {
    echo "🔗 Creating storage symlink..."
    php /app/artisan storage:link --force 2>/dev/null || true
    echo "✅ Storage link created!"
}

# Main initialization sequence
main() {
    # Wait for database to be ready
    if [ -n "$DB_HOST" ]; then
        wait_for_postgres
    fi

    # Generate APP_KEY if needed
    generate_app_key

    # Set up storage directories
    setup_storage

    # Create storage link
    create_storage_link

    # Run migrations
    run_migrations

    # Warm up caches
    warm_caches

    echo ""
    echo "✨ Initialization completed successfully!"
    echo "🌐 Application is starting..."
    echo ""
}

# Run initialization
main

# Execute the main command (supervisord)
exec "$@"
