<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerConnexion();

$utilisateurController = new UtilisateurController();
$id = $_SESSION['user']['id'];
$utilisateur = $utilisateurController->getUtilisateurById($id);

if (!$utilisateur) {
    fermerSession();
    header('Location: connexion.php');
    exit;
}

$valeurs = $utilisateur;
$erreursInfos = [];
$erreursMdp = [];
$erreursSuppression = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
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
                '',
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
$sousTitre = 'Mes informations et mon mot de passe';
$pageActive = 'profil';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row gy-4">

          <div class="col-lg-6">
            <form method="post" action="mon-profil.php" class="formulaire-vivora h-100" novalidate>
              <?= champJeton() ?>
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
              <?= champJeton() ?>
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
            <form method="post" action="mon-profil.php" class="formulaire-vivora zone-danger h-100" novalidate onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est définitive.');">
              <?= champJeton() ?>
              <input type="hidden" name="action" value="supprimer">
              <h3 class="mb-3"><i class="bi bi-trash"></i> Supprimer mon compte</h3>
              <?php if (isset($erreursSuppression['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreursSuppression['general']) ?></div>
              <?php endif; ?>
              <?php if ($utilisateur['role'] === 'admin'): ?>
                <p>Un administrateur ne peut pas supprimer son propre compte.</p>
              <?php else: ?>
                <p>Votre compte sera supprimé définitivement.</p>
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
