<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

requireRole('admin');
verifierPost();

$id = (int) ($_POST['id'] ?? 0);
$utilisateurController = new UtilisateurController();

if ($id === (int) $_SESSION['user']['id']) {
    $_SESSION['erreur'] = 'Vous ne pouvez pas supprimer votre propre compte.';
} elseif (!$utilisateurController->getUtilisateurById($id)) {
    $_SESSION['erreur'] = 'Utilisateur introuvable.';
} else {
    $utilisateurController->Delete($id);
    $_SESSION['message'] = 'L\'utilisateur a été supprimé.';
}

header('Location: utilisateurs-liste.php');
exit;
