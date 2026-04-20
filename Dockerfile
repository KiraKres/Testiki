FROM php:7.4-fpm
# Установка библиопек
RUN apt-get update && apt-get install -y libpng-dev libzip-dev zip unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# фреймворк Yii2
# docker-compose exec php composer create-project --prefer-dist yiisoft/yii2-app-basic .