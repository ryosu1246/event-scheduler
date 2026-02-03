# Stage 1: Build frontend assets
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js postcss.config.js tailwind.config.js ./
RUN npm ci
COPY resources ./resources
COPY public ./public
RUN npm run build

# Stage 2: PHP application
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .
COPY --from=assets /app/public/build /var/www/public/build

RUN cp .env.example .env \
 && sed -i 's/APP_ENV=local/APP_ENV=production/' .env \
 && sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env \
 && rm -f bootstrap/cache/*.php \
 && rm -f public/hot

# --no-scripts to avoid artisan errors during build
# Generate package manifest and key via PHP directly
RUN composer install --no-dev --optimize-autoloader --no-scripts \
 && php -r " \
    require 'vendor/autoload.php'; \
    \$app = new Illuminate\Foundation\Application(getcwd()); \
    (new Illuminate\Foundation\PackageManifest( \
        new Illuminate\Filesystem\Filesystem, \
        \$app->basePath(), \
        \$app->getCachedPackagesPath() \
    ))->build(); \
    echo 'Package manifest generated'.PHP_EOL; \
"

RUN chown -R www-data:www-data storage bootstrap/cache database

CMD ["php-fpm"]
