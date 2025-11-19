# 📋 PLAN DE TEST - MIGRATION DES NOTIFICATIONS VERS CLIENT_APP_URL

## 🎯 Objectif
Tester que toutes les notifications modifiées génèrent des URLs correctes pointant vers le frontend client via `CLIENT_APP_URL`.

---

## ⚙️ Configuration Préalable

### 1. Variables d'Environnement
Vérifiez que votre fichier `.env` contient :
```bash
CLIENT_APP_URL=https://votre-frontend-url.com
# ou pour le dev local
CLIENT_APP_URL=http://localhost:3000
```

### 2. Outils Nécessaires
- Base de données de test avec des données de test
- Compte utilisateur de test avec différents rôles (DGPD, DPAF, Chef de projet, etc.)
- Client mail de test (MailHog, Mailtrap, ou email réel)
- Inspecteur de base de données (phpMyAdmin, TablePlus, etc.)

---

## 📝 TESTS PAR NOTIFICATION

### ✅ Test 1: AppreciationNoteConceptuelleNotification

**Fichier:** `app/Notifications/AppreciationNoteConceptuelleNotification.php`
**Problème corrigé:** Bug d'interpolation de variables (guillemets simples → doubles)

#### Scénario de Test
1. **Prérequis:**
   - Un projet existant avec une note conceptuelle
   - Un évaluateur DGPD

2. **Action:**
   - Créer une appréciation de note conceptuelle via l'interface ou en base de données
   - Déclencher la notification pour les différents types de destinataires

3. **Types de destinataires à tester:**
   - ✓ `redacteur_info` - Rédacteur de la note
   - ✓ `dpaf_supervision` - DPAF du ministère
   - ✓ `dgpd_collegial` - Autres membres DGPD
   - ✓ `chef_projet_evaluation_terminee` - Chef de projet
   - ✓ `evaluateur_confirmation` - Évaluateur

4. **Vérifications:**
   ```sql
   -- Vérifier dans la base de données
   SELECT data->>'$.action_url' as action_url
   FROM notifications
   WHERE type = 'App\\Notifications\\AppreciationNoteConceptuelleNotification'
   ORDER BY created_at DESC LIMIT 5;
   ```

   **URLs attendues:**
   - `redacteur_info`: `{CLIENT_APP_URL}/projet/{projet_id}/resultat-evaluation-note-conceptuelle/{note_id}/evaluations`
   - `dpaf_supervision`: `{CLIENT_APP_URL}/projet/{projet_id}`
   - `evaluateur_confirmation`: `{CLIENT_APP_URL}/evaluations/{evaluation_id}`

5. **Email:**
   - Vérifier que l'email contient des liens cliquables
   - Cliquer sur le lien "Action" dans l'email
   - Vérifier que l'URL commence bien par `CLIENT_APP_URL`

---

### ✅ Test 2: NoteConceptuelleSoumiseNotification

**Fichier:** `app/Notifications/NoteConceptuelleSoumiseNotification.php`
**Statut:** Déjà correctement migré

#### Scénario de Test
1. **Action:** Soumettre une note conceptuelle
2. **Destinataires:** `confirmation`, `evaluation_requise`, `information`
3. **URLs attendues:**
   - `evaluation_requise`: `{CLIENT_APP_URL}/projet/{projet_id}/resultat-evaluation-note-conceptuelle{note_id}`
   - `confirmation`: `{CLIENT_APP_URL}/projet/{projet_id}/detail-note-conceptuelle`

---

### ✅ Test 3: NotificationTdrFaisabiliteSoumis

**Fichier:** `app/Notifications/NotificationTdrFaisabiliteSoumis.php`
**Problème corrigé:** Erreur syntaxe PHP fatale (double match block)

#### Scénario de Test
1. **Prérequis:** Un TDR de faisabilité prêt à soumettre
2. **Action:** Soumettre le TDR de faisabilité
3. **⚠️ TEST CRITIQUE:** Vérifier que PHP ne plante PAS
4. **Destinataires:** `dgpd_evaluation`, `dpaf_supervision`, `equipe_organisation`, `soumetteur_confirmation`
5. **URLs attendues:** Toutes doivent pointer vers `{CLIENT_APP_URL}/projet/{projet_id}/detail-appreciation-tdr-faisabilite`

---

### ✅ Test 4: NotificationTdrFaisabiliteEvalue

**Fichier:** `app/Notifications/NotificationTdrFaisabiliteEvalue.php`
**Problème corrigé:** Typo "dashbaord" → "dashboard"

#### Scénario de Test
1. **Action:** Évaluer un TDR de faisabilité
2. **Destinataires:** `redacteur_resultat`, `dpaf_supervision`, `equipe_organisation`, `evaluateur_confirmation`
3. **URLs attendues:** `{CLIENT_APP_URL}/dashboard/projet/{projet_id}/detail-appreciation-tdr-faisabilite`
4. **⚠️ Vérification importante:** URL ne contient PAS "dashbaord"

---

### ✅ Test 5: NotificationRapportFaisabiliteSoumis

**Fichier:** `app/Notifications/NotificationRapportFaisabiliteSoumis.php`
**Problème corrigé:** Slashes manquants dans les URLs

#### Scénario de Test
1. **Action:** Soumettre un rapport de faisabilité
2. **URLs attendues:**
   - `dgpd_validation`: `{CLIENT_APP_URL}/projet/{projet_id}/details-validation-faisabilite`
   - `dpaf_supervision`: `{CLIENT_APP_URL}/projet/{projet_id}/details-soumission-rapport-faisabilite`
3. **⚠️ Vérification:** Toutes les URLs doivent avoir le `/` avant "details-"

---

### ✅ Test 6: NotificationRapportPrefaisabiliteSoumis

**Fichier:** `app/Notifications/NotificationRapportPrefaisabiliteSoumis.php`
**Problème corrigé:** Slashes manquants dans les URLs

#### Scénario de Test
1. **Action:** Soumettre un rapport de préfaisabilité
2. **URLs attendues:**
   - `dgpd_validation`: `{CLIENT_APP_URL}/projet/{projet_id}/details-validations-etude-prefaisabilite`
   - `dpaf_supervision`: `{CLIENT_APP_URL}/projet/{projet_id}/details-soumission-rapport-prefaisabilite`
3. **⚠️ Vérification:** Toutes les URLs doivent avoir le `/` avant "details-"

---

### ✅ Test 7: NotificationTdrPrefaisabiliteSoumis

**Fichier:** `app/Notifications/NotificationTdrPrefaisabiliteSoumis.php`
**Problème corrigé:** Migration complète vers CLIENT_APP_URL

#### Scénario de Test
1. **Action:** Soumettre un TDR de préfaisabilité
2. **Destinataires:** `dgpd_evaluation`, `dpaf_supervision`, `equipe_organisation`, `soumetteur_confirmation`
3. **URLs attendues:** Toutes pointent vers `{CLIENT_APP_URL}/projet/{projet_id}/detail-appreciation-tdr-prefaisabilite`
4. **⚠️ Vérification:** Aucune URL ne doit commencer par `/projets/` (ancien format)

---

### ✅ Test 8: NotificationTdrPrefaisabiliteEvalue

**Fichier:** `app/Notifications/NotificationTdrPrefaisabiliteEvalue.php`
**Problème corrigé:** Migration complète vers CLIENT_APP_URL

#### Scénario de Test
1. **Action:** Évaluer un TDR de préfaisabilité
2. **Destinataires:** `redacteur_resultat`, `dpaf_supervision`, `equipe_organisation`, `evaluateur_confirmation`
3. **URLs attendues:** `{CLIENT_APP_URL}/dashboard/projet/{projet_id}/detail-appreciation-tdr-prefaisabilite`

---

### ✅ Tests 9-13: Notifications Déjà Migrées

Ces notifications étaient déjà correctement migrées. Tests de régression recommandés :

- **NotificationRapportEvaluationExAnteSoumis**
- **NotificationRapportEvaluationExAnteValide**
- **NotificationEtudeFaisabiliteValidee**
- **NotificationEtudePrefaisabiliteValidee**
- **NotificationEtudeProfilValidee**

#### Test de Régression (Quick Check)
```sql
SELECT
    type,
    data->>'$.action_url' as action_url,
    data->>'$.type_destinataire' as destinataire,
    created_at
FROM notifications
WHERE type IN (
    'App\\Notifications\\NotificationRapportEvaluationExAnteSoumis',
    'App\\Notifications\\NotificationRapportEvaluationExAnteValide',
    'App\\Notifications\\NotificationEtudeFaisabiliteValidee'
)
ORDER BY created_at DESC LIMIT 10;
```

**Vérifier:** Toutes les URLs commencent par la valeur de `CLIENT_APP_URL`

---

## 🔍 MÉTHODES DE TEST

### Méthode 1: Test Unitaire via Tinker

```php
// Laravel Tinker
php artisan tinker

// Créer une notification test
$projet = \App\Models\Projet::first();
$user = \App\Models\User::first();

// Test AppreciationNoteConceptuelleNotification
$notification = new \App\Notifications\AppreciationNoteConceptuelleNotification(
    $evaluation, $noteConceptuelle, $projet, $evaluateur, 'redacteur_info'
);

// Vérifier l'URL
$array = $notification->toArray($user);
echo $array['action_url'];
// Attendu: https://votre-frontend.com/projet/xxx/...

// Test Email
$mail = $notification->toMail($user);
dd($mail->actionUrl);
```

### Méthode 2: Test via la Base de Données

```sql
-- Vérifier toutes les notifications récentes
SELECT
    id,
    type,
    JSON_EXTRACT(data, '$.action_url') as action_url,
    created_at
FROM notifications
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY created_at DESC;

-- Vérifier que TOUTES les URLs commencent par CLIENT_APP_URL
SELECT
    COUNT(*) as total_incorrect
FROM notifications
WHERE JSON_EXTRACT(data, '$.action_url') NOT LIKE CONCAT(
    (SELECT value FROM config WHERE key = 'CLIENT_APP_URL'), '%'
);
-- Résultat attendu: 0
```

### Méthode 3: Test via Email (MailHog/Mailtrap)

1. Configurer `.env` pour utiliser MailHog :
   ```bash
   MAIL_MAILER=smtp
   MAIL_HOST=localhost
   MAIL_PORT=1025
   ```

2. Déclencher une notification

3. Ouvrir MailHog (http://localhost:8025)

4. Vérifier :
   - ✓ L'email est bien reçu
   - ✓ Le lien "Action" est présent
   - ✓ Le lien commence par `CLIENT_APP_URL`
   - ✓ Cliquer sur le lien ne génère pas d'erreur 404

### Méthode 4: Test d'Intégration Complet

```php
// Test Feature Laravel
public function test_notification_urls_use_client_app_url()
{
    config(['CLIENT_APP_URL' => 'https://test-frontend.com']);

    // Créer les données de test
    $projet = Projet::factory()->create();
    $user = User::factory()->create();

    // Déclencher la notification
    $user->notify(new AppreciationNoteConceptuelleNotification(...));

    // Vérifier dans la base de données
    $notification = $user->notifications()->latest()->first();
    $this->assertStringStartsWith(
        'https://test-frontend.com',
        $notification->data['action_url']
    );

    // Vérifier l'email
    Mail::assertSent(function ($mail) {
        return str_starts_with($mail->actionUrl, 'https://test-frontend.com');
    });
}
```

---

## ✅ CHECKLIST DE VALIDATION FINALE

Avant de déployer en production, vérifier :

### Configuration
- [ ] `CLIENT_APP_URL` est défini dans `.env`
- [ ] `CLIENT_APP_URL` pointe vers le bon domaine (prod/staging/dev)
- [ ] Pas de trailing slash dans `CLIENT_APP_URL`

### Tests de Base
- [ ] Toutes les notifications modifiées sont testées
- [ ] Aucune erreur PHP 500 lors du déclenchement
- [ ] Les emails sont envoyés correctement
- [ ] Les liens dans les emails sont cliquables

### Tests d'URLs
- [ ] Aucune URL ne commence par `/projets/` (ancien format)
- [ ] Toutes les URLs commencent par `CLIENT_APP_URL`
- [ ] Pas de typo "dashbaord" dans les URLs
- [ ] Tous les slashes sont présents (pas de "projetdetails")
- [ ] Pas de guillemets simples dans les URLs (`'{$path}'`)

### Tests de Navigation
- [ ] Cliquer sur les liens dans les emails redirige vers le frontend
- [ ] Les pages du frontend se chargent correctement
- [ ] Pas d'erreur 404 ou 500

### Tests de Régression
- [ ] Les anciennes notifications (déjà migrées) fonctionnent toujours
- [ ] Les autres notifications (non modifiées) fonctionnent toujours

---

## 🐛 DÉBOGAGE

### Si une URL ne contient pas CLIENT_APP_URL

1. Vérifier que `.env` contient `CLIENT_APP_URL`
2. Redémarrer le serveur Laravel : `php artisan config:clear && php artisan cache:clear`
3. Vérifier le code : `env("CLIENT_APP_URL") ?? config("app.url")`

### Si l'email ne contient pas de lien

1. Vérifier la méthode `toMail()` utilise `url($this->getActionUrl())`
2. Vérifier que `action()` est appelé dans la chaîne de méthodes

### Si PHP plante (erreur 500)

1. Vérifier les logs : `tail -f storage/logs/laravel.log`
2. Chercher les erreurs de syntaxe (double `default`, double `match`)
3. Vérifier que toutes les variables sont définies (`$path`)

---

## 📊 RAPPORT DE TEST

Une fois les tests terminés, créer un rapport avec :

```markdown
## Résultats des Tests - Notifications

**Date:** YYYY-MM-DD
**Testeur:** Votre Nom
**Environnement:** Dev/Staging/Prod

### Notifications Testées
- [x] AppreciationNoteConceptuelleNotification - ✅ OK
- [x] NoteConceptuelleSoumiseNotification - ✅ OK
- [x] NotificationTdrFaisabiliteSoumis - ✅ OK
- [x] NotificationTdrFaisabiliteEvalue - ✅ OK
- [x] NotificationRapportFaisabiliteSoumis - ✅ OK
- [x] NotificationRapportPrefaisabiliteSoumis - ✅ OK
- [x] NotificationTdrPrefaisabiliteSoumis - ✅ OK
- [x] NotificationTdrPrefaisabiliteEvalue - ✅ OK

### Problèmes Identifiés
- Aucun / [Description des problèmes]

### Recommandations
- [Vos recommandations]
```

---

## 🎉 CONCLUSION

Ce plan de test couvre :
- ✅ 8 notifications modifiées
- ✅ 5 notifications de régression
- ✅ Tests unitaires, intégration et manuels
- ✅ Vérification des emails, base de données et navigation

**Temps estimé:** 2-3 heures pour tous les tests

**Prochaines étapes après validation:**
1. Commit des changements
2. Push vers la branche
3. Créer une Pull Request
4. Tests en staging
5. Déploiement en production
