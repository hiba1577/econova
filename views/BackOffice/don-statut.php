<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

exigerAdmin();
verifierFormulaire();

$id = (int) ($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$donController = new DonController();
$don = $donController->getDonById($id);
$messages = [
    'en_attente' => 'Le don a été remis en attente.',
    'valide' => 'Le don a été validé.',
    'refuse' => 'Le don a été refusé.',
    'recu' => 'Le don a été marqué comme reçu.'
];

if (!$don || !isset($messages[$statut])) {
    $_SESSION['erreur'] = 'Demande invalide.';
} elseif ($statut === 'recu' && $don['type_don'] !== 'physique') {
    $_SESSION['erreur'] = 'Seul un don physique peut être marqué comme reçu.';
} else {
    $donController->changerStatut($id, $statut);
    $_SESSION['message'] = $messages[$statut];
}

if (($_POST['retour'] ?? '') === 'detail' && $don) {
    header('Location: don-detail.php?id=' . $id);
} else {
    header('Location: dons-liste.php');
}
exit;
