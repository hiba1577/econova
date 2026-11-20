<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

requireRole('admin');

$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$partenaires = $partenaireController->readPartenaire();

$partenaireId = $_GET['partenaire'] ?? '';
$statut = $_GET['statut'] ?? '';
if (!in_array((string) $partenaireId, array_map('strval', array_column($partenaires, 'id')), true)) {
    $partenaireId = '';
}
if (!in_array($statut, ['active', 'inactive'], true)) {
    $statut = '';
}
$publicites = $publiciteController->readPublicite($partenaireId, $statut);
$aujourdhui = date('Y-m-d');

$titre = 'Publicités';
$menu = 'publicites';
include __DIR__ . '/includes/header.php';
?>

        <div class="card mb-4">
          <div class="card-body">
            <form method="get" action="publicites-liste.php" class="row g-3 align-items-end">
              <div class="col-md-5">
                <label for="partenaire" class="form-label">Partenaire</label>
                <select id="partenaire" name="partenaire" class="form-select">
                  <option value="">Tous les partenaires</option>
                  <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $partenaireId === (string) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                  <option value="">Tous</option>
                  <option value="active" <?= $statut === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $statut === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
              <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-vivora w-100"><i class="bi bi-funnel"></i> Filtrer</button>
                <a href="publicites-liste.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
              </div>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title"><?= count($publicites) ?> publicité(s)</h3>
            <div class="card-tools"><a href="publicite-ajout.php" class="btn btn-sm btn-vivora"><i class="bi bi-plus-lg"></i> Ajouter</a></div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Titre</th>
                  <th>Partenaire</th>
                  <th>Période</th>
                  <th>Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($publicites)): ?>
                  <tr><td colspan="6" class="text-center">Aucune publicité.</td></tr>
                <?php endif; ?>
                <?php foreach ($publicites as $pub): ?>
                  <tr>
                    <td>
                      <?php if (!empty($pub['image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($pub['image']) ?>" alt="" class="vignette">
                      <?php else: ?>
                        <i class="bi bi-image fs-4 text-secondary"></i>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= htmlspecialchars($pub['titre']) ?><br>
                      <a href="<?= htmlspecialchars($pub['lien']) ?>" target="_blank" rel="noopener noreferrer" class="small"><?= htmlspecialchars($pub['lien']) ?></a>
                    </td>
                    <td><a href="partenaire-detail.php?id=<?= (int) $pub['partenaire_id'] ?>"><?= htmlspecialchars($pub['partenaire_nom']) ?></a></td>
                    <td>
                      <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_debut']))) ?> - <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_fin']))) ?>
                      <?php if ($pub['date_fin'] < $aujourdhui): ?><br><span class="badge text-bg-light">Terminée</span><?php endif; ?>
                      <?php if ($pub['date_debut'] > $aujourdhui): ?><br><span class="badge text-bg-light">À venir</span><?php endif; ?>
                    </td>
                    <td><span class="badge <?= $pub['statut'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars(ucfirst($pub['statut'])) ?></span></td>
                    <td class="text-end text-nowrap">
                      <form method="post" action="publicite-statut.php" class="d-inline">
                        <?= champCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $pub['id'] ?>">
                        <?php if ($pub['statut'] === 'active'): ?>
                          <input type="hidden" name="statut" value="inactive">
                          <button type="submit" class="btn btn-sm btn-outline-warning" title="Désactiver"><i class="bi bi-pause-circle"></i></button>
                        <?php else: ?>
                          <input type="hidden" name="statut" value="active">
                          <button type="submit" class="btn btn-sm btn-outline-success" title="Activer"><i class="bi bi-play-circle"></i></button>
                        <?php endif; ?>
                      </form>
                      <a href="publicite-modifier.php?id=<?= (int) $pub['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                      <form method="post" action="publicite-supprimer.php" class="d-inline" onsubmit="return confirm('Supprimer cette publicité ?');">
                        <?= champCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $pub['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
