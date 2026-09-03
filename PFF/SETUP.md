# Installation — BJ SERVICE (backend PHP + MySQL + Chat)

## 1. Copier le projet
Remplace ton ancien dossier `PFF` dans `wamp64/www/` par celui-ci.

## 2. Base de données (2 imports, dans l'ordre)
1. Ouvre `http://localhost/phpmyadmin`
2. **Importer** → `bdmf.sql` → Exécuter (crée toutes les tables de base)
3. **Importer** → `migration_chat.sql` → Exécuter (corrige les types de colonnes + ajoute `regrouper` et `messages` + insère les 9 services déjà présents sur le site)

## 3. Créer ton compte admin
- Va sur `http://localhost/PFF/create_admin.php` **une seule fois**
- Ça crée : email `admin@bjservice.com` / mot de passe `admin123`
- **Supprime le fichier `create_admin.php` juste après** (sécurité)
- Connecte-toi sur `http://localhost/PFF/admin_login.php`

## 4. Tester le flow complet
1. Sur `index.html`, inscris-toi / connecte-toi comme client (`login.php`)
2. Clique **Order now** sur un service (ex: Valorant) → un panier + un chat se créent automatiquement → tu arrives sur `chat.php`
3. Écris un message
4. Dans un **autre navigateur** (ou navigation privée), connecte-toi sur `admin_login.php` → `admin_chat.php` → tu vois la conversation → réponds
5. Reviens sur l'onglet client : le message admin apparaît automatiquement (actualisation toutes les 3 secondes)
6. Le chat affiche un compte à rebours ("Ferme dans XXm XXs") — après 1h depuis le premier message, il devient définitivement lecture seule.

## Fichiers ajoutés dans cette étape
- `migration_chat.sql` — corrige le schéma + tables `regrouper`, `messages` + seed `service`
- `order.php` — clic "Order now" → crée panier + regrouper + 1er message
- `chat.php` — interface chat **client** (liste conversations + messages temps réel)
- `admin_chat.php` — interface chat **admin** (toutes les conversations)
- `admin_login.php` / `create_admin.php` — authentification admin
- `fetch_messages.php` / `send_message.php` — endpoints AJAX (polling 3s)

## Notes importantes
- Chat = **polling** (pas de vrais WebSockets), suffisant pour un PFE, se sent "temps réel" (refresh 3s)
- Les items **PAYMENT** (STEG, SONEDE...) ne sont pas encore reliés à des services/commandes — ils renvoient vers `contact.html` pour l'instant
- Le mot de passe admin est hashé (comme les users) — sécurisé

## Prochaines étapes possibles
- Page "Mon compte" (afficher/modifier profil utilisateur)
- Rendre les tuiles PAYMENT fonctionnelles (les ajouter à `service`)
- Dashboard admin complet (gérer services, valider/annuler commandes, promotions, publications)
- Badge "non lu" sur les conversations admin
