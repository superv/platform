#!/bin/bash

php artisan queue:restart
php artisan queue:work --queue=superv-high,superv-low --tries=1 --timeout=600 --daemon&
php artisan queue:work --queue=superv-default --tries=1 --daemon&
