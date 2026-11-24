<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';

exigerAdmin();

$partenaireController = new PartenaireController();
$id = (int) ($_GET['id'] ?? 0);
$partenaire = $partenaireController->getPartenaireById($id);
if (!$partenaire) {
    $_SESSION['erreur'] = 'Partenaire introuvable.';
    header('Location: partenaires-liste.php');
    exit;
}

$erreurs = [];
$valeurs = $partenaire;
$types = ['association' => 'Association', 'clinique' => 'Clinique', 'pharmacie' => 'Pharmacie', 'sponsor' => 'Sponsor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
    $valeurs = array_merge($partenaire, $_POST);
    $erreurs = $partenaireController->valider($_POST);

    if (empty($erreurs)) {
        $upload = $partenaireController->uploadLogo($_FILES['logo'] ?? null);
        if ($upload['erreur']) {
            $erreurs['logo'] = $upload['erreur'];
        } else {
            $logo = $partenaire['logo'];
            if ($upload['nom'] !== null) {
                if ($logo && is_file(__DIR__ . '/../../uploads/' . $logo)) {
                    unlink(__DIR__ . '/../../uploads/' . $logo);
                }
                $logo = $upload['nom'];
            }
            $modifie = new Partenaire(
                $id,
                trim($_POST['nom']),
                $_POST['type'],
                trim($_POST['email']),
                trim($_POST['telephone']),
                trim($_POST['adresse']),
                trim($_POST['description'] ?? ''),
                $logo,
                $_POST['statut'],
                $_POST['date_debut']
            );
            if ($partenaireController->Update($modifie, $id)) {
                $_SESSION['message'] = 'Le partenaire a été modifié.';
                header('Location: partenaire-detail.php?id=' . $id);
                exit;
            }
        }
    }
}

$titre = 'Modifier un partenaire';
$menu = 'partenaires';
include __DIR__ . '/includes/header.php';
?>

        <div class="card card-vivora mb-4">
          <div class="card-header"><h3 class="card-title"><?= htmlspecialchars($partenaire['nom']) ?></h3></div>
          <form method="post" action="partenaire-modifier.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>
            <?= champJeton() ?>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nom" class="form-label">Nom</label>
                  <input type="text" id="nom" name="nom" class="form-control" data-regle="texte" data-min="2" data-max="100" value="<?= htmlspecialchars($valeurs['nom'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['nom'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="type" class="form-label">Type</label>
                  <select id="type" name="type" class="form-select" data-regle="choix">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($types as $cle => $libelle): ?>
                      <option value="<?= $cle ?>" <?= ($valeurs['type'] ?? '') === $cle ? 'selected' : '' ?>><?= $libelle ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['type'] ?? '') ?></div>
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
                <div class="col-12 mb-3">
                  <label for="adresse" class="form-label">Adresse</label>
                  <input type="text" id="adresse" name="adresse" class="form-control" data-regle="texte" data-min="5" data-max="255" value="<?= htmlspecialchars($valeurs['adresse'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['adresse'] ?? '') ?></div>
                </div>
                <div class="col-12 mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($valeurs['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="logo" class="form-label">Nouveau logo (jpg, png ou webp, 2 Mo max)</label>
                  <?php if (!empty($partenaire['logo'])): ?>
                    <div class="mb-2"><img src="../../uploads/<?= htmlspecialchars($partenaire['logo']) ?>" alt="Logo actuel" class="vignette"> <span class="small text-muted">Logo actuel</span></div>
                  <?php endif; ?>
                  <input type="file" id="logo" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-regle="image">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['logo'] ?? '') ?></div>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="statut" class="form-label">Statut</label>
                  <select id="statut" name="statut" class="form-select" data-regle="choix">
                    <option value="actif" <?= ($valeurs['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= ($valeurs['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                  </select>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['statut'] ?? '') ?></div>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="date_debut" class="form-label">Date de début du partenariat</label>
                  <input type="date" id="date_debut" name="date_debut" class="form-control" data-regle="date" value="<?= htmlspecialchars($valeurs['date_debut'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['date_debut'] ?? '') ?></div>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex gap-2">
              <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Enregistrer</button>
              <a href="partenaire-detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
