<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$partenaireController = new PartenaireController();
$partenaire = $partenaireController->getPartenaireById($id);

if (!$partenaire) {
    $_SESSION['erreur'] = 'Partenaire introuvable.';
} else {
    $partenaireController->Delete($id);
    if ($partenaire['logo'] && is_file(__DIR__ . '/../../uploads/' . $partenaire['logo'])) {
        unlink(__DIR__ . '/../../uploads/' . $partenaire['logo']);
    }
    $_SESSION['message'] = 'Le partenaire a été supprimé avec ses publicités et ses dons.';
}

header('Location: partenaires-liste.php');
exit;
