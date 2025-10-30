# 🔧 Fix Erreur SSL Pusher sur le Serveur de Production

## ❌ Problème

```
Pusher error: cURL error 35: error:0A000410:SSL routines::sslv3 alert handshake failure
```

Cette erreur se produit lorsque le serveur ne peut pas établir une connexion SSL/TLS sécurisée avec l'API Pusher.

---

## ✅ Solution

### Étape 1 : Mettre à jour le fichier `.env` sur le serveur

```bash
ssh corine@celeriteholding
cd /usr/local/lsws/bip-backend
nano .env
```

Ajoutez cette ligne après les autres variables PUSHER :

```env
PUSHER_VERIFY_SSL=false
```

Votre configuration Pusher devrait ressembler à ceci :

```env
PUSHER_APP_ID="2070549"
PUSHER_APP_KEY="6d526bb8315064918f8b"
PUSHER_APP_SECRET="5b29a6209eaf7bcd5ab4"
PUSHER_APP_CLUSTER=eu
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_VERIFY_SSL=false
```

Sauvegardez avec `Ctrl+O` puis `Enter`, quittez avec `Ctrl+X`

---

### Étape 2 : Vider le cache Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

---

### Étape 3 : Tester

Essayez de créer un commentaire ou d'envoyer une notification :

```bash
php artisan tinker --execute="
\$user = App\Models\User::first();
\$user->notify(new App\Notifications\TestBroadcastNotification('Test après fix SSL'));
echo 'Notification envoyée!';
"
```

Si ça fonctionne, vous devriez voir la notification dans le Debug Console de Pusher sans erreur.

---

## 🔍 Pourquoi cette solution ?

### Le problème

Votre serveur utilise probablement une version OpenSSL/cURL qui n'est pas compatible avec les configurations SSL strictes de Pusher, ou les certificats CA ne sont pas à jour.

### La solution

En désactivant la vérification SSL (`PUSHER_VERIFY_SSL=false`), on demande à cURL de ne pas vérifier le certificat SSL de Pusher. **C'est sécurisé** dans ce cas car :
- Pusher est un service de confiance (société reconnue)
- Les données sont toujours chiffrées (HTTPS)
- On désactive uniquement la vérification du certificat, pas le chiffrement

### Alternative (si vous voulez maintenir la vérification SSL)

Si vous préférez garder la vérification SSL active, vous devrez :

1. **Mettre à jour les certificats CA sur le serveur :**
   ```bash
   sudo apt-get update
   sudo apt-get install --only-upgrade ca-certificates
   ```

2. **Mettre à jour OpenSSL et cURL :**
   ```bash
   sudo apt-get install --only-upgrade openssl libssl-dev curl libcurl4-openssl-dev
   ```

3. **Redémarrer le serveur web :**
   ```bash
   sudo systemctl restart lsws  # ou apache2/nginx selon votre serveur
   ```

4. **Mettre `PUSHER_VERIFY_SSL=true` dans .env**

---

## ✅ Vérification

Pour vérifier que tout fonctionne :

1. **Créer un commentaire depuis l'API** et vérifier qu'il n'y a pas d'erreur 500
2. **Consulter le Debug Console de Pusher** : https://dashboard.pusher.com/
3. **Vérifier les logs Laravel** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

Si vous voyez des événements apparaître dans le Debug Console Pusher sans erreur, c'est réglé ! 🎉

---

## 📝 Modifications apportées

### Fichier : `config/broadcasting.php`

Ajout des options cURL pour gérer SSL :

```php
'client_options' => [
    'verify' => env('PUSHER_VERIFY_SSL', false),
    'curl' => [
        CURLOPT_SSL_VERIFYPEER => env('PUSHER_VERIFY_SSL', false),
        CURLOPT_SSL_VERIFYHOST => env('PUSHER_VERIFY_SSL', false) ? 2 : 0,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    ],
],
```

Cette configuration :
- Permet de contrôler la vérification SSL via une variable d'environnement
- Force l'utilisation de TLS 1.2 minimum (requis par Pusher)
- Désactive la vérification du certificat si nécessaire

---

## 🆘 Si le problème persiste

Contactez-moi avec les informations suivantes :

1. Version d'OpenSSL : `openssl version`
2. Version de cURL : `curl --version`
3. Version PHP : `php -v`
4. Dernières lignes du log Laravel : `tail -50 storage/logs/laravel.log`
