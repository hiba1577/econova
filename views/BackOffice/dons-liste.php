<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireRole('admin');

$partenaireController = new PartenaireController();
$donController = new DonController();
$partenaires = $partenaireController->readPartenaire();

$statuts = [
    'en_attente' => ['En attente', 'text-bg-warning'],
    'valide' => ['Validé', 'text-bg-success'],
    'refuse' => ['Refusé', 'text-bg-danger']
];
$moyens = ['carte' => 'Carte', 'virement' => 'Virement', 'especes' => 'Espèces'];

$statut = $_GET['statut'] ?? '';
$partenaireId = $_GET['partenaire'] ?? '';
if (!isset($statuts[$statut])) {
    $statut = '';
}
if (!in_array((string) $partenaireId, array_map('strval', array_column($partenaires, 'id')), true)) {
    $partenaireId = '';
}
$dons = $donController->readDon($statut, $partenaireId);

$total = 0;
foreach ($dons as $d) {
    if ($d['statut'] === 'valide') {
        $total += $d['montant'];
    }
}

$titre = 'Liste des dons';
$menu = 'dons';
include __DIR__ . '/includes/header.php';
?>

        <div class="card mb-4">
          <div class="card-body">
            <form method="get" action="dons-liste.php" class="row g-3 align-items-end">
              <div class="col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                  <option value="">Tous</option>
                  <?php foreach ($statuts as $cle => $s): ?>
                    <option value="<?= $cle ?>" <?= $statut === $cle ? 'selected' : '' ?>><?= $s[0] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5">
                <label for="partenaire" class="form-label">Partenaire</label>
                <select id="partenaire" name="partenaire" class="form-select">
                  <option value="">Tous les partenaires</option>
                  <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $partenaireId === (string) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nom']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-vivora w-100"><i class="bi bi-funnel"></i> Filtrer</button>
                <a href="dons-liste.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
              </div>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title"><?= count($dons) ?> don(s) - total validé : <strong><?= htmlspecialchars(number_format($total, 2, ',', ' ')) ?> DT</strong></h3>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Donateur</th>
                  <th>Partenaire</th>
                  <th>Montant</th>
                  <th>Paiement</th>
                  <th>Message</th>
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
                    <td><?= htmlspecialchars(number_format($d['montant'], 2, ',', ' ')) ?> DT</td>
                    <td><?= htmlspecialchars($moyens[$d['moyen_paiement']]) ?></td>
                    <td><?= htmlspecialchars($d['message'] ?? '') ?></td>
                    <td><span class="badge <?= $statuts[$d['statut']][1] ?>"><?= $statuts[$d['statut']][0] ?></span></td>
                    <td class="text-end text-nowrap">
                      <?php if ($d['statut'] !== 'valide'): ?>
                        <form method="post" action="don-statut.php" class="d-inline">
                          <?= champCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                          <input type="hidden" name="statut" value="valide">
                          <button type="submit" class="btn btn-sm btn-outline-success" title="Valider"><i class="bi bi-check-lg"></i></button>
                        </form>
                      <?php endif; ?>
                      <?php if ($d['statut'] !== 'refuse'): ?>
                        <form method="post" action="don-statut.php" class="d-inline">
                          <?= champCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                          <input type="hidden" name="statut" value="refuse">
                          <button type="submit" class="btn btn-sm btn-outline-warning" title="Refuser"><i class="bi bi-x-lg"></i></button>
                        </form>
                      <?php endif; ?>
                      <form method="post" action="don-supprimer.php" class="d-inline" onsubmit="return confirm('Supprimer ce don ?');">
                        <?= champCsrf() ?>
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
