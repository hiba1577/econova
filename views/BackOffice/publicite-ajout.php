<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

exigerAdmin();

$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$partenaires = $partenaireController->readPartenaire();
$idsValides = array_map('strval', array_column($partenaires, 'id'));

$erreurs = [];
$valeurs = [
    'partenaire_id' => $_GET['partenaire'] ?? '',
    'statut' => 'active',
    'date_debut' => date('Y-m-d'),
    'date_fin' => date('Y-m-d', strtotime('+30 days'))
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierFormulaire();
    $valeurs = $_POST;
    $erreurs = $publiciteController->valider($_POST, $idsValides);

    if (empty($erreurs)) {
        $upload = $publiciteController->uploadImage($_FILES['image'] ?? null);
        if ($upload['erreur']) {
            $erreurs['image'] = $upload['erreur'];
        } else {
            $publicite = new Publicite(
                null,
                (int) $_POST['partenaire_id'],
                trim($_POST['titre']),
                trim($_POST['description'] ?? ''),
                $upload['nom'],
                trim($_POST['lien']),
                $_POST['date_debut'],
                $_POST['date_fin'],
                $_POST['statut']
            );
            if ($publiciteController->Create($publicite)) {
                $_SESSION['message'] = 'La publicité a été ajoutée.';
                header('Location: publicites-liste.php');
                exit;
            }
        }
    }
}

$titre = 'Ajouter une publicité';
$menu = 'publicite-ajout';
include __DIR__ . '/includes/header.php';
?>

        <div class="card card-vivora mb-4">
          <div class="card-header"><h3 class="card-title">Nouvelle publicité</h3></div>
          <form method="post" action="publicite-ajout.php" enctype="multipart/form-data" novalidate>
            <?= champJeton() ?>
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
                  <input type="url" id="lien" name="lien" class="form-control" data-regle="url" placeholder="https://..." value="<?= htmlspecialchars($valeurs['lien'] ?? '') ?>">
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['lien'] ?? '') ?></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="image" class="form-label">Image (jpg, png ou webp, 2 Mo max)</label>
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
              <button type="submit" class="btn btn-vivora"><i class="bi bi-check-lg"></i> Ajouter</button>
              <a href="publicites-liste.php" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
