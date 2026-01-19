FROM php:8.3-fpm

# 安裝系統依賴與 PHP 擴充
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# 設定工作目錄
WORKDIR /var/www/html

# 修正權限 (確保 Web Server 可寫)
RUN chown -R www-data:www-data /var/www/html
