<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

exigerAdmin();

$partenaireController = new PartenaireController();
$donController = new DonController();
$partenaires = $partenaireController->readPartenaire();

$statuts = [
    'en_attente' => ['En attente', 'text-bg-warning'],
    'valide' => ['Validé', 'text-bg-success'],
    'refuse' => ['Refusé', 'text-bg-danger'],
    'recu' => ['Reçu', 'text-bg-info']
];
$types = ['financier' => 'Financier', 'physique' => 'Physique'];
$moyens = ['carte' => 'Carte', 'virement' => 'Virement', 'especes' => 'Espèces'];

$statut = $_GET['statut'] ?? '';
$partenaireId = $_GET['partenaire'] ?? '';
$type = $_GET['type'] ?? '';
$categorie = $_GET['categorie'] ?? '';
if (!isset($statuts[$statut])) {
    $statut = '';
}
if (!in_array((string) $partenaireId, array_map('strval', array_column($partenaires, 'id')), true)) {
    $partenaireId = '';
}
if (!isset($types[$type])) {
    $type = '';
}
if (!isset(DonPhysiqueController::CATEGORIES[$categorie])) {
    $categorie = '';
}
if ($categorie !== '') {
    $type = 'physique';
}
$dons = $donController->readDon($statut, $partenaireId, $type, $categorie);

$total = 0;
foreach ($dons as $d) {
    if ($d['type_don'] === 'financier' && $d['statut'] === 'valide') {
        $total += $d['montant'];
    }
}

$titre = $type === 'physique' ? 'Dons physiques' : 'Liste des dons';
$menu = $type === 'physique' ? 'dons-physiques' : 'dons';
include __DIR__ . '/includes/header.php';
?>

        <div class="card mb-4">
          <div class="card-body">
            <form method="get" action="dons-liste.php" class="row g-3 align-items-end">
              <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select id="type" name="type" class="form-select">
                  <option value="">Tous</option>
                  <?php foreach ($types as $cle => $libelle): ?>
                    <option value="<?= $cle ?>" <?= $type === $cle ? 'selected' : '' ?>><?= $libelle ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label for="categorie" class="form-label">Catégorie (dons physiques)</label>
                <select id="categorie" name="categorie" class="form-select">
                  <option value="">Toutes</option>
                  <?php foreach (DonPhysiqueController::CATEGORIES as $cle => $libelle): ?>
                    <option value="<?= $cle ?>" <?= $categorie === $cle ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                  <option value="">Tous</option>
                  <?php foreach ($statuts as $cle => $s): ?>
                    <option value="<?= $cle ?>" <?= $statut === $cle ? 'selected' : '' ?>><?= $s[0] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label for="partenaire" class="form-label">Partenaire</label>
                <select id="partenaire" name="partenaire" class="form-select">
                  <option value="">Tous les partenaires</option>
                  <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $partenaireId === (string) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-vivora w-100"><i class="bi bi-funnel"></i> Filtrer</button>
                <a href="dons-liste.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
              </div>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title"><?= count($dons) ?> don(s) - total financier validé : <strong><?= htmlspecialchars(number_format($total, 2, ',', ' ')) ?> DT</strong></h3>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Donateur</th>
                  <th>Partenaire</th>
                  <th>Type</th>
                  <th>Détail</th>
                  <th>Paiement</th>
                  <th>Statut</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($dons)): ?>
                  <tr><td colspan="8" class="text-center">Aucun don.</td></tr>
                <?php endif; ?>
                <?php foreach ($dons as $d): ?>
                  <tr>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($d['date_don']))) ?></td>
                    <td>
                      <?php if ($d['utilisateur_nom'] !== null): ?>
                        <a href="utilisateur-detail.php?id=<?= (int) $d['utilisateur_id'] ?>"><?= htmlspecialchars($d['utilisateur_prenom'] . ' ' . $d['utilisateur_nom']) ?></a>
                      <?php else: ?>
                        <em>Compte supprimé</em>
                      <?php endif; ?>
                    </td>
                    <td><a href="partenaire-detail.php?id=<?= (int) $d['partenaire_id'] ?>"><?= htmlspecialchars($d['partenaire_nom']) ?></a></td>
                    <td>
                      <?= $d['type_don'] === 'physique' ? 'Physique' : 'Financier' ?>
                      <?php if ($d['categorie'] !== null): ?><br><span class="badge text-bg-light"><?= htmlspecialchars(DonPhysiqueController::CATEGORIES[$d['categorie']]) ?></span><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($donController->detail($d)) ?></td>
                    <td><?= htmlspecialchars($moyens[$d['moyen_paiement']] ?? '-') ?></td>
                    <td><span class="badge <?= $statuts[$d['statut']][1] ?>"><?= $statuts[$d['statut']][0] ?></span></td>
                    <td class="text-end text-nowrap">
                      <a href="don-detail.php?id=<?= (int) $d['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Détail"><i class="bi bi-eye"></i></a>
                      <?php if ($d['statut'] === 'en_attente' || $d['statut'] === 'refuse'): ?>
                        <form method="post" action="don-statut.php" class="d-inline">
                          <?= champJeton() ?>
                          <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                          <input type="hidden" name="statut" value="valide">
                          <button type="submit" class="btn btn-sm btn-outline-success" title="Valider"><i class="bi bi-check-lg"></i></button>
                        </form>
                      <?php endif; ?>
                      <?php if ($d['statut'] === 'en_attente' || $d['statut'] === 'valide'): ?>
                        <form method="post" action="don-statut.php" class="d-inline">
                          <?= champJeton() ?>
                          <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                          <input type="hidden" name="statut" value="refuse">
                          <button type="submit" class="btn btn-sm btn-outline-warning" title="Refuser"><i class="bi bi-x-lg"></i></button>
                        </form>
                      <?php endif; ?>
                      <?php if ($d['type_don'] === 'physique' && $d['statut'] !== 'recu' && $d['statut'] !== 'refuse'): ?>
                        <form method="post" action="don-statut.php" class="d-inline">
                          <?= champJeton() ?>
                          <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                          <input type="hidden" name="statut" value="recu">
                          <button type="submit" class="btn btn-sm btn-outline-info" title="Marquer comme reçu"><i class="bi bi-box-arrow-in-down"></i></button>
                        </form>
                      <?php endif; ?>
                      <form method="post" action="don-supprimer.php" class="d-inline" onsubmit="return confirm('Supprimer ce don ?');">
                        <?= champJeton() ?>
                        <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
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
