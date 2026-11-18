# Vivora - Module 1 : Utilisateurs, partenariats et dons

Groupe Econova - 2A9 - Esprit 2026-2027
Thème : Santé et bien-être inclusif
Responsable du module : Hiba Sakka

Vivora (vie + vitalité) est une plateforme de santé et de bien-être accessible à tous.
Ce module gère les comptes utilisateurs et leur profil d'accessibilité, les partenaires
(associations, cliniques, pharmacies, sponsors), leurs publicités et les dons.

Le projet est écrit en PHP 8 avec PDO et MySQL, en architecture MVC, sans framework.

- Front office : template **Medilab** (`views/FrontOffice/`)
- Back office : template **AdminLTE 4** (`views/BackOffice/`)

## Fonctionnalités

### Front office

| Page | Rôle |
|------|------|
| `index.php` | Accueil avec les publicités actives (date du jour entre `date_debut` et `date_fin`) |
| `inscription.php` | Création du compte et du profil d'accessibilité (le rôle est toujours `utilisateur`) |
| `connexion.php` | Connexion. Un compte bloqué est refusé. L'admin est redirigé vers le Back office |
| `deconnexion.php` | Déconnexion (avec jeton) |
| `mon-profil.php` | Modifier ses informations, changer son mot de passe (l'ancien est exigé), modifier son profil d'accessibilité, supprimer son compte |
| `partenaires.php` | Partenaires actifs, avec un filtre par type |
| `partenaire-detail.php` | Fiche d'un partenaire, ses publicités et le bouton « Faire un don » |
| `faire-don.php` | Formulaire de don (utilisateur connecté) |
| `mes-dons.php` | Historique de ses propres dons avec leur statut |

Les préférences d'accessibilité de l'utilisateur connecté s'appliquent à tout le site
grâce à des classes posées sur la balise `<html>` :
`texte-grand`, `texte-tres-grand`, `contraste-fort` et `lecture-facile`.

### Back office (rôle admin)

| Groupe | Pages |
|--------|-------|
| Tableau de bord | `dashboard.php` : utilisateurs, comptes bloqués, partenaires, publicités actives, total des dons validés, dons en attente, derniers dons |
| Utilisateurs | `utilisateurs-liste.php` (jointure avec le profil, recherche, filtre par rôle et statut, tri), `utilisateur-ajout.php`, `utilisateur-modifier.php`, `utilisateur-detail.php`, `utilisateur-supprimer.php`, `utilisateur-statut.php` (bloquer / réactiver), `utilisateurs-par-besoin.php` (INNER JOIN) |
| Partenariats | `partenaires-liste.php`, `partenaire-ajout.php`, `partenaire-modifier.php`, `partenaire-detail.php` (avec ses publicités et ses dons), `partenaire-supprimer.php`, `publicites-liste.php`, `publicite-ajout.php`, `publicite-modifier.php`, `publicite-supprimer.php`, `publicite-statut.php` (activer / désactiver) |
| Dons | `dons-liste.php` (filtre par statut et par partenaire), `don-statut.php` (valider / refuser), `don-supprimer.php`, `dons-par-partenaire.php` (total par partenaire) |

Upload d'images (publicités et logos) dans `uploads/` : jpg, png ou webp uniquement
(extension et type MIME réel vérifiés), 2 Mo maximum, nom de fichier généré au hasard.

## Entités et relations

| Table | Champs |
|-------|--------|
| `utilisateur` | id, nom, prenom, email (UNIQUE), mot_de_passe, telephone, role (utilisateur, admin), statut (actif, bloque), date_inscription |
| `profil_accessibilite` | id, user_id (UNIQUE, FK), besoin_visuel, besoin_moteur, besoin_auditif, taille_texte (normal, grand, tres-grand), contraste_fort, lecture_facile |
| `partenaire` | id, nom, type (association, clinique, pharmacie, sponsor), email, telephone, adresse, description, logo, statut (actif, inactif), date_debut |
| `publicite` | id, partenaire_id (FK), titre, description, image, lien, date_debut, date_fin, statut (active, inactive) |
| `don` | id, utilisateur_id (FK), partenaire_id (FK), montant, date_don, moyen_paiement (carte, virement, especes), message, statut (en_attente, valide, refuse) |

- Utilisateur **1 - 1** ProfilAccessibilite (`ON DELETE CASCADE`)
- Partenaire **1 - n** Publicite (`ON DELETE CASCADE`)
- Partenaire **1 - n** Don (`ON DELETE CASCADE`)
- Utilisateur **1 - n** Don (`ON DELETE SET NULL` : le don reste dans l'historique du partenaire si le compte est supprimé)

Les diagrammes sont dans `docs/` :

- `docs/cas-utilisation.puml` : diagramme de cas d'utilisation
- `docs/diagramme-classes.puml` : diagramme de classes

Pour obtenir les images PNG, ouvrir les fichiers avec l'extension PlantUML de VS Code
(clic droit, « Export Current Diagram ») ou avec `java -jar plantuml.jar docs/*.puml`.

## Sécurité

- `session.php` est inclus en premier dans chaque page :
  - `session.use_strict_mode`, `session.cookie_httponly` et `session.cookie_samesite=Lax` avant `session_start()` ;
  - expiration après 30 minutes d'inactivité ;
  - `session_regenerate_id(true)` à la connexion et au changement de mot de passe ;
  - en-têtes `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` et `Referrer-Policy` ;
  - fonctions `requireAuth()`, `requireRole($role)`, `getCsrfToken()`, `champCsrf()`, `verifyCsrfToken($token)` (avec `hash_equals`) et `fermerSession()`.
- `requireRole('admin')` en haut de chaque page du Back office : un utilisateur simple reçoit une erreur 403.
- Un jeton CSRF dans tous les formulaires POST. Les suppressions et les changements de statut se font uniquement en POST.
- Mots de passe hachés avec `password_hash` et vérifiés avec `password_verify`.
- Requêtes préparées PDO partout (`EMULATE_PREPARES` à false). Le tri passe par une liste blanche.
- `htmlspecialchars` à chaque affichage.
- Un utilisateur ne voit que ses propres dons : l'identifiant vient toujours de la session.
- Un admin ne peut pas se supprimer, se bloquer ni changer son propre rôle.

## Validation des saisies

- PHP : une méthode `valider()` dans chaque contrôleur. Les messages s'affichent sous chaque champ et les valeurs saisies sont gardées.
- JavaScript : `assets/js/validation.js` (Front et Back) vérifie les mêmes règles avant l'envoi. Les formulaires ont l'attribut `novalidate`.

| Champ | Règle |
|-------|-------|
| Nom, prénom | 2 à 50 lettres |
| E-mail | format valide et unique |
| Téléphone | 8 chiffres |
| Mot de passe | au moins 8 caractères, une majuscule et un chiffre, confirmation identique |
| Montant du don | entre 1 et 10000 DT |
| Dates de publicité | `date_fin` après `date_debut` |
| Listes (enum) | valeurs autorisées seulement |
| Lien de publicité | URL valide en http ou https |

## Installation avec XAMPP

1. Copier le dossier `vivora` dans `C:\xampp\htdocs\`.
2. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
3. Ouvrir http://localhost/phpmyadmin, onglet **Importer**, choisir `database/vivora_db.sql` puis **Exécuter**.
   Le script crée la base `vivora_db`, les 5 tables et les données de test.
4. Ouvrir http://localhost/vivora/

La connexion à la base est dans `config.php` (hôte `localhost`, base `vivora_db`, utilisateur `root`, sans mot de passe).

## Comptes de test

| Rôle | E-mail | Mot de passe | Remarque |
|------|--------|--------------|----------|
| Admin | admin@vivora.tn | Admin123! | accès au Back office |
| Utilisateur | sami.benali@vivora.tn | Vivora123 | texte grand et contraste fort |
| Utilisateur | amira.trabelsi@vivora.tn | Vivora123 | besoin moteur |
| Utilisateur | youssef.gharbi@vivora.tn | Vivora123 | **compte bloqué** |
| Utilisateur | nour.mansouri@vivora.tn | Vivora123 | texte très grand et lecture facile |

## Adresses

- Front office : http://localhost/vivora/views/FrontOffice/index.php
- Connexion : http://localhost/vivora/views/FrontOffice/connexion.php
- Back office : http://localhost/vivora/views/BackOffice/dashboard.php

## Structure des dossiers

```
vivora/
├── config.php                  classe config, getConnexion()
├── session.php                 sessions, CSRF, rôles, en-têtes de sécurité
├── index.php                   redirige vers le Front office
├── controllers/
│   ├── UtilisateurController.php
│   ├── ProfilAccessibiliteController.php
│   ├── PartenaireController.php
│   ├── PubliciteController.php
│   └── DonController.php
├── models/
│   ├── Utilisateur.php
│   ├── ProfilAccessibilite.php
│   ├── Partenaire.php
│   ├── Publicite.php
│   └── Don.php
├── views/
│   ├── erreur-403.php
│   ├── FrontOffice/
│   │   ├── includes/header.php, footer.php
│   │   ├── assets/             template Medilab + css/vivora.css + js/validation.js
│   │   └── index.php, inscription.php, connexion.php, ...
│   └── BackOffice/
│       ├── includes/header.php, footer.php
│       ├── assets/             template AdminLTE + css/vivora-admin.css + js/validation.js
│       └── dashboard.php, utilisateurs-liste.php, ...
├── uploads/                    images des publicités et logos
├── database/vivora_db.sql      base de données et données de test
└── docs/                       diagrammes PlantUML
```
