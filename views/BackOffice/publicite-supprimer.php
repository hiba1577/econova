<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

requireRole('admin');
verifierPost();

$id = (int) ($_POST['id'] ?? 0);
$publiciteController = new PubliciteController();
$publicite = $publiciteController->getPubliciteById($id);

if (!$publicite) {
    $_SESSION['erreur'] = 'Publicité introuvable.';
} else {
    $publiciteController->Delete($id);
    if ($publicite['image'] && is_file(__DIR__ . '/../../uploads/' . $publicite['image'])) {
        unlink(__DIR__ . '/../../uploads/' . $publicite['image']);
    }
    $_SESSION['message'] = 'La publicité a été supprimée.';
}

header('Location: publicites-liste.php');
exit;
