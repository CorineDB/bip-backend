# Frontend
## Application mobile
### Application mobile de paiement locatif
  > [Google Play Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)

  > [App Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)

- Flutter (mobile) et/ou React (web) pour l’interface utilisateur.

- Communique via API REST / GraphQL avec le backend.

- CDN pour les fichiers statiques (images KYC, reçus) pour réduire la latence.
  
### Application mobile de recouvrement locatif des creances
  > [Google Play Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)
  
  > [App Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)

- Flutter (mobile) et/ou React (web) pour l’interface utilisateur.

- Communique via API REST / GraphQL avec le backend.

- CDN pour les fichiers statiques (images KYC, reçus) pour réduire la latence.
  
### Application mobile de recherce de logement ou service locatif de proximite
  > [Google Play Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)
  
  > [App Store](https://play.google.com/store/apps/details?id=com.hokwe.hokwe_pay&pcampaignid=web_share)

- Flutter (mobile) et/ou React (web) pour l’interface utilisateur.

- Communique via API REST / GraphQL avec le backend.

- CDN pour les fichiers statiques (images KYC, reçus) pour réduire la latence.

## Site internet
- https://www.hokwe.co : Site internet de la holding
- https://www.hokwe.com : Site internet de presentation de hokwe
- https://www.pay.hokwe.com : landing page de hokwepay
- https://www.market.hokwe.com : marketplace d'offre des services immobilier
  

### Web application
- [manager.hokwe.com](https://manager.hokwe.com) web application de gestion locatif
- [booking.hokwe.com](https://manager.hokwe.com) web application de gestion locatif
- [pay.hokwepay.me](https://www.hokwepay.me) web application de payment locatif
- [recover.hokwepay.me](https://manager.hokwe.com) web application de recouvrement locatif
- [marchand.hokwepay.me](https://manager.hokwe.com) web application de pos locatif
- [checkout.hokwepay.me](https://checkout.hokwepay.me) widget de checkout locatif

 
# API Gateway

Point d’entrée unique pour toutes les requêtes des clients.

Fonctions :
    - Point d’entrée unique pour toutes les requêtes.
    - Routage vers microservices backend.

    - Authentification / Autorisation via Identity Provider (JWT, OAuth2).

    - Throttling et protection contre les attaques DDoS.
    - Throttling, logging et monitoring.

    - Gestion des logs et métriques pour monitoring.
    - Authentification / Autorisation via Identity Provider.

# Identity Provider (IdP)

    - Authentification centralisée OAuth2 et gestion des identités.

    - Gère :

        Connexion / inscription des utilisateurs.

            OAuth2 tokens pour sécuriser les API.

            OpenID Connect, OAuth 2.0, SAML 2.0

            MFA / OTP pour les transactions sensibles.

Fournisseurs possibles : le service open-source (Keycloak).
# CDN

    Use CloudFront to serve static content (like KYC images) quickly across regions.

    Reduces latency and offloads traffic from your backend.

# Backend

### HokweManager : service de management des real estates

- HokweEstate: sous-module de gestion des proprietes et toute operation de gestion locative ou d'administration de bien

### HokwePay : module de paiement et de recouvrement locatif

- HokweAgregator: sous-module agregator de paiement
- HokwePay: sous-module de paiement des factures
- HokweRecover: sous-module recouvrement des creances locatifs

### HokweWallet : module de gestion de portefeuille electronique, epargne, pret, investissement

- HokweWallet: gestion de wallet
  - Cotis'Hokwe: sous-module de tontine collective de loyer
  - HokweSplit: Epargne progressive du loyer
  - HokweEmergencyFund et emergency loan backed on rental score of epargne ou rental income
- HokweInvest: sous-module d'investissement dans les marches financiers
  

### HokweScoring : service de scoring, afin de construire la credibilite des payants
- HokweScore builing
- HokweTenantScreening
- HokweValuation

### HokweAnalytics: service analytics
- HokweReporting
- HokweAnalytics

### HokweCMS : service de gestion des contenus
- Help-Center : Gestion des aides et FAQ
- HokweNews : sous module de news
- HokweDoc : sous module de gestion des documents

### HokweMarket : service de management des real estates
- marketplace : service de gestion des 
- HokweSearch : service de gestion des
  
### HokweAds: module d'advertisement
- Social media advertisement
- marketplace advertisement
- HokweCreatorStudio

### HokweBilling : module de facturation
- Module de facturation des clients
- Gestion des services, plan d'abonnement et abonnement


### HokweAI : module ai agent llm
- sous-module chat llm
- Sous-module assistant vocal ai "AFI"

### Gestion des utilisateurs et KYC module de gestion des utilisateur

Calcul du scoring de loyauté.

Architecture : microservices and monolithique modulable.
Technologies possibles : Node.js, Laravel, Django, Spring Boot.
Communiquent via queues / events pour le traitement asynchrone.
Connexion à PostgreSQL, Redis, stockage fichiers.

# Base de données

Base relationnelle (PostgreSQL) pour les données critiques.
Cache mémoire : Redis pour OTP, sessions, scoring temporaire.
Base NoSQL optionnelle pour les logs ou notifications.
Base NoSQL optionnelle pour les logs

# Stockage de fichiers

Stocker les images KYC, reçus et documents dans un système scalable (S3, Blob Storage, GCS).

Gestion des permissions et chiffrement des données sensibles.

# Caching

Cache en mémoire (Redis/Memcached) pour :

Sessions utilisateurs et OTPs.

Résultats de scoring, calculs temporaires.

Réduction des appels à la base de données.

# Notifications

Service pour envoyer SMS, Emails, OTP, Push.
Service pour envoyer SMS, Emails, OTP, Push.

# Load Balancing & Scalabilité

Load Balancer pour répartir le trafic entre instances backend.

Auto-scaling selon la charge pour gérer les pics de fin de mois ou les périodes de loyers.

A intégré derrière l’API Gateway pour simplifier la gestion.

8. Sécurité et gestion des identités

Authentification et autorisation centralisée (JWT, OAuth2, ou IAM cloud provider).

Chiffrement des données sensibles au repos et en transit.

Contrôle d’accès basé sur les rôles (RBAC).

# Monitoring & Logging

Collecte des logs d’application, infrastructure et base de données.

Dashboards pour : performances, erreurs, taux de réussite des paiements.

Alertes automatiques en cas d’incident critique.

Collecte centralisée des logs et métriques : performance, erreurs, transactions.

Tracing des requêtes end-to-end (ex : OpenTelemetry, X-Ray).

# Event-driven / Background Jobs

Queue / Broker (RabbitMQ, Kafka, SQS) pour :

Notifications asynchrones.

Calcul du scoring des loyers.

Traitement des transactions batch.

# Services tiers (Third-party services)

Paiement : kkiapay.

KYC / Vérification identité : Jumio, Onfido, Trulioo.

SMS / Email / Push

Analytics

Credit scoring / financial data : Experian, TransUnion, locale fintech APIs.