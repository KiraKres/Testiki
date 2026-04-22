FROM php:7.4-fpm
# Установка библиопек
RUN apt-get update && apt-get install -y libpng-dev libzip-dev zip unzip && docker-php-ext-install pdo pdo_mysql zip gd
# Ласт для Yii2
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
# фреймворк Yii2
# docker-compose exec php composer create-project --prefer-dist yiisoft/yii2-app-basic .
#  docker-compose exec php php /var/www/html/yii migrate - служебная таблица для сохранения изменений