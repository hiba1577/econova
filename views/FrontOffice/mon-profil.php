<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';
require_once __DIR__ . '/../../controllers/ProfilAccessibiliteController.php';

requireAuth();

$utilisateurController = new UtilisateurController();
$profilController = new ProfilAccessibiliteController();
$id = $_SESSION['user']['id'];
$utilisateur = $utilisateurController->getUtilisateurById($id);

if (!$utilisateur) {
    fermerSession();
    header('Location: connexion.php');
    exit;
}

$profil = $profilController->getProfilByUserId($id);
$valeurs = $utilisateur;
$valeursProfil = $profil ? $profil : ['besoin_visuel' => 0, 'besoin_moteur' => 0, 'besoin_auditif' => 0, 'taille_texte' => 'normal', 'contraste_fort' => 0, 'lecture_facile' => 0];
$erreursInfos = [];
$erreursMdp = [];
$erreursProfil = [];
$erreursSuppression = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $action = $_POST['action'] ?? '';

    if ($action === 'infos') {
        $valeurs = array_merge($utilisateur, $_POST);
        $erreursInfos = $utilisateurController->valider($_POST, $id, false);
        if (empty($erreursInfos)) {
            $modifie = new Utilisateur(
                $id,
                trim($_POST['nom']),
                trim($_POST['prenom']),
                trim($_POST['email']),
                $utilisateur['mot_de_passe'],
                trim($_POST['telephone']),
                $utilisateur['role'],
                $utilisateur['statut']
            );
            if ($utilisateurController->Update($modifie, $id)) {
                $_SESSION['user']['nom'] = $modifie->getNom();
                $_SESSION['user']['prenom'] = $modifie->getPrenom();
                $_SESSION['user']['email'] = $modifie->getEmail();
                $_SESSION['message'] = 'Vos informations ont été mises à jour.';
                header('Location: mon-profil.php');
                exit;
            }
        }
    }

    if ($action === 'motdepasse') {
        if (!password_verify($_POST['ancien_mot_de_passe'] ?? '', $utilisateur['mot_de_passe'])) {
            $erreursMdp['ancien_mot_de_passe'] = 'L\'ancien mot de passe est incorrect.';
        }
        $erreursMdp = array_merge($erreursMdp, $utilisateurController->validerMotDePasse($_POST['mot_de_passe'] ?? '', $_POST['confirmation'] ?? ''));
        if (empty($erreursMdp)) {
            $utilisateurController->changerMotDePasse($id, $_POST['mot_de_passe']);
            session_regenerate_id(true);
            $_SESSION['message'] = 'Votre mot de passe a été changé.';
            header('Location: mon-profil.php');
            exit;
        }
    }

    if ($action === 'accessibilite') {
        $erreursProfil = $profilController->valider($_POST);
        if (empty($erreursProfil)) {
            $profilController->enregistrer($profilController->depuisFormulaire($_POST, $id));
            majAccessibilite($profilController->getProfilByUserId($id));
            $_SESSION['message'] = 'Votre profil d\'accessibilité a été enregistré.';
            header('Location: mon-profil.php');
            exit;
        }
    }

    if ($action === 'supprimer') {
        if ($utilisateur['role'] === 'admin') {
            $erreursSuppression['general'] = 'Un administrateur ne peut pas supprimer son propre compte.';
        } elseif (!password_verify($_POST['mot_de_passe_suppression'] ?? '', $utilisateur['mot_de_passe'])) {
            $erreursSuppression['mot_de_passe_suppression'] = 'Mot de passe incorrect.';
        } else {
            $utilisateurController->Delete($id);
            fermerSession();
            session_start();
            $_SESSION['message'] = 'Votre compte a été supprimé.';
            header('Location: index.php');
            exit;
        }
    }
}

$titre = 'Mon profil';
$sousTitre = 'Mes informations, mon mot de passe et mon profil d\'accessibilité';
$pageActive = 'profil';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row gy-4">

          <div class="col-lg-6">
            <form method="post" action="mon-profil.php" class="formulaire-vivora h-100" novalidate>
              <?= champCsrf() ?>
              <input type="hidden" name="action" value="infos">
              <h3 class="mb-3"><i class="bi bi-person"></i> Mes informations</h3>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nom" class="form-label">Nom</label>
                  <input type="text" id="nom" name="nom" class="form-control" data-regle="nom" value="<?= htmlspecialchars($valeurs['nom'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreursInfos['nom'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="prenom" class="form-label">Prénom</label>
                  <input type="text" id="prenom" name="prenom" class="form-control" data-regle="nom" value="<?= htmlspecialchars($valeurs['prenom'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreursInfos['prenom'] ?? '') ?></div>
                </div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" data-regle="email" value="<?= htmlspecialchars($valeurs['email'] ?? '') ?>">
                <div class="erreur-champ"><?= htmlspecialchars($erreursInfos['email'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" class="form-control" data-regle="telephone" value="<?= htmlspecialchars($valeurs['telephone'] ?? '') ?>">
                <div class="erreur-champ"><?= htmlspecialchars($erreursInfos['telephone'] ?? '') ?></div>
              </div>
              <p class="small text-muted">Inscrit le <?= htmlspecialchars(date('d/m/Y', strtotime($utilisateur['date_inscription']))) ?></p>
              <button type="submit" class="btn btn-vivora">Enregistrer mes informations</button>
            </form>
          </div>

          <div class="col-lg-6">
            <form method="post" action="mon-profil.php" class="formulaire-vivora h-100" novalidate>
              <?= champCsrf() ?>
              <input type="hidden" name="action" value="motdepasse">
              <h3 class="mb-3"><i class="bi bi-key"></i> Changer mon mot de passe</h3>
              <div class="mb-3">
                <label for="ancien_mot_de_passe" class="form-label">Ancien mot de passe</label>
                <input type="password" id="ancien_mot_de_passe" name="ancien_mot_de_passe" class="form-control" data-regle="requis">
                <div class="erreur-champ"><?= htmlspecialchars($erreursMdp['ancien_mot_de_passe'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="nouveau_mot_de_passe" class="form-label">Nouveau mot de passe</label>
                <input type="password" id="nouveau_mot_de_passe" name="mot_de_passe" class="form-control" data-regle="motdepasse">
                <div class="form-text">8 caractères minimum, une majuscule et un chiffre.</div>
                <div class="erreur-champ"><?= htmlspecialchars($erreursMdp['mot_de_passe'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirmation" name="confirmation" class="form-control" data-regle="confirmation" data-champ="nouveau_mot_de_passe">
                <div class="erreur-champ"><?= htmlspecialchars($erreursMdp['confirmation'] ?? '') ?></div>
              </div>
              <button type="submit" class="btn btn-vivora">Changer le mot de passe</button>
            </form>
          </div>

          <div class="col-lg-6">
            <form method="post" action="mon-profil.php" class="formulaire-vivora h-100" novalidate>
              <?= champCsrf() ?>
              <input type="hidden" name="action" value="accessibilite">
              <h3 class="mb-3"><i class="bi bi-universal-access"></i> Mon profil d'accessibilité</h3>
              <div class="mb-3">
                <span class="form-label d-block">Mes besoins</span>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_visuel" name="besoin_visuel" value="1" <?= !empty($valeursProfil['besoin_visuel']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_visuel">Visuel</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_moteur" name="besoin_moteur" value="1" <?= !empty($valeursProfil['besoin_moteur']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_moteur">Moteur</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="besoin_auditif" name="besoin_auditif" value="1" <?= !empty($valeursProfil['besoin_auditif']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="besoin_auditif">Auditif</label>
                </div>
              </div>
              <div class="mb-3">
                <label for="taille_texte" class="form-label">Taille du texte</label>
                <select id="taille_texte" name="taille_texte" class="form-select" data-regle="choix">
                  <option value="normal" <?= $valeursProfil['taille_texte'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                  <option value="grand" <?= $valeursProfil['taille_texte'] === 'grand' ? 'selected' : '' ?>>Grand</option>
                  <option value="tres-grand" <?= $valeursProfil['taille_texte'] === 'tres-grand' ? 'selected' : '' ?>>Très grand</option>
                </select>
                <div class="erreur-champ"><?= htmlspecialchars($erreursProfil['taille_texte'] ?? '') ?></div>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="contraste_fort" name="contraste_fort" value="1" <?= !empty($valeursProfil['contraste_fort']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="contraste_fort">Contraste fort</label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="lecture_facile" name="lecture_facile" value="1" <?= !empty($valeursProfil['lecture_facile']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="lecture_facile">Lecture facile</label>
              </div>
              <button type="submit" class="btn btn-vivora">Enregistrer mon profil</button>
            </form>
          </div>

          <div class="col-lg-6">
            <form method="post" action="mon-profil.php" class="formulaire-vivora zone-danger h-100" novalidate onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est définitive.');">
              <?= champCsrf() ?>
              <input type="hidden" name="action" value="supprimer">
              <h3 class="mb-3"><i class="bi bi-trash"></i> Supprimer mon compte</h3>
              <?php if (isset($erreursSuppression['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreursSuppression['general']) ?></div>
              <?php endif; ?>
              <?php if ($utilisateur['role'] === 'admin'): ?>
                <p>Un administrateur ne peut pas supprimer son propre compte.</p>
              <?php else: ?>
                <p>Votre compte et votre profil d'accessibilité seront supprimés définitivement.</p>
                <div class="mb-3">
                  <label for="mot_de_passe_suppression" class="form-label">Confirmez avec votre mot de passe</label>
                  <input type="password" id="mot_de_passe_suppression" name="mot_de_passe_suppression" class="form-control" data-regle="requis">
                  <div class="erreur-champ"><?= htmlspecialchars($erreursSuppression['mot_de_passe_suppression'] ?? '') ?></div>
                </div>
                <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
              <?php endif; ?>
            </form>
          </div>

        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
