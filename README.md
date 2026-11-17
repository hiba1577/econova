# Vivora - Module 1 : Gestion des utilisateurs

Groupe Econova - 2A9 - Esprit 2026-2027
Responsable du module : Hiba Sakka
Entités : Utilisateur, ProfilAccessibilite

## Pages HTML

Front office (template Medilab) : `views/FrontOffice/`

- index.html : accueil
- inscription.html : créer un compte avec son profil d'accessibilité
- connexion.html : se connecter
- mon-profil.html : modifier ses informations, son mot de passe et son profil d'accessibilité, supprimer son compte

Back office (template AdminLTE) : `views/BackOffice/`

- dashboard.html : tableau de bord
- utilisateurs-liste.html : liste, recherche, filtre et tri des utilisateurs
- utilisateur-ajout.html : ajouter un utilisateur et son profil
- utilisateur-modifier.html : modifier un utilisateur et son profil
- utilisateur-detail.html : détail d'un utilisateur et de son profil
- utilisateurs-par-besoin.html : utilisateurs par besoin d'accessibilité

## Lancer

Copier le dossier dans `C:\xampp\htdocs\`, démarrer Apache, puis ouvrir :

- http://localhost/vivora/views/FrontOffice/index.html
- http://localhost/vivora/views/BackOffice/dashboard.html
