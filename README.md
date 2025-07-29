composer install

php artisan key:generate --force

php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan passport:keys --force
php artisan passport:client --password

copier les cles et remplacer ca respectivement au niveau des cles values

PASSPORT_GRANT_ACCESS_CLIENT_ID="client_id"
PASSPORT_GRANT_ACCESS_CLIENT_SECRET="client_secret"