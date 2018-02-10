#!/bin/bash

echo "superv-mono"| xargs -I {} sh -c "mysql -Nse 'show tables' {}| xargs -I[] mysql -e 'SET FOREIGN_KEY_CHECKS=0; drop table []' {}"

sed -i '' 's/^SV_INSTALLED=true/SV_INSTALLED=false/g' .env

php artisan superv:install
php artisan droplet:install superv.supreme --path=droplets/superv/droplets/supreme
php artisan droplet:install themes.tailwind --path=droplets/superv/themes/tailwind
#php artisan superv:install --modules=acp,web,ui,nucleo,supreme
#php artisan acp:create-user "Ali Selcuk" maselcuk@gmail.com secret