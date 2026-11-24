<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$publiciteController = new PubliciteController();

if (!$publiciteController->getPubliciteById($id) || !in_array($statut, ['active', 'inactive'], true)) {
    $_SESSION['erreur'] = 'Demande invalide.';
} else {
    $publiciteController->changerStatut($id, $statut);
    $_SESSION['message'] = $statut === 'active' ? 'La publicité a été activée.' : 'La publicité a été désactivée.';
}

header('Location: publicites-liste.php');
exit;
