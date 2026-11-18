<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';
require_once __DIR__ . '/../../controllers/ProfilAccessibiliteController.php';

if (estConnecte()) {
    header('Location: mon-profil.php');
    exit;
}

$utilisateurController = new UtilisateurController();
$profilController = new ProfilAccessibiliteController();
$erreurs = [];
$valeurs = ['taille_texte' => 'normal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $valeurs = $_POST;
    $erreurs = array_merge($utilisateurController->valider($_POST), $profilController->valider($_POST));

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
            $profilController->Create($profilController->depuisFormulaire($_POST, $id));
            $_SESSION['message'] = 'Votre compte a été créé. Vous pouvez maintenant vous connecter.';
            header('Location: connexion.php');
            exit;
        }
    }
}

$titre = 'Créer un compte';
$sousTitre = 'Un compte et un profil d\'accessibilité pour tout Vivora';
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

              <h3 class="mt-4 mb-3">Mon profil d'accessibilité</h3>
              <p class="text-muted">Ces réglages s'appliquent à toutes les pages du site dès votre connexion.</p>
              <div class="mb-3">
                <span class="form-label d-block">Mes besoins</span>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_visuel" name="besoin_visuel" value="1" <?= !empty($valeurs['besoin_visuel']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_visuel">Visuel</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_moteur" name="besoin_moteur" value="1" <?= !empty($valeurs['besoin_moteur']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_moteur">Moteur</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_auditif" name="besoin_auditif" value="1" <?= !empty($valeurs['besoin_auditif']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_auditif">Auditif</label>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="taille_texte" class="form-label">Taille du texte</label>
                  <select id="taille_texte" name="taille_texte" class="form-select" data-regle="choix">
                    <option value="normal" <?= ($valeurs['taille_texte'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="grand" <?= ($valeurs['taille_texte'] ?? '') === 'grand' ? 'selected' : '' ?>>Grand</option>
                    <option value="tres-grand" <?= ($valeurs['taille_texte'] ?? '') === 'tres-grand' ? 'selected' : '' ?>>Très grand</option>
                  </select>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['taille_texte'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3 d-flex flex-column justify-content-end">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="contraste_fort" name="contraste_fort" value="1" <?= !empty($valeurs['contraste_fort']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="contraste_fort">Contraste fort</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="lecture_facile" name="lecture_facile" value="1" <?= !empty($valeurs['lecture_facile']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="lecture_facile">Lecture facile</label>
                  </div>
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
