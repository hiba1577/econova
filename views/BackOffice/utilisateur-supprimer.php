<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$utilisateurController = new UtilisateurController();
$utilisateur = $utilisateurController->getUtilisateurById($id);

if ($id === (int) $_SESSION['user']['id']) {
    $_SESSION['erreur'] = 'Vous ne pouvez pas supprimer votre propre compte.';
} elseif (!$utilisateur) {
    $_SESSION['erreur'] = 'Utilisateur introuvable.';
} elseif ($utilisateurController->estDernierAdmin($utilisateur)) {
    $_SESSION['erreur'] = 'Il doit toujours rester au moins un administrateur.';
} else {
    $utilisateurController->Delete($id);
    $_SESSION['message'] = 'L\'utilisateur et son profil d\'accessibilité ont été supprimés.';
}

header('Location: utilisateurs-liste.php');
exit;
