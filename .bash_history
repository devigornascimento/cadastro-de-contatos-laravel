curl -s https://laravel.build/api-contatos | bash
cd api-contatos
ls
./vendor/bin/sail up -d
./vendor/bin/sail composer require laravel/breeze --dev
./vendor/bin/sail artisan breeze:install api
explorer.exe .
./vendor/bin/sail artisan make:model Contact -m
./vendor/bin/sail artisan migrate:fresh
./vendor/bin/sail artisan db:show
./vendor/bin/sail artisan model:show User
./vendor/bin/sail artisan migrate:fresh
./vendor/bin/sail artisan model:show User
