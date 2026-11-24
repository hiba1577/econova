<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();

$utilisateurController = new UtilisateurController();

$id = (int) ($_GET['id'] ?? 0);
$utilisateur = $utilisateurController->getUtilisateurById($id);
if (!$utilisateur) {
    $_SESSION['erreur'] = 'Utilisateur introuvable.';
    header('Location: utilisateurs-liste.php');
    exit;
}

$moiMeme = $id === (int) $_SESSION['user']['id'];
$valeurs = $utilisateur;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
    $donnees = $_POST;
    if ($moiMeme) {
        $donnees['role'] = 'admin';
        $donnees['statut'] = 'actif';
    }
    $valeurs = array_merge($valeurs, $donnees);
    $changerMdp = ($donnees['mot_de_passe'] ?? '') !== '';
    $erreurs = $utilisateurController->valider($donnees, $id, $changerMdp);

    if (empty($erreurs)) {
        $modifie = new Utilisateur(
            $id,
            trim($donnees['nom']),
            trim($donnees['prenom']),
            trim($donnees['email']),
            $changerMdp ? password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT) : '',
            trim($donnees['telephone']),
            $donnees['role'],
            $donnees['statut']
        );
        $utilisateurController->Update($modifie, $id);
        if ($moiMeme) {
            $_SESSION['user']['nom'] = $modifie->getNom();
            $_SESSION['user']['prenom'] = $modifie->getPrenom();
            $_SESSION['user']['email'] = $modifie->getEmail();
        }
        $_SESSION['message'] = 'L\'utilisateur a été modifié.';
        header('Location: utilisateur-detail.php?id=' . $id);
        exit;
    }
}

$titre = 'Modifier un utilisateur';
$menu = 'utilisateurs';
include __DIR__ . '/includes/header.php';
?>

        <form method="post" action="utilisateur-modifier.php?id=<?= $id ?>" novalidate>
          <?= champJeton() ?>
          <div class="row">
            <div class="col-lg-9">
              <div class="card card-vivora mb-4">
                <div class="card-header"><h3 class="card-title">Compte de <?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></h3></div>
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
                      <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                      <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" data-regle="motdepasse" data-optionnel="1">
                      <div class="form-text">Laisser vide pour ne pas le changer.</div>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['mot_de_passe'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="confirmation" class="form-label">Confirmation</label>
                      <input type="password" id="confirmation" name="confirmation" class="form-control" data-regle="confirmation" data-champ="mot_de_passe">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['confirmation'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="role" class="form-label">Rôle</label>
                      <select id="role" name="role" class="form-select" data-regle="choix" <?= $moiMeme ? 'disabled' : '' ?>>
                        <option value="utilisateur" <?= ($valeurs['role'] ?? '') === 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                        <option value="admin" <?= ($valeurs['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['role'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="statut" class="form-label">Statut</label>
                      <select id="statut" name="statut" class="form-select" data-regle="choix" <?= $moiMeme ? 'disabled' : '' ?>>
                        <option value="actif" <?= ($valeurs['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="bloque" <?= ($valeurs['statut'] ?? '') === 'bloque' ? 'selected' : '' ?>>Bloqué</option>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['statut'] ?? '') ?></div>
                    </div>
                    <?php if ($moiMeme): ?>
                      <p class="small text-muted">Vous ne pouvez pas changer votre propre rôle ni bloquer votre compte.</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="utilisateur-detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
              </div>
            </div>
          </div>
        </form>

<?php include __DIR__ . '/includes/footer.php'; ?>
