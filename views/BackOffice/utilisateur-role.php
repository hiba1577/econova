<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$role = $_POST['role'] ?? '';
$utilisateurController = new UtilisateurController();
$utilisateur = $utilisateurController->getUtilisateurById($id);

if (!$utilisateur || !in_array($role, ['utilisateur', 'admin'], true)) {
    $_SESSION['erreur'] = 'Demande invalide.';
} elseif ($id === (int) $_SESSION['user']['id']) {
    $_SESSION['erreur'] = 'Vous ne pouvez pas changer votre propre rôle.';
} elseif ($role === 'utilisateur' && $utilisateurController->estDernierAdmin($utilisateur)) {
    $_SESSION['erreur'] = 'Il doit toujours rester au moins un administrateur.';
} else {
    $utilisateurController->changerRole($id, $role);
    $_SESSION['message'] = $role === 'admin' ? 'L\'utilisateur est maintenant administrateur.' : 'L\'utilisateur est maintenant un utilisateur simple.';
}

header('Location: utilisateurs-liste.php');
exit;
