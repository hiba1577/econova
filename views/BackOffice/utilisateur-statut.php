<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$utilisateurController = new UtilisateurController();
$utilisateur = $utilisateurController->getUtilisateurById($id);

if (!$utilisateur || !in_array($statut, ['actif', 'bloque'], true)) {
    $_SESSION['erreur'] = 'Demande invalide.';
} elseif ($id === (int) $_SESSION['user']['id']) {
    $_SESSION['erreur'] = 'Vous ne pouvez pas bloquer votre propre compte.';
} elseif ($statut === 'bloque' && $utilisateurController->estDernierAdmin($utilisateur)) {
    $_SESSION['erreur'] = 'Il doit toujours rester au moins un administrateur.';
} else {
    $utilisateurController->changerStatut($id, $statut);
    $_SESSION['message'] = $statut === 'bloque' ? 'Le compte a été bloqué.' : 'Le compte a été réactivé.';
}

header('Location: utilisateurs-liste.php');
exit;
