composer install

php artisan key:generate --force
chmod 660 app/secrets/oauth/oauth-public.key app/secrets/oauth/oauth-private.key
chown <web-user>:<web-group> app/secrets/oauth/oauth-public.key app/secrets/oauth/oauth-private.key

php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan passport:keys --force
php artisan passport:client --password

copier les cles et remplacer ca respectivement au niveau des cles values

PASSPORT_GRANT_ACCESS_CLIENT_ID="client_id"
PASSPORT_GRANT_ACCESS_CLIENT_SECRET="client_secret"

01989169-a0f1-7341-9c97-f0e89666b2b6
spVAGmWRaWURlVygrn69TTVQ3Lw3wu2Oi3lsufDS

Gerer les problemes de permissions au niveau de passport 
mkdir -p app/secrets/oauth

chmod 660 app/secrets/oauth/oauth-public.key app/secrets/oauth/oauth-private.key
chown nobody:nogroup app/secrets/oauth/oauth-public.key app/secrets/oauth/oauth-private.key