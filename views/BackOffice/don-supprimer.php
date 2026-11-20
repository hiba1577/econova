<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireRole('admin');
verifierPost();

$id = (int) ($_POST['id'] ?? 0);
$donController = new DonController();

if (!$donController->getDonById($id)) {
    $_SESSION['erreur'] = 'Don introuvable.';
} else {
    $donController->Delete($id);
    $_SESSION['message'] = 'Le don a été supprimé.';
}

header('Location: dons-liste.php');
exit;
