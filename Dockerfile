FROM php:8.2-fpm

# 安装系统依赖和扩展
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev zip libonig-dev curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 给权限
RUN chown -R www-data:www-data /var/www/html

