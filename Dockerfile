FROM php:8.3-fpm

# 1. Устанавливаем системные зависимости
# libpq-dev нужен для pdo_pgsql
# libzip-dev нужен для zip
# libicu-dev нужен для intl
# libfreetype-dev, libjpeg62-turbo-dev, libpng-dev нужны для gd
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    git \
    acl \
    && rm -rf /var/lib/apt/lists/*

# 2. Настраиваем и устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        pdo_pgsql \
        zip \
        opcache \
        exif \
        mbstring

# 3. Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Рабочая папка
WORKDIR /var/www/html
