#!/bin/bash

# Health check script for Personal Notes System

# Check if Nginx is running
if ! pgrep nginx > /dev/null; then
    echo "Nginx is not running"
    exit 1
fi

# Check if PHP-FPM is running
if ! pgrep php-fpm > /dev/null; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if the application responds
if ! curl -f http://localhost/health > /dev/null 2>&1; then
    echo "Application health check failed"
    exit 1
fi

# Check database connection
if ! php -r "
try {
    \$pdo = new PDO('mysql:host=mysql-service;dbname=personal', 'root', \$_ENV['DB_PASSWORD'] ?? '');
    echo 'Database connection successful';
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage();
    exit(1);
}
" > /dev/null 2>&1; then
    echo "Database connection failed"
    exit 1
fi

# Check Redis connection
if ! php -r "
try {
    \$redis = new Redis();
    \$redis->connect('redis-service', 6379);
    if (isset(\$_ENV['REDIS_PASSWORD'])) {
        \$redis->auth(\$_ENV['REDIS_PASSWORD']);
    }
    \$redis->ping();
    echo 'Redis connection successful';
} catch (Exception \$e) {
    echo 'Redis connection failed: ' . \$e->getMessage();
    exit(1);
}
" > /dev/null 2>&1; then
    echo "Redis connection failed"
    exit 1
fi

echo "All health checks passed"
exit 0
