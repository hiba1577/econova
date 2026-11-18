<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireAuth();

$donController = new DonController();
$dons = $donController->readDonsByUtilisateur($_SESSION['user']['id']);

$total = 0;
foreach ($dons as $d) {
    if ($d['statut'] === 'valide') {
        $total += $d['montant'];
    }
}

$statuts = [
    'en_attente' => ['En attente', 'bg-warning text-dark'],
    'valide' => ['Validé', 'bg-success'],
    'refuse' => ['Refusé', 'bg-danger']
];
$moyens = ['carte' => 'Carte bancaire', 'virement' => 'Virement', 'especes' => 'Espèces'];

$titre = 'Mes dons';
$sousTitre = 'L\'historique de vos dons aux partenaires de Vivora';
$pageActive = 'dons';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
          <p class="mb-2">Total de vos dons validés : <strong><?= htmlspecialchars(number_format($total, 2, ',', ' ')) ?> DT</strong></p>
          <a href="faire-don.php" class="btn btn-vivora"><i class="bi bi-plus-circle"></i> Faire un nouveau don</a>
        </div>

        <?php if (empty($dons)): ?>
          <p class="text-center">Vous n'avez pas encore fait de don.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle tableau-vivora">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Partenaire</th>
                  <th>Montant</th>
                  <th>Paiement</th>
                  <th>Message</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dons as $d): ?>
                  <tr>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($d['date_don']))) ?></td>
                    <td><a href="partenaire-detail.php?id=<?= (int) $d['partenaire_id'] ?>"><?= htmlspecialchars($d['partenaire_nom']) ?></a></td>
                    <td><?= htmlspecialchars(number_format($d['montant'], 2, ',', ' ')) ?> DT</td>
                    <td><?= htmlspecialchars($moyens[$d['moyen_paiement']]) ?></td>
                    <td><?= htmlspecialchars($d['message'] ?? '') ?></td>
                    <td><span class="badge <?= $statuts[$d['statut']][1] ?>"><?= $statuts[$d['statut']][0] ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
