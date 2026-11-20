<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

if (estConnecte()) {
    header('Location: mon-profil.php');
    exit;
}

$utilisateurController = new UtilisateurController();
$erreurs = [];
$valeurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $valeurs = $_POST;
    $erreurs = $utilisateurController->valider($_POST);

    if (empty($erreurs)) {
        $utilisateur = new Utilisateur(
            null,
            trim($_POST['nom']),
            trim($_POST['prenom']),
            trim($_POST['email']),
            password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT),
            trim($_POST['telephone']),
            'utilisateur',
            'actif'
        );
        $id = $utilisateurController->Create($utilisateur);
        if ($id) {
            $_SESSION['message'] = 'Votre compte a été créé. Vous pouvez maintenant vous connecter.';
            header('Location: connexion.php');
            exit;
        }
    }
}

$titre = 'Créer un compte';
$sousTitre = 'Un seul compte pour tout Vivora';
$pageActive = 'inscription';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <form method="post" action="inscription.php" class="formulaire-vivora" novalidate>
              <?= champCsrf() ?>

              <h3 class="mb-3">Mes informations</h3>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nom" class="form-label">Nom</label>
                  <input type="text" id="nom" name="nom" class="form-control" data-regle="nom" value="<?= htmlspecialchars($valeurs['nom'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['nom'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="prenom" class="form-label">Prénom</label>
                  <input type="text" id="prenom" name="prenom" class="form-control" data-regle="nom" value="<?= htmlspecialchars($valeurs['prenom'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['prenom'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">E-mail</label>
                  <input type="email" id="email" name="email" class="form-control" data-regle="email" value="<?= htmlspecialchars($valeurs['email'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['email'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="telephone" class="form-label">Téléphone</label>
                  <input type="tel" id="telephone" name="telephone" class="form-control" data-regle="telephone" value="<?= htmlspecialchars($valeurs['telephone'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['telephone'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="mot_de_passe" class="form-label">Mot de passe</label>
                  <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" data-regle="motdepasse">
                  <div class="form-text">8 caractères minimum, une majuscule et un chiffre.</div>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['mot_de_passe'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="confirmation" class="form-label">Confirmer le mot de passe</label>
                  <input type="password" id="confirmation" name="confirmation" class="form-control" data-regle="confirmation" data-champ="mot_de_passe">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['confirmation'] ?? '') ?></div>
                </div>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-vivora btn-lg">Créer mon compte</button>
                <p class="mt-3">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
