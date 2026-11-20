<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';

$types = ['association' => 'Associations', 'clinique' => 'Cliniques', 'pharmacie' => 'Pharmacies', 'sponsor' => 'Sponsors'];
$type = $_GET['type'] ?? '';
if (!isset($types[$type])) {
    $type = '';
}

$partenaireController = new PartenaireController();
$partenaires = $partenaireController->readPartenairesActifs($type);

$titre = 'Nos partenaires';
$sousTitre = 'Associations, cliniques, pharmacies et sponsors engagés pour l\'inclusion';
$pageActive = 'partenaires';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">

        <div class="filtres-partenaires text-center mb-4">
          <a href="partenaires.php" class="btn <?= $type === '' ? 'btn-vivora' : 'btn-outline-vivora' ?> btn-sm m-1">Tous</a>
          <?php foreach ($types as $cle => $libelle): ?>
            <a href="partenaires.php?type=<?= htmlspecialchars($cle) ?>" class="btn <?= $type === $cle ? 'btn-vivora' : 'btn-outline-vivora' ?> btn-sm m-1"><?= htmlspecialchars($libelle) ?></a>
          <?php endforeach; ?>
        </div>

        <div class="row gy-4">
          <?php if (empty($partenaires)): ?>
            <p class="text-center">Aucun partenaire dans cette catégorie.</p>
          <?php endif; ?>
          <?php foreach ($partenaires as $p): ?>
            <div class="col-lg-4 col-md-6">
              <div class="carte-partenaire h-100">
                <div class="d-flex align-items-center mb-3">
                  <?php if (!empty($p['logo'])): ?>
                    <img src="../../uploads/<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>" class="logo-partenaire me-3">
                  <?php else: ?>
                    <span class="logo-partenaire logo-vide me-3"><i class="bi bi-building-heart"></i></span>
                  <?php endif; ?>
                  <div>
                    <h3 class="h5 mb-1"><?= htmlspecialchars($p['nom']) ?></h3>
                    <span class="badge badge-vivora"><?= htmlspecialchars(ucfirst($p['type'])) ?></span>
                  </div>
                </div>
                <p><?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 140, '...')) ?></p>
                <p class="small mb-3"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($p['adresse']) ?></p>
                <a href="partenaire-detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline-vivora btn-sm">Voir la fiche</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
