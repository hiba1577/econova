<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';
require_once __DIR__ . '/../../controllers/ProfilAccessibiliteController.php';

requireRole('admin');

$utilisateurController = new UtilisateurController();
$profilController = new ProfilAccessibiliteController();

$id = (int) ($_GET['id'] ?? 0);
$utilisateur = $utilisateurController->getUtilisateurById($id);
if (!$utilisateur) {
    $_SESSION['erreur'] = 'Utilisateur introuvable.';
    header('Location: utilisateurs-liste.php');
    exit;
}

$moiMeme = $id === (int) $_SESSION['user']['id'];
$profil = $profilController->getProfilByUserId($id);
$valeurs = array_merge($utilisateur, $profil ? $profil : ['taille_texte' => 'normal']);
$valeurs['id'] = $utilisateur['id'];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $donnees = $_POST;
    if ($moiMeme) {
        $donnees['role'] = 'admin';
        $donnees['statut'] = 'actif';
    }
    $valeurs = array_merge($valeurs, $donnees);
    $changerMdp = ($donnees['mot_de_passe'] ?? '') !== '';
    $erreurs = array_merge(
        $utilisateurController->valider($donnees, $id, $changerMdp),
        $profilController->valider($donnees)
    );

    if (empty($erreurs)) {
        $modifie = new Utilisateur(
            $id,
            trim($donnees['nom']),
            trim($donnees['prenom']),
            trim($donnees['email']),
            $utilisateur['mot_de_passe'],
            trim($donnees['telephone']),
            $donnees['role'],
            $donnees['statut']
        );
        $utilisateurController->Update($modifie, $id);
        if ($changerMdp) {
            $utilisateurController->changerMotDePasse($id, $donnees['mot_de_passe']);
        }
        $profilController->enregistrer($profilController->depuisFormulaire($donnees, $id));
        if ($moiMeme) {
            $_SESSION['user']['nom'] = $modifie->getNom();
            $_SESSION['user']['prenom'] = $modifie->getPrenom();
            $_SESSION['user']['email'] = $modifie->getEmail();
            majAccessibilite($profilController->getProfilByUserId($id));
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
          <?= champCsrf() ?>
          <div class="row">
            <div class="col-lg-7">
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

            <div class="col-lg-5">
              <div class="card card-vivora mb-4">
                <div class="card-header"><h3 class="card-title">Profil d'accessibilité</h3></div>
                <div class="card-body">
                  <p class="mb-2 fw-semibold">Besoins</p>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="besoin_visuel" name="besoin_visuel" value="1" <?= !empty($valeurs['besoin_visuel']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="besoin_visuel">Visuel</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="besoin_moteur" name="besoin_moteur" value="1" <?= !empty($valeurs['besoin_moteur']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="besoin_moteur">Moteur</label>
                  </div>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="besoin_auditif" name="besoin_auditif" value="1" <?= !empty($valeurs['besoin_auditif']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="besoin_auditif">Auditif</label>
                  </div>
                  <div class="mb-3">
                    <label for="taille_texte" class="form-label">Taille du texte</label>
                    <select id="taille_texte" name="taille_texte" class="form-select" data-regle="choix">
                      <option value="normal" <?= ($valeurs['taille_texte'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                      <option value="grand" <?= ($valeurs['taille_texte'] ?? '') === 'grand' ? 'selected' : '' ?>>Grand</option>
                      <option value="tres-grand" <?= ($valeurs['taille_texte'] ?? '') === 'tres-grand' ? 'selected' : '' ?>>Très grand</option>
                    </select>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['taille_texte'] ?? '') ?></div>
                  </div>
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
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Enregistrer</button>
                <a href="utilisateur-detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
              </div>
            </div>
          </div>
        </form>

<?php include __DIR__ . '/includes/footer.php'; ?>
