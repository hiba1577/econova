<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireRole('admin');

$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$donController = new DonController();

$id = (int) ($_GET['id'] ?? 0);
$partenaire = $partenaireController->getPartenaireById($id);
if (!$partenaire) {
    $_SESSION['erreur'] = 'Partenaire introuvable.';
    header('Location: partenaires-liste.php');
    exit;
}
$publicites = $publiciteController->readPublicite($id);
$dons = $donController->readDon('', $id);

$total = 0;
foreach ($dons as $d) {
    if ($d['type_don'] === 'financier' && $d['statut'] === 'valide') {
        $total += $d['montant'];
    }
}
$statutsDon = [
    'en_attente' => ['En attente', 'text-bg-warning'],
    'valide' => ['Validé', 'text-bg-success'],
    'refuse' => ['Refusé', 'text-bg-danger'],
    'recu' => ['Reçu', 'text-bg-info']
];

$titre = 'Détail du partenaire';
$menu = 'partenaires';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-lg-5">
            <div class="card card-vivora mb-4">
              <div class="card-header"><h3 class="card-title"><?= htmlspecialchars($partenaire['nom']) ?></h3></div>
              <div class="card-body">
                <?php if (!empty($partenaire['logo'])): ?>
                  <img src="../../uploads/<?= htmlspecialchars($partenaire['logo']) ?>" alt="Logo" class="vignette grande mb-3">
                <?php endif; ?>
                <dl class="row mb-0">
                  <dt class="col-sm-4">Type</dt><dd class="col-sm-8"><?= htmlspecialchars(ucfirst($partenaire['type'])) ?></dd>
                  <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><?= htmlspecialchars($partenaire['email']) ?></dd>
                  <dt class="col-sm-4">Téléphone</dt><dd class="col-sm-8"><?= htmlspecialchars($partenaire['telephone']) ?></dd>
                  <dt class="col-sm-4">Adresse</dt><dd class="col-sm-8"><?= htmlspecialchars($partenaire['adresse']) ?></dd>
                  <dt class="col-sm-4">Statut</dt><dd class="col-sm-8"><span class="badge <?= $partenaire['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars(ucfirst($partenaire['statut'])) ?></span></dd>
                  <dt class="col-sm-4">Depuis le</dt><dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y', strtotime($partenaire['date_debut']))) ?></dd>
                  <dt class="col-sm-4">Description</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($partenaire['description'] ?? '')) ?></dd>
                  <dt class="col-sm-4">Dons financiers validés</dt><dd class="col-sm-8"><strong><?= htmlspecialchars(number_format($total, 2, ',', ' ')) ?> DT</strong></dd>
                </dl>
              </div>
              <div class="card-footer d-flex gap-2">
                <a href="partenaire-modifier.php?id=<?= $id ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Modifier</a>
                <form method="post" action="partenaire-supprimer.php" onsubmit="return confirm('Supprimer ce partenaire ? Ses publicités et ses dons seront aussi supprimés.');">
                  <?= champCsrf() ?>
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                </form>
                <a href="partenaires-liste.php" class="btn btn-sm btn-outline-secondary ms-auto">Retour</a>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Publicités (<?= count($publicites) ?>)</h3>
                <div class="card-tools"><a href="publicite-ajout.php?partenaire=<?= $id ?>" class="btn btn-sm btn-vivora"><i class="bi bi-plus-lg"></i> Ajouter</a></div>
              </div>
              <div class="card-body p-0 table-responsive">
                <table class="table table-striped mb-0">
                  <thead><tr><th>Titre</th><th>Période</th><th>Statut</th><th></th></tr></thead>
                  <tbody>
                    <?php if (empty($publicites)): ?>
                      <tr><td colspan="4" class="text-center">Aucune publicité.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($publicites as $pub): ?>
                      <tr>
                        <td><?= htmlspecialchars($pub['titre']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_debut']))) ?> - <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_fin']))) ?></td>
                        <td><span class="badge <?= $pub['statut'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars(ucfirst($pub['statut'])) ?></span></td>
                        <td><a href="publicite-modifier.php?id=<?= (int) $pub['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="card mb-4">
              <div class="card-header"><h3 class="card-title">Dons reçus (<?= count($dons) ?>)</h3></div>
              <div class="card-body p-0 table-responsive">
                <table class="table table-striped mb-0">
                  <thead><tr><th>Date</th><th>Donateur</th><th>Montant / détail</th><th>Statut</th></tr></thead>
                  <tbody>
                    <?php if (empty($dons)): ?>
                      <tr><td colspan="4" class="text-center">Aucun don.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($dons as $d): ?>
                      <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($d['date_don']))) ?></td>
                        <td><?= $d['utilisateur_nom'] !== null ? htmlspecialchars($d['utilisateur_prenom'] . ' ' . $d['utilisateur_nom']) : '<em>Compte supprimé</em>' ?></td>
                        <td><a href="don-detail.php?id=<?= (int) $d['id'] ?>"><?= htmlspecialchars($donController->detail($d)) ?></a></td>
                        <td><span class="badge <?= $statutsDon[$d['statut']][1] ?>"><?= $statutsDon[$d['statut']][0] ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
