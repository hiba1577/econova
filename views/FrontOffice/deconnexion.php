<?php
require_once __DIR__ . '/../../session.php';

if (!verifyCsrfToken($_GET['jeton'] ?? '') && !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: index.php');
    exit;
}

fermerSession();
session_start();
$_SESSION['message'] = 'Vous êtes déconnecté. À bientôt sur Vivora !';
header('Location: connexion.php');
exit;
