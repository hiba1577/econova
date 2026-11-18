<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireRole('admin');

$donController = new DonController();
$lignes = $donController->donsParPartenaire();

$totalGeneral = 0;
$totalAttente = 0;
foreach ($lignes as $l) {
    $totalGeneral += $l['total_valide'];
    $totalAttente += $l['total_attente'];
}

$titre = 'Dons par partenaire';
$menu = 'dons-partenaire';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-md-6">
            <div class="small-box text-bg-success">
              <div class="inner"><h3><?= htmlspecialchars(number_format($totalGeneral, 2, ',', ' ')) ?> <sup class="fs-5">DT</sup></h3><p>Total des dons validés</p></div>
              <i class="small-box-icon bi bi-cash-coin"></i>
              <a href="dons-liste.php?statut=valide" class="small-box-footer link-light link-underline-opacity-0">Voir les dons validés <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="small-box text-bg-warning">
              <div class="inner"><h3><?= htmlspecialchars(number_format($totalAttente, 2, ',', ' ')) ?> <sup class="fs-5">DT</sup></h3><p>Montant en attente de validation</p></div>
              <i class="small-box-icon bi bi-hourglass-split"></i>
              <a href="dons-liste.php?statut=en_attente" class="small-box-footer link-dark link-underline-opacity-0">Traiter les dons <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header"><h3 class="card-title">Total par partenaire</h3></div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Partenaire</th>
                  <th>Type</th>
                  <th>Nombre de dons</th>
                  <th>Dons validés</th>
                  <th>En attente</th>
                  <th>Total validé</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lignes as $l): ?>
                  <tr>
                    <td><?= htmlspecialchars($l['nom']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($l['type'])) ?></td>
                    <td><?= (int) $l['nombre_dons'] ?></td>
                    <td><?= (int) $l['nombre_valides'] ?></td>
                    <td><?= htmlspecialchars(number_format($l['total_attente'], 2, ',', ' ')) ?> DT</td>
                    <td><strong><?= htmlspecialchars(number_format($l['total_valide'], 2, ',', ' ')) ?> DT</strong></td>
                    <td><a href="dons-liste.php?partenaire=<?= (int) $l['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir les dons</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="table-light">
                  <th colspan="5">Total</th>
                  <th><?= htmlspecialchars(number_format($totalGeneral, 2, ',', ' ')) ?> DT</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
