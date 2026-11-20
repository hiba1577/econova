<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_only_cookies', 1);
    session_start();
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (isset($_SESSION['derniere_activite']) && time() - $_SESSION['derniere_activite'] > 1800) {
    fermerSession();
    session_start();
    $_SESSION['erreur'] = 'Votre session a expiré après 30 minutes d\'inactivité. Veuillez vous reconnecter.';
}
$_SESSION['derniere_activite'] = time();

function connecterUtilisateur($utilisateur, $profil)
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $utilisateur['id'],
        'nom' => $utilisateur['nom'],
        'prenom' => $utilisateur['prenom'],
        'email' => $utilisateur['email'],
        'role' => $utilisateur['role']
    ];
    majAccessibilite($profil);
    unset($_SESSION['csrf_token']);
}

function majAccessibilite($profil)
{
    $_SESSION['accessibilite'] = [
        'taille_texte' => $profil ? $profil['taille_texte'] : 'normal',
        'contraste_fort' => $profil ? (int) $profil['contraste_fort'] : 0,
        'lecture_facile' => $profil ? (int) $profil['lecture_facile'] : 0
    ];
}

function classesAccessibilite()
{
    if (!isset($_SESSION['accessibilite'])) {
        return '';
    }
    $classes = [];
    $a = $_SESSION['accessibilite'];
    if ($a['taille_texte'] === 'grand') {
        $classes[] = 'texte-grand';
    }
    if ($a['taille_texte'] === 'tres-grand') {
        $classes[] = 'texte-tres-grand';
    }
    if ($a['contraste_fort']) {
        $classes[] = 'contraste-fort';
    }
    if ($a['lecture_facile']) {
        $classes[] = 'lecture-facile';
    }
    return implode(' ', $classes);
}

function estConnecte()
{
    return isset($_SESSION['user']);
}

function requireAuth()
{
    if (!estConnecte()) {
        $_SESSION['erreur'] = 'Veuillez vous connecter pour accéder à cette page.';
        header('Location: ../FrontOffice/connexion.php');
        exit;
    }
}

function requireRole($role)
{
    requireAuth();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        include __DIR__ . '/views/erreur-403.php';
        exit;
    }
}

function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function champCsrf()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken()) . '">';
}

function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function verifierPost()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Requête refusée : jeton CSRF invalide ou absent.');
    }
}

function fermerSession()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
