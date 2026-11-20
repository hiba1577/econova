<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();

$id = (int) ($_GET['id'] ?? 0);
$partenaire = $partenaireController->getPartenaireById($id);

if (!$partenaire || $partenaire['statut'] !== 'actif') {
    $_SESSION['erreur'] = 'Ce partenaire est introuvable.';
    header('Location: partenaires.php');
    exit;
}

$publicites = $publiciteController->readPublicitesActives($id);

$titre = $partenaire['nom'];
$sousTitre = ucfirst($partenaire['type']) . ' partenaire de Vivora';
$pageActive = 'partenaires';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4">
            <div class="carte-partenaire text-center">
              <?php if (!empty($partenaire['logo'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($partenaire['logo']) ?>" alt="Logo <?= htmlspecialchars($partenaire['nom']) ?>" class="logo-partenaire grand mb-3">
              <?php else: ?>
                <span class="logo-partenaire logo-vide grand mb-3"><i class="bi bi-building-heart"></i></span>
              <?php endif; ?>
              <h2 class="h4"><?= htmlspecialchars($partenaire['nom']) ?></h2>
              <span class="badge badge-vivora mb-3"><?= htmlspecialchars(ucfirst($partenaire['type'])) ?></span>
              <ul class="list-unstyled text-start mt-3">
                <li class="mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($partenaire['adresse']) ?></li>
                <li class="mb-2"><i class="bi bi-envelope"></i> <?= htmlspecialchars($partenaire['email']) ?></li>
                <li class="mb-2"><i class="bi bi-telephone"></i> <?= htmlspecialchars($partenaire['telephone']) ?></li>
                <li class="mb-2"><i class="bi bi-calendar-check"></i> Partenaire depuis le <?= htmlspecialchars(date('d/m/Y', strtotime($partenaire['date_debut']))) ?></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-8">
            <h3>À propos</h3>
            <p><?= nl2br(htmlspecialchars($partenaire['description'] ?? '')) ?></p>

            <h3 class="mt-4">Offres en cours</h3>
            <?php if (empty($publicites)): ?>
              <p>Ce partenaire n'a pas d'offre en cours.</p>
            <?php endif; ?>
            <div class="row gy-4">
              <?php foreach ($publicites as $pub): ?>
                <div class="col-md-6">
                  <div class="carte-publicite h-100">
                    <?php if (!empty($pub['image'])): ?>
                      <img src="../../uploads/<?= htmlspecialchars($pub['image']) ?>" alt="<?= htmlspecialchars($pub['titre']) ?>" class="img-fluid">
                    <?php endif; ?>
                    <div class="p-3">
                      <h4 class="h5"><?= htmlspecialchars($pub['titre']) ?></h4>
                      <p><?= htmlspecialchars($pub['description'] ?? '') ?></p>
                      <p class="small text-muted">Du <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_debut']))) ?> au <?= htmlspecialchars(date('d/m/Y', strtotime($pub['date_fin']))) ?></p>
                      <a href="<?= htmlspecialchars($pub['lien']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-vivora btn-sm">En savoir plus</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
