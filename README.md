# Vivora - Module Utilisateur

Groupe Econova - 2A9 - Esprit 2026-2027
Thème : Santé et bien-être inclusif

Vivora (vie + vitalité) est une plateforme de santé et de bien-être accessible à tous.
Ce module est **commun à tout le groupe** : il gère les comptes, la connexion, les rôles,
les sessions et la sécurité. Chaque membre construit son propre module par-dessus.

Le projet est écrit en PHP 8 avec PDO et MySQL, en architecture MVC simple, sans framework.

- Front office : template **Medilab** (`views/FrontOffice/`)
- Back office : template **AdminLTE 4** (`views/BackOffice/`)

## Membres

| Membre | Partie |
|--------|--------|
| Hiba Sakka | Module utilisateur (ce dépôt), accessibilité, partenariats et dons |
| *à compléter* | *autres modules du groupe* |

## Fonctionnalités

### Front office

| Page | Rôle |
|------|------|
| `index.php` | Accueil |
| `inscription.php` | Création d'un compte (rôle toujours `utilisateur`). Message « Compte créé avec succès », puis connexion |
| `connexion.php` | Connexion. Un compte bloqué est refusé. L'admin va au Back office, l'utilisateur à l'accueil |
| `deconnexion.php` | Déconnexion (avec jeton) |
| `mon-profil.php` | Modifier ses informations, changer son mot de passe (l'ancien est exigé), supprimer son compte |

### Back office (rôle admin)

| Page | Rôle |
|------|------|
| `dashboard.php` | Nombre d'utilisateurs, comptes actifs, comptes bloqués, derniers inscrits |
| `utilisateurs-liste.php` | Liste avec recherche, filtre par rôle et statut, tri, boutons d'action |
| `utilisateur-ajout.php` | Ajouter un utilisateur |
| `utilisateur-modifier.php` | Modifier un utilisateur (mot de passe facultatif) |
| `utilisateur-detail.php` | Fiche d'un utilisateur |
| `utilisateur-supprimer.php` | Supprimer (POST + CSRF) |
| `utilisateur-statut.php` | Bloquer / débloquer (POST + CSRF) |
| `utilisateur-role.php` | Rendre admin / retirer le rôle admin (POST + CSRF) |

Chaque action affiche un message de succès (vert) ou d'erreur (rouge).

## Rôles et règles métier

- `utilisateur` : Front office et son profil.
- `admin` : en plus, le Back office.
- Un compte `bloque` ne peut plus se connecter. S'il est bloqué pendant qu'il est connecté, il est déconnecté à la page suivante.
- Si le rôle d'un compte change pendant qu'il est connecté, le changement s'applique à la page suivante.
- Un admin ne peut pas se supprimer, se bloquer ni changer son propre rôle.
- Il reste toujours au moins un admin actif : supprimer, bloquer ou rétrograder le dernier est refusé.

## Sécurité

- `session.php` est inclus en premier dans chaque page :
  - `use_strict_mode`, `cookie_httponly`, `cookie_samesite=Lax` avant `session_start()` ;
  - expiration après 30 minutes d'inactivité ;
  - `session_regenerate_id(true)` à la connexion et au changement de mot de passe ;
  - en-têtes `X-Frame-Options: DENY` et `X-Content-Type-Options: nosniff`.
- Mots de passe hachés avec `password_hash` et vérifiés avec `password_verify`.
- Requêtes préparées PDO partout. Le tri passe par une liste blanche. Les `id` sont convertis en entier.
- `htmlspecialchars` à chaque affichage (protection XSS).
- Un jeton CSRF dans tous les formulaires POST. Suppression, blocage et changement de rôle se font uniquement en POST.

### Fonctions de `session.php`

| Fonction | Rôle |
|----------|------|
| `estConnecte()` | `true` si un utilisateur est connecté |
| `estAdmin()` | `true` si l'utilisateur connecté est admin |
| `exigerConnexion()` | Redirige vers la connexion si personne n'est connecté, ou si le compte a été bloqué ou supprimé |
| `exigerAdmin()` | Exige le rôle admin, sinon erreur 403 (`views/erreur-403.php`) |
| `creerJeton()`, `verifierJeton($jeton)` | Créer et vérifier le jeton CSRF (avec `hash_equals`) |
| `champJeton()` | Affiche le champ caché du jeton dans un formulaire |
| `verifierFormulaire()` | Refuse la requête (403) si ce n'est pas un POST avec un jeton valide |
| `fermerSession()` | Vide et détruit la session |

L'utilisateur connecté est dans `$_SESSION['user']` : `id`, `nom`, `prenom`, `email`, `role`.

## Validation des saisies

- PHP : `UtilisateurController::validerUtilisateur()`. Les messages s'affichent en rouge sous chaque champ,
  un message général s'affiche en haut du formulaire, et les valeurs saisies sont gardées (sauf les mots de passe).
- JavaScript : `assets/js/validation.js` (Front et Back) vérifie les mêmes règles avant l'envoi.
  Tous les formulaires ont l'attribut `novalidate` : pas de validation HTML5.

| Champ | Règle |
|-------|-------|
| Nom, prénom | 2 à 50 lettres |
| E-mail | format valide et unique |
| Téléphone | 8 chiffres |
| Mot de passe | au moins 8 caractères, une majuscule et un chiffre, confirmation identique |
| Rôle, statut | valeurs autorisées seulement |

## Installation avec XAMPP

1. Cloner le dépôt dans `C:\xampp\htdocs\vivora` :
   `git clone https://github.com/hiba1577/econova.git C:\xampp\htdocs\vivora` puis `git checkout module-utilisateur`.
2. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
3. Ouvrir http://localhost/phpmyadmin, onglet **Importer**, choisir `database/vivora_db.sql` puis **Exécuter**.
   Attention : le script commence par `DROP DATABASE vivora_db` et efface les tables des autres modules.
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

## Tests réalisés

Tests faits sur http://localhost/vivora/ (49 vérifications, toutes réussies) :

- inscription valide, mot de passe haché `$2y$...` vérifié dans phpMyAdmin, mot de passe `abc` et e-mail déjà pris refusés ;
- connexion utilisateur, admin, compte bloqué, mauvais mot de passe ;
- CRUD admin : ajout, modification avec et sans mot de passe, suppression ;
- bloquer, débloquer, changer le rôle, y compris pendant que le compte est connecté ;
- règles : l'admin ne peut pas se bloquer, se supprimer ni retirer son rôle ;
- recherche, filtre et tri ;
- routes protégées sans connexion, et Back office refusé (403) à un utilisateur simple ;
- CSRF : POST sans jeton, avec un faux jeton ou en GET refusé (403) ;
- injection SQL (`' OR '1'='1` dans la connexion, la recherche, le tri et l'`id`) sans effet ;
- XSS (`<script>` dans le nom et la recherche) refusé ou affiché échappé ;
- expiration de la session après 30 minutes ;
- aucune erreur PHP dans `C:\xampp\apache\logs\error.log` ni dans les pages.

## Organisation Git

| Branche | Contenu |
|---------|---------|
| `main` | initialisation du dépôt |
| `module-utilisateur` | ce module (partie commune) |
| `module-hiba-accessibilite-dons` | module de Hiba, construit sur `module-utilisateur` |
| `module1-hiba` | ancienne version du module de Hiba |

- Un commit par partie (base et session, CRUD, connexion et rôles, validation, liste…).
- `module-utilisateur` est fusionné dans `module-hiba-accessibilite-dons` avec `git merge` (sans `--force`).
- Le tag `sauvegarde-module1-complet` garde une sauvegarde de l'ancien module.

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
├── docs/explication-code.md    explication de chaque fichier et questions de tuteur
├── uploads/                    fichiers envoyés (vide)
└── database/vivora_db.sql      base de données et comptes de test
```

## Utiliser ce module dans votre partie

Une page du Front office (utilisateur connecté) :

```php
<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/MonController.php';

exigerConnexion();
$utilisateurId = $_SESSION['user']['id'];

$titre = 'Ma page';
include __DIR__ . '/includes/header.php';
?>
    ...
<?php include __DIR__ . '/includes/footer.php'; ?>
```

Une page du Back office : remplacer `exigerConnexion()` par `exigerAdmin()`.

Un formulaire en POST :

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
}
?>
<form method="post" action="ma-page.php" novalidate>
  <?= champJeton() ?>
  ...
</form>
```

Dans vos tables, ajoutez une clé étrangère `utilisateur_id` vers `utilisateur(id)`
(`ON DELETE CASCADE` ou `ON DELETE SET NULL`). L'identifiant vient toujours de la session, jamais du formulaire.

## Usage de l'IA

Ce module a été réalisé avec l'aide de **Claude** (Claude Code, modèle Claude Opus 5.5, Anthropic).

**Ce que Claude a fait**

- Claude a écrit le code de ce module : la base de données, `config.php`, `session.php`, le modèle,
  le contrôleur (CRUD, connexion, validation, recherche, rôles), les vues Front et Back office
  et `validation.js`.
- Au départ, Hiba devait écrire elle-même le CRUD, la connexion, la validation et la liste, avec Claude comme guide.
  Faute de temps avant la validation, elle a demandé à Claude d'écrire aussi ces parties.
- Claude a écrit et lancé les tests (49 vérifications sur http://localhost/vivora/) et a corrigé les bugs trouvés :
  l'inscription qui échouait sans message, et le message d'expiration de session remplacé par un autre.
- Claude a rédigé ce README et `docs/explication-code.md`.

**Ce que Hiba a fait**

- Elle a défini le besoin : les fonctionnalités, les rôles, les règles métier, le style du cours
  (MVC de l'atelier « Club Sportif », noms en français, code court) et les règles Git.
- Elle a testé chaque fonctionnalité dans le navigateur et vérifié les données dans phpMyAdmin.
  C'est elle qui a trouvé le bug de l'inscription sans message.
- Elle a relu le code fichier par fichier avec `docs/explication-code.md`.

**Ce qu'elle a appris**

- Le schéma MVC : le contrôleur parle à la base, la vue affiche, le modèle transporte les données.
- Pourquoi utiliser des requêtes préparées (injection SQL), `htmlspecialchars` (XSS), un jeton CSRF,
  `password_hash` et `session_regenerate_id`.
- Pourquoi valider deux fois : en JavaScript pour le confort, et en PHP pour la sécurité.
- Qu'un code généré doit être testé et relu : une méthode vide ne produit aucune erreur visible.
