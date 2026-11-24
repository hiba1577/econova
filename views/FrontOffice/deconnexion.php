<?php
require_once __DIR__ . '/../../session.php';

if (!verifierJeton($_GET['jeton'] ?? '')) {
    header('Location: index.php');
    exit;
}

fermerSession();
session_start();
$_SESSION['message'] = 'Vous êtes déconnecté. À bientôt sur Vivora !';
header('Location: connexion.php');
exit;
