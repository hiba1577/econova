<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

if (estConnecte()) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? '../BackOffice/dashboard.php' : 'mon-profil.php'));
    exit;
}

$erreurs = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Adresse e-mail invalide.';
    }
    if ($motDePasse === '') {
        $erreurs['mot_de_passe'] = 'Veuillez saisir votre mot de passe.';
    }

    if (empty($erreurs)) {
        $utilisateurController = new UtilisateurController();
        $utilisateur = $utilisateurController->verifierConnexion($email, $motDePasse);
        if (!$utilisateur) {
            $erreurs['general'] = 'E-mail ou mot de passe incorrect.';
        } elseif ($utilisateur['statut'] === 'bloque') {
            $erreurs['general'] = 'Votre compte est bloqué. Contactez l\'administrateur de Vivora.';
        } else {
            connecterUtilisateur($utilisateur);
            $_SESSION['message'] = 'Bienvenue ' . $utilisateur['prenom'] . ' !';
            if ($utilisateur['role'] === 'admin') {
                header('Location: ../BackOffice/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

$titre = 'Connexion';
$sousTitre = 'Accédez à votre espace Vivora';
$pageActive = 'connexion';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 col-md-8">
            <form method="post" action="connexion.php" class="formulaire-vivora" novalidate>
              <?= champCsrf() ?>
              <?php if (isset($erreurs['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreurs['general']) ?></div>
              <?php endif; ?>
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" data-regle="email" value="<?= htmlspecialchars($email) ?>">
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['email'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" data-regle="requis">
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['mot_de_passe'] ?? '') ?></div>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-vivora btn-lg w-100">Se connecter</button>
                <p class="mt-3">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
