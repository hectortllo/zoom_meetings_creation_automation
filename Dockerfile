FROM php:7.4-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    gnupg2 \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev


#INSTALL NODEJS
RUN curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - \
    && sh -c "echo deb https://deb.nodesource.com/node_12.x hirsute main \
    > /etc/apt/sources.list.d/nodesource.list" \
    && apt-get update \
    && apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

#INSTALL extension with pecl
RUN pecl config-set php_ini "${PHP_INI_DIR}/php.ini" \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install php-soap
RUN apt-get update && \
    apt-get install -y libxml2-dev

RUN docker-php-ext-install soap

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user