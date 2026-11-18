<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

requireRole('admin');

$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$partenaires = $partenaireController->readPartenaire();
$idsValides = array_map('strval', array_column($partenaires, 'id'));

$id = (int) ($_GET['id'] ?? 0);
$publicite = $publiciteController->getPubliciteById($id);
if (!$publicite) {
    $_SESSION['erreur'] = 'Publicité introuvable.';
    header('Location: publicites-liste.php');
    exit;
}

$erreurs = [];
$valeurs = $publicite;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $valeurs = array_merge($publicite, $_POST);
    $erreurs = $publiciteController->valider($_POST, $idsValides);

    if (empty($erreurs)) {
        $upload = $publiciteController->uploadImage($_FILES['image'] ?? null);
        if ($upload['erreur']) {
            $erreurs['image'] = $upload['erreur'];
        } else {
            $image = $publicite['image'];
            if ($upload['nom'] !== null) {
                if ($image && is_file(__DIR__ . '/../../uploads/' . $image)) {
                    unlink(__DIR__ . '/../../uploads/' . $image);
                }
                $image = $upload['nom'];
            }
            $modifiee = new Publicite(
                $id,
                (int) $_POST['partenaire_id'],
                trim($_POST['titre']),
                trim($_POST['description'] ?? ''),
                $image,
                trim($_POST['lien']),
                $_POST['date_debut'],
                $_POST['date_fin'],
                $_POST['statut']
            );
            if ($publiciteController->Update($modifiee, $id)) {
                $_SESSION['message'] = 'La publicité a été modifiée.';
                header('Location: publicites-liste.php');
                exit;
            }
        }
    }
}

$titre = 'Modifier une publicité';
$menu = 'publicites';
include __DIR__ . '/includes/header.php';
?>

        <div class="card card-vivora mb-4">
          <div class="card-header"><h3 class="card-title"><?= htmlspecialchars($publicite['titre']) ?></h3></div>
          <form method="post" action="publicite-modifier.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>
            <?= champCsrf() ?>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="partenaire_id" class="form-label">Partenaire</label>
                  <select id="partenaire_id" name="partenaire_id" class="form-select" data-regle="choix">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($partenaires as $p): ?>
                      <option value="<?= (int) $p['id'] ?>" <?= (string) ($valeurs['partenaire_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['partenaire_id'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="titre" class="form-label">Titre</label>
                  <input type="text" id="titre" name="titre" class="form-control" data-regle="texte" data-min="3" data-max="150" value="<?= htmlspecialchars($valeurs['titre'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['titre'] ?? '') ?></div>
                </div>
                <div class="col-12 mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($valeurs['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="lien" class="form-label">Lien (URL)</label>
                  <input type="url" id="lien" name="lien" class="form-control" data-regle="url" value="<?= htmlspecialchars($valeurs['lien'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['lien'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="image" class="form-label">Nouvelle image (jpg, png ou webp, 2 Mo max)</label>
                  <?php if (!empty($publicite['image'])): ?>
                    <div class="mb-2"><img src="../../uploads/<?= htmlspecialchars($publicite['image']) ?>" alt="Image actuelle" class="vignette"> <span class="small text-muted">Image actuelle</span></div>
                  <?php endif; ?>
                  <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-regle="image">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['image'] ?? '') ?></div>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="date_debut" class="form-label">Date de début</label>
                  <input type="date" id="date_debut" name="date_debut" class="form-control" data-regle="date" value="<?= htmlspecialchars($valeurs['date_debut'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['date_debut'] ?? '') ?></div>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="date_fin" class="form-label">Date de fin</label>
                  <input type="date" id="date_fin" name="date_fin" class="form-control" data-regle="datefin" data-champ="date_debut" value="<?= htmlspecialchars($valeurs['date_fin'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['date_fin'] ?? '') ?></div>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="statut" class="form-label">Statut</label>
                  <select id="statut" name="statut" class="form-select" data-regle="choix">
                    <option value="active" <?= ($valeurs['statut'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($valeurs['statut'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                  </select>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['statut'] ?? '') ?></div>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex gap-2">
              <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Enregistrer</button>
              <a href="publicites-liste.php" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
