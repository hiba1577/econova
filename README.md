# Vivora - Module Utilisateur (partie commune)

Groupe Econova - 2A9 - Esprit 2026-2027
Thème : Santé et bien-être inclusif

Vivora (vie + vitalité) est une plateforme de santé et de bien-être accessible à tous.
Ce module est **commun à tout le groupe** : il gère les comptes, la connexion, les rôles,
les sessions et la sécurité. Chaque membre construit son propre module par-dessus.

Le projet est écrit en PHP 8 avec PDO et MySQL, en architecture MVC, sans framework.

- Front office : template **Medilab** (`views/FrontOffice/`)
- Back office : template **AdminLTE 4** (`views/BackOffice/`)

## Fonctionnalités

### Front office

| Page | Rôle |
|------|------|
| `index.php` | Accueil |
| `inscription.php` | Création d'un compte (le rôle est toujours `utilisateur`) |
| `connexion.php` | Connexion. Un compte bloqué est refusé. L'admin est redirigé vers le Back office |
| `deconnexion.php` | Déconnexion (avec jeton) |
| `mon-profil.php` | Modifier ses informations, changer son mot de passe (l'ancien est exigé), supprimer son compte |

### Back office (rôle admin)

| Page | Rôle |
|------|------|
| `dashboard.php` | Nombre d'utilisateurs, comptes actifs, comptes bloqués, derniers inscrits |
| `utilisateurs-liste.php` | Liste avec recherche, filtre par rôle et statut, tri |
| `utilisateur-ajout.php` | Ajouter un utilisateur |
| `utilisateur-modifier.php` | Modifier un utilisateur (mot de passe facultatif) |
| `utilisateur-detail.php` | Fiche d'un utilisateur |
| `utilisateur-supprimer.php` | Supprimer (POST + CSRF) |
| `utilisateur-statut.php` | Bloquer / réactiver (POST + CSRF) |

Le menu contient un lien « Autres modules » (`#`) à remplacer par les pages des autres membres.

## Rôles

- `utilisateur` : accès au Front office et à son profil.
- `admin` : accès en plus au Back office.
- Un admin ne peut pas se supprimer, se bloquer ni changer son propre rôle.
- Un compte `bloque` ne peut plus se connecter.

## Sessions et sécurité

- `session.php` est inclus en premier dans chaque page :
  - `session.use_strict_mode`, `session.cookie_httponly` et `session.cookie_samesite=Lax` avant `session_start()` ;
  - expiration après 30 minutes d'inactivité ;
  - `session_regenerate_id(true)` à la connexion et au changement de mot de passe ;
  - en-têtes `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` et `Referrer-Policy`.
- Mots de passe hachés avec `password_hash` et vérifiés avec `password_verify`.
- Requêtes préparées PDO partout (`EMULATE_PREPARES` à false). Le tri passe par une liste blanche.
- `htmlspecialchars` à chaque affichage.
- Un jeton CSRF dans tous les formulaires POST. Les suppressions et les changements de statut se font uniquement en POST.

### Fonctions de `session.php`

| Fonction | Rôle |
|----------|------|
| `estConnecte()` | `true` si un utilisateur est connecté |
| `requireAuth()` | Redirige vers la connexion si personne n'est connecté |
| `requireRole('admin')` | Exige un rôle, sinon erreur 403 (`views/erreur-403.php`) |
| `champCsrf()` | Affiche le champ caché du jeton CSRF dans un formulaire |
| `verifierPost()` | Refuse la requête (403) si ce n'est pas un POST avec un jeton CSRF valide |
| `getCsrfToken()`, `verifyCsrfToken($token)` | Créer et vérifier le jeton (avec `hash_equals`) |
| `connecterUtilisateur($utilisateur)`, `fermerSession()` | Ouvrir et fermer la session |

L'utilisateur connecté est dans `$_SESSION['user']` : `id`, `nom`, `prenom`, `email`, `role`.

## Utiliser ce module dans votre partie

### 1. Une page du Front office (utilisateur connecté)

```php
<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/MonController.php';

requireAuth();

$utilisateurId = $_SESSION['user']['id'];

$titre = 'Ma page';
$sousTitre = 'Description de ma page';
$pageActive = 'ma-page';
include __DIR__ . '/includes/header.php';
?>
    ...
<?php include __DIR__ . '/includes/footer.php'; ?>
```

### 2. Une page du Back office (admin)

```php
<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/MonController.php';

requireRole('admin');

$titre = 'Ma liste';
$menu = 'ma-liste';
include __DIR__ . '/includes/header.php';
?>
    ...
<?php include __DIR__ . '/includes/footer.php'; ?>
```

Ajoutez ensuite votre lien dans la sidebar de `views/BackOffice/includes/header.php`.

### 3. Un formulaire en POST avec CSRF

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
}
?>
<form method="post" action="ma-page.php" novalidate>
  <?= champCsrf() ?>
  ...
</form>
```

### 4. Une table liée à l'utilisateur

Dans vos tables, ajoutez une clé étrangère `utilisateur_id` vers `utilisateur(id)` :

```sql
CREATE TABLE ma_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    ...
    CONSTRAINT fk_ma_table_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Utilisez `ON DELETE CASCADE` si vos lignes doivent disparaître avec le compte,
ou `ON DELETE SET NULL` (avec `utilisateur_id INT DEFAULT NULL`) pour les garder.
L'identifiant vient toujours de la session (`$_SESSION['user']['id']`), jamais du formulaire.

## Entité

| Table | Champs |
|-------|--------|
| `utilisateur` | id, nom, prenom, email (UNIQUE), mot_de_passe, telephone, role (utilisateur, admin), statut (actif, bloque), date_inscription |

## Validation des saisies

- PHP : `UtilisateurController::valider()`. Les messages s'affichent sous chaque champ et les valeurs saisies sont gardées.
- JavaScript : `assets/js/validation.js` (Front et Back) vérifie les mêmes règles avant l'envoi. Les formulaires ont l'attribut `novalidate`.

| Champ | Règle |
|-------|-------|
| Nom, prénom | 2 à 50 lettres |
| E-mail | format valide et unique |
| Téléphone | 8 chiffres |
| Mot de passe | au moins 8 caractères, une majuscule et un chiffre, confirmation identique |
| Rôle, statut | valeurs autorisées seulement |

## Installation avec XAMPP

1. Copier le dossier `vivora` dans `C:\xampp\htdocs\`.
2. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
3. Ouvrir http://localhost/phpmyadmin, onglet **Importer**, choisir `database/vivora_db.sql` puis **Exécuter**.
   Le script crée la base `vivora_db`, la table `utilisateur` et les comptes de test.
4. Ouvrir http://localhost/vivora/

La connexion à la base est dans `config.php` (hôte `localhost`, base `vivora_db`, utilisateur `root`, sans mot de passe).

## Comptes de test

| Rôle | E-mail | Mot de passe | Remarque |
|------|--------|--------------|----------|
| Admin | admin@vivora.tn | Admin123! | accès au Back office |
| Utilisateur | sami.benali@vivora.tn | Vivora123 | |
| Utilisateur | amira.trabelsi@vivora.tn | Vivora123 | |
| Utilisateur | youssef.gharbi@vivora.tn | Vivora123 | **compte bloqué** |
| Utilisateur | nour.mansouri@vivora.tn | Vivora123 | |

## Structure des dossiers

```
vivora/
├── config.php                  classe config, getConnexion()
├── session.php                 sessions, CSRF, rôles, en-têtes de sécurité
├── index.php                   redirige vers le Front office
├── controllers/
│   └── UtilisateurController.php
├── models/
│   └── Utilisateur.php
├── views/
│   ├── erreur-403.php
│   ├── FrontOffice/
│   │   ├── includes/header.php, footer.php
│   │   ├── assets/             template Medilab + css/vivora.css + js/validation.js
│   │   └── index.php, inscription.php, connexion.php, deconnexion.php, mon-profil.php
│   └── BackOffice/
│       ├── includes/header.php, footer.php
│       ├── assets/             template AdminLTE + css/vivora-admin.css + js/validation.js
│       └── dashboard.php, utilisateurs-liste.php, utilisateur-*.php
├── uploads/                    fichiers envoyés (vide)
└── database/vivora_db.sql      base de données et comptes de test
```
