#!/bin/bash

BRANCH=${1:-'master'}
SERVICE_NUMBER=${2:-'1'}

git pull origin "$BRANCH"

docker-compose exec -T fpm$SERVICE_NUMBER composer install
docker-compose exec -T fpm$SERVICE_NUMBER composer require zanysoft/laravel-zip
docker-compose exec -T fpm$SERVICE_NUMBER php artisan migrate
docker-compose exec -T fpm$SERVICE_NUMBER php artisan tenancy:migrate
docker-compose exec -T fpm$SERVICE_NUMBER php artisan cache:clear
docker-compose exec -T fpm$SERVICE_NUMBER php artisan config:cache
docker-compose exec -T fpm$SERVICE_NUMBER chmod -R 777 vendor/mpdf/mpdf

#nota habilitar extension extension=intl en docker está en el php.ini del contenedor php-fpm
#en el contenedror php-fpm ejecutar
    apt-get update -y
    apt-get install -y php-intl
#luego reiniciar nginx en el contenedr nginx con
    service nginx restart

# ejecutar
    composer require mike42/escpos-php




