## Laravel + React = vwChat

Laravel 5.8 + react.

## Installation

```
cp .env.example .env
chnage .env file db and etc. configs( APP_URL, MAIL)
php artisan key:generate
composer install
yarn install
php artisan optimize:clear
php artisan  l5-swagger:generate
php artisan  migrate:fresh --seed
```

##API
```
php artisan  l5-swagger:generate
```
url is ```/api/docs```

##Migrate
```
php artisan  migrate
php artisan  migrate:fresh //force
php artisan  migrate:fresh --seed //with seed
```


##Helpful commands
```
php artisan cache:clear 
php artisan route:clear 
php artisan config:clear
php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache
php artisan optimize:clear //equal to all
```

##Seeder
```
php artisan db:seed
```

##Yarn
```
yarn run watch
```
##Npm
```
npm run watch
```
##Laracademy Generator
```
php artisan generate:modelfromtable --table=user --folder=App\Models
```

##Websockets
```
php artisan websockets:serve
```

##Queues
```
php artisan queue:work  
```

##Solve seeder file not found problem
```
composer dump-autoload

```
