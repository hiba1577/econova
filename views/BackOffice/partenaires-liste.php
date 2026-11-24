<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';

exigerAdmin();

$partenaireController = new PartenaireController();
$partenaires = $partenaireController->readPartenaire();

$titre = 'Partenaires';
$menu = 'partenaires';
include __DIR__ . '/includes/header.php';
?>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title"><?= count($partenaires) ?> partenaire(s)</h3>
            <div class="card-tools">
              <a href="partenaire-ajout.php" class="btn btn-sm btn-vivora"><i class="bi bi-plus-lg"></i> Ajouter</a>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Logo</th>
                  <th>Nom</th>
                  <th>Type</th>
                  <th>E-mail</th>
                  <th>Téléphone</th>
                  <th>Statut</th>
                  <th>Depuis</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($partenaires)): ?>
                  <tr><td colspan="8" class="text-center">Aucun partenaire.</td></tr>
                <?php endif; ?>
                <?php foreach ($partenaires as $p): ?>
                  <tr>
                    <td>
                      <?php if (!empty($p['logo'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($p['logo']) ?>" alt="Logo" class="vignette">
                      <?php else: ?>
                        <i class="bi bi-building fs-4 text-secondary"></i>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($p['type'])) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['telephone']) ?></td>
                    <td><span class="badge <?= $p['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= htmlspecialchars(ucfirst($p['statut'])) ?></span></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($p['date_debut']))) ?></td>
                    <td class="text-end text-nowrap">
                      <a href="partenaire-detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Détail"><i class="bi bi-eye"></i></a>
                      <a href="partenaire-modifier.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                      <form method="post" action="partenaire-supprimer.php" class="d-inline" onsubmit="return confirm('Supprimer ce partenaire ? Ses publicités et ses dons seront aussi supprimés.');">
                        <?= champJeton() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
