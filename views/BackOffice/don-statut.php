<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireRole('admin');
verifierPost();

$id = (int) ($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$donController = new DonController();

if (!$donController->getDonById($id) || !in_array($statut, ['en_attente', 'valide', 'refuse'], true)) {
    $_SESSION['erreur'] = 'Demande invalide.';
} else {
    $donController->changerStatut($id, $statut);
    $_SESSION['message'] = $statut === 'valide' ? 'Le don a été validé.' : 'Le don a été refusé.';
}

header('Location: dons-liste.php');
exit;
