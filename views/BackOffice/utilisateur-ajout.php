<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();

$utilisateurController = new UtilisateurController();
$erreurs = [];
$valeurs = ['role' => 'utilisateur', 'statut' => 'actif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
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
            $_POST['role'],
            $_POST['statut']
        );
        $id = $utilisateurController->Create($utilisateur);
        if ($id) {
            $_SESSION['message'] = 'L\'utilisateur a été ajouté.';
            header('Location: utilisateur-detail.php?id=' . $id);
            exit;
        }
    }
}

$titre = 'Ajouter un utilisateur';
$menu = 'utilisateur-ajout';
include __DIR__ . '/includes/header.php';
?>

        <form method="post" action="utilisateur-ajout.php" novalidate>
          <?= champJeton() ?>
          <div class="row">
            <div class="col-lg-9">
              <div class="card card-vivora mb-4">
                <div class="card-header"><h3 class="card-title">Compte utilisateur</h3></div>
                <div class="card-body">
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
                      <label for="confirmation" class="form-label">Confirmation</label>
                      <input type="password" id="confirmation" name="confirmation" class="form-control" data-regle="confirmation" data-champ="mot_de_passe">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['confirmation'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="role" class="form-label">Rôle</label>
                      <select id="role" name="role" class="form-select" data-regle="choix">
                        <option value="utilisateur" <?= ($valeurs['role'] ?? '') === 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                        <option value="admin" <?= ($valeurs['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['role'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="statut" class="form-label">Statut</label>
                      <select id="statut" name="statut" class="form-select" data-regle="choix">
                        <option value="actif" <?= ($valeurs['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="bloque" <?= ($valeurs['statut'] ?? '') === 'bloque' ? 'selected' : '' ?>>Bloqué</option>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['statut'] ?? '') ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Ajouter</button>
                <a href="utilisateurs-liste.php" class="btn btn-outline-secondary">Annuler</a>
              </div>
            </div>
          </div>
        </form>

<?php include __DIR__ . '/includes/footer.php'; ?>
