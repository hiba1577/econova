# Explication du code - Module Utilisateur

## 1. Le rôle de chaque fichier

| Fichier | Rôle |
|---------|------|
| `config.php` | La classe `config` et sa méthode `getConnexion()` ouvrent **une seule** connexion PDO à `vivora_db` et la renvoient. Les erreurs SQL deviennent des exceptions, qu'on attrape avec `try / catch`. |
| `session.php` | Inclus en premier dans chaque page. Il démarre une session sécurisée, la ferme après 30 minutes d'inactivité, et contient les fonctions de rôle (`estConnecte`, `estAdmin`, `exigerConnexion`, `exigerAdmin`) et de jeton CSRF (`creerJeton`, `verifierJeton`, `champJeton`, `verifierFormulaire`). |
| `models/Utilisateur.php` | La classe `Utilisateur` : des attributs `private`, un constructeur, des getters et des setters. Elle transporte les données d'un utilisateur de la vue vers le contrôleur. |
| `controllers/UtilisateurController.php` | Toutes les requêtes SQL sur la table `utilisateur` : CRUD, connexion, recherche, comptages, validation. Les vues n'écrivent jamais de SQL. |
| `database/vivora_db.sql` | Crée la base, la table `utilisateur` et les 5 comptes de test (mots de passe déjà hachés). |
| `index.php` | Redirige simplement vers `views/FrontOffice/index.php`. |
| `views/erreur-403.php` | La page « Accès refusé », affichée par `exigerAdmin()`. |
| `FrontOffice/includes/header.php`, `footer.php` | Le haut et le bas du template Medilab. Le menu change selon `estConnecte()` et `estAdmin()`. Le header affiche les messages de succès et d'erreur gardés dans la session. |
| `FrontOffice/inscription.php` | Formulaire d'inscription. En POST : jeton, validation PHP, `password_hash`, `Create()`, puis redirection vers la connexion. |
| `FrontOffice/connexion.php` | Vérifie l'e-mail et le mot de passe (`password_verify`), refuse un compte bloqué, change l'identifiant de session, puis redirige selon le rôle. |
| `FrontOffice/deconnexion.php` | Vérifie le jeton du lien, ferme la session et renvoie vers la connexion. |
| `FrontOffice/mon-profil.php` | Trois formulaires : modifier ses informations, changer son mot de passe (l'ancien est demandé), supprimer son compte (refusé pour un admin). |
| `BackOffice/includes/header.php`, `footer.php` | Le haut et le bas du template AdminLTE : la barre du haut, la sidebar et les messages. |
| `BackOffice/dashboard.php` | Le tableau de bord : nombre d'utilisateurs, de comptes actifs et de comptes bloqués, et les 5 derniers inscrits. |
| `BackOffice/utilisateurs-liste.php` | Le formulaire de recherche, filtre et tri en GET, puis une boucle `foreach` qui affiche une ligne et ses boutons d'action pour chaque utilisateur. |
| `BackOffice/utilisateur-ajout.php`, `utilisateur-modifier.php` | Les formulaires admin. La modification ne change le mot de passe que s'il est rempli, et l'admin ne peut pas changer son propre rôle ni son statut. |
| `BackOffice/utilisateur-detail.php` | La fiche d'un utilisateur, avec les boutons modifier, bloquer, changer le rôle et supprimer. |
| `BackOffice/utilisateur-supprimer.php`, `utilisateur-statut.php`, `utilisateur-role.php` | Les pages de traitement, sans HTML : POST et jeton, vérification des règles métier, action, message, puis retour à la liste. |
| `assets/js/validation.js` | Vérifie chaque champ selon son attribut `data-regle` quand on le quitte et à l'envoi. S'il y a une erreur, il l'écrit en rouge sous le champ et bloque l'envoi. |

## 2. Les méthodes du contrôleur

Toutes suivent le même schéma : `$db = config::getConnexion();`, puis dans un `try`, `prepare` → `execute` → résultat.
Le `catch` affiche l'erreur et renvoie `false` (ou `[]`, ou `0`).

| Méthode | Ce qu'elle fait |
|---------|-----------------|
| `readUtilisateur()` | Renvoie tous les utilisateurs, du plus récent au plus ancien. |
| `getUtilisateurById($id)` | Renvoie un utilisateur (un tableau) ou `false` s'il n'existe pas. |
| `getUtilisateurByEmail($email)` | Pareil, avec l'e-mail. Utilisée par la connexion. |
| `Create($utilisateur)` | `INSERT` avec les getters du modèle. Le mot de passe arrive déjà haché. Renvoie le nouvel `id` (`lastInsertId`). |
| `Update($utilisateur, $id)` | `UPDATE` du nom, du prénom, de l'e-mail, du téléphone, du rôle et du statut. Ajoute `mot_de_passe` à la requête **seulement s'il est rempli**. |
| `Delete($id)` | `DELETE` de la ligne. |
| `changerMotDePasse($id, $mdp)` | Hache le nouveau mot de passe et l'enregistre (page Mon profil). |
| `changerStatut($id, $statut)` | Met `actif` ou `bloque`. |
| `changerRole($id, $role)` | Met `utilisateur` ou `admin`. |
| `compterAdmins()` | Nombre d'admins actifs. |
| `estDernierAdmin($u)` | `true` si `$u` est le dernier admin actif. On ne doit alors ni le supprimer, ni le bloquer, ni le rétrograder. |
| `verifierConnexion($email, $mdp)` | Cherche le compte par e-mail et compare le mot de passe avec `password_verify`. Renvoie le compte ou `false`. |
| `emailExiste($email, $idExclu)` | `true` si l'e-mail est déjà pris par un **autre** compte. |
| `rechercherUtilisateurs(...)` | Construit le `WHERE` pas à pas (recherche `LIKE`, rôle, statut). Le tri vient d'une liste blanche. |
| `compterUtilisateurs()`, `compterBloques()` | Les chiffres du tableau de bord. |
| `validerMotDePasse($mdp, $conf)` | Au moins 8 caractères, une majuscule, un chiffre, et une confirmation identique. |
| `validerUtilisateur($donnees, ...)` | Vérifie le nom, le prénom, l'e-mail (format et unicité), le téléphone, le rôle, le statut et le mot de passe. Renvoie un tableau `champ => message`, vide si tout est bon. |

## 3. Le parcours : inscription → connexion → Back office

1. **Inscription** (`inscription.php`). Le formulaire contient un champ caché `jeton`.
   `validation.js` vérifie les champs avant l'envoi. En PHP, `verifierFormulaire()` contrôle le jeton, puis `validerUtilisateur()` vérifie les valeurs.
   S'il y a des erreurs, la page se réaffiche avec les messages en rouge et les valeurs gardées.
   Sinon, on crée un objet `Utilisateur` avec `password_hash(...)`, on appelle `Create()`,
   on met « Compte créé avec succès » dans `$_SESSION['message']` et on redirige vers la connexion.
2. **Connexion** (`connexion.php`). `verifierConnexion()` trouve le compte et vérifie le mot de passe.
   Un compte bloqué est refusé. Sinon, `session_regenerate_id(true)` change l'identifiant de session,
   puis on enregistre `id`, `nom`, `prenom`, `email` et `role` dans `$_SESSION['user']`.
   L'admin est redirigé vers `dashboard.php`, l'utilisateur vers l'accueil.
3. **Back office**. Chaque page commence par `exigerAdmin()`. Cette fonction appelle `exigerConnexion()`, qui relit le compte en base
   (bloqué ou supprimé → déconnexion, et le rôle est mis à jour). Si le rôle n'est pas `admin`, elle affiche l'erreur 403.
   L'admin peut ensuite lister, chercher, ajouter, modifier, bloquer, changer le rôle et supprimer.
   Chaque action passe par un formulaire POST avec un jeton, et revient à la liste avec un message.

## 4. Questions de tuteur

1. **Pourquoi séparer le modèle, le contrôleur et la vue ?**
   Chaque fichier a un seul rôle : le modèle transporte les données, le contrôleur parle à la base, la vue affiche. C'est plus facile à lire et à corriger.

2. **À quoi sert `prepare` / `execute` ?**
   La requête et les valeurs sont envoyées séparément. Une valeur comme `' OR '1'='1` reste du texte et ne change pas la requête : c'est la protection contre l'injection SQL.

3. **Pourquoi le tri n'utilise-t-il pas de marqueur `:tri` ?**
   Un marqueur ne peut remplacer qu'une valeur, pas un nom de colonne. On choisit donc le `ORDER BY` dans une liste blanche (`$tris`). Toute autre valeur donne le tri par défaut.

4. **Comment le mot de passe est-il stocké ?**
   Avec `password_hash`, qui donne un hachage `$2y$...` (bcrypt avec un sel aléatoire). Pour la connexion, on utilise `password_verify`. On ne peut pas retrouver le mot de passe à partir du hachage.

5. **Pourquoi `session_regenerate_id(true)` à la connexion ?**
   Il donne un nouvel identifiant de session. Quelqu'un qui connaissait l'ancien identifiant (fixation de session) ne peut donc pas se retrouver connecté.

6. **Comment marche l'expiration de 30 minutes ?**
   À chaque page, on compare `time()` à `$_SESSION['derniere_activite']`. Au-delà de 1800 secondes, on ferme la session et on affiche un message. Sinon, on met l'heure à jour.

7. **Qu'est-ce que le CSRF et comment est-il bloqué ?**
   C'est un autre site qui fait envoyer un formulaire à notre site à l'insu de l'utilisateur. Chaque formulaire contient un jeton aléatoire gardé dans la session. `verifierFormulaire()` refuse (403) si le jeton manque ou ne correspond pas.

8. **Pourquoi `hash_equals` pour comparer le jeton ?**
   Il compare en un temps constant. On ne peut donc pas deviner le jeton caractère par caractère en mesurant le temps de réponse.

9. **Comment le XSS est-il évité ?**
   Tout ce qui vient de l'utilisateur est affiché avec `htmlspecialchars`. `<script>` devient `&lt;script&gt;` et n'est pas exécuté. En plus, la validation refuse les caractères spéciaux dans le nom.

10. **Pourquoi valider en JavaScript ET en PHP ?**
    Le JavaScript aide l'utilisateur avant l'envoi, mais on peut le désactiver ou envoyer une requête sans navigateur. Le PHP est la vraie protection. C'est aussi pour ça que les formulaires ont `novalidate` : on n'utilise pas la validation HTML5.

11. **Que se passe-t-il si un utilisateur simple tape l'URL du Back office ?**
    `exigerAdmin()` affiche la page 403. S'il n'est pas connecté du tout, `exigerConnexion()` le renvoie vers la connexion.

12. **Et si l'admin bloque un compte qui est déjà connecté ?**
    À sa page suivante, `exigerConnexion()` relit le compte en base, voit `bloque`, ferme la session et le renvoie vers la connexion. Un changement de rôle s'applique de la même façon.

13. **Pourquoi un admin ne peut-il pas se supprimer ni se bloquer ?**
    Pour ne pas perdre l'accès au Back office. Plus largement, `estDernierAdmin()` empêche de supprimer, bloquer ou rétrograder le dernier admin actif.

14. **Comment `Update()` sait-il s'il doit changer le mot de passe ?**
    Si `getMotDePasse()` est vide, la requête ne touche pas à la colonne `mot_de_passe`. Sinon, il ajoute `, mot_de_passe = :mot_de_passe` au `SET`. La vue envoie le mot de passe déjà haché, ou une chaîne vide.

15. **Pourquoi la suppression et le blocage se font-ils en POST et pas avec un lien ?**
    Un lien GET peut être déclenché par une simple image ou un clic piégé. En POST avec un jeton, seule l'action volontaire de l'admin sur notre propre page est acceptée. `utilisateur-supprimer.php` ouvert en GET renvoie 403.
