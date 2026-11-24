<?php
require_once __DIR__ . '/config.php';

// Démarrer une session sécurisée (cookie inaccessible au JavaScript)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Expiration après 30 minutes d'inactivité
if (isset($_SESSION['derniere_activite']) && time() - $_SESSION['derniere_activite'] > 1800) {
    fermerSession();
    session_start();
    $_SESSION['erreur'] = 'Votre session a expiré après 30 minutes. Veuillez vous reconnecter.';
}
$_SESSION['derniere_activite'] = time();

function estConnecte()
{
    return isset($_SESSION['user']);
}

function estAdmin()
{
    return estConnecte() && $_SESSION['user']['role'] === 'admin';
}

// Rediriger vers la connexion si l'utilisateur n'est pas connecté
function exigerConnexion()
{
    if (!estConnecte()) {
        $_SESSION['erreur'] = 'Veuillez vous connecter pour accéder à cette page.';
        header('Location: ../FrontOffice/connexion.php');
        exit;
    }

    // Relire le compte en base : il a pu être bloqué, supprimé ou changer de rôle
    $query = config::getConnexion()->prepare('SELECT role, statut FROM utilisateur WHERE id = :id');
    $query->execute(['id' => $_SESSION['user']['id']]);
    $compte = $query->fetch();
    if (!$compte || $compte['statut'] === 'bloque') {
        fermerSession();
        session_start();
        $_SESSION['erreur'] = 'Votre compte n\'est plus accessible. Contactez l\'administrateur.';
        header('Location: ../FrontOffice/connexion.php');
        exit;
    }
    $_SESSION['user']['role'] = $compte['role'];
}

// Vérifier que l'utilisateur est admin, sinon erreur 403
function exigerAdmin()
{
    exigerConnexion();
    if (!estAdmin()) {
        http_response_code(403);
        include __DIR__ . '/views/erreur-403.php';
        exit;
    }
}

// Jeton CSRF : créé une fois par session
function creerJeton()
{
    if (empty($_SESSION['jeton'])) {
        $_SESSION['jeton'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['jeton'];
}

function verifierJeton($jeton)
{
    return isset($_SESSION['jeton']) && is_string($jeton) && hash_equals($_SESSION['jeton'], $jeton);
}

function champJeton()
{
    return '<input type="hidden" name="jeton" value="' . creerJeton() . '">';
}

// Refuser un formulaire qui n'est pas en POST ou sans jeton valide
function verifierFormulaire()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifierJeton($_POST['jeton'] ?? '')) {
        http_response_code(403);
        exit('Requête refusée : jeton CSRF invalide.');
    }
}

function fermerSession()
{
    $_SESSION = [];
    setcookie(session_name(), '', time() - 3600, '/');
    session_destroy();
}
