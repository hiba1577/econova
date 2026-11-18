<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

requireRole('admin');

$besoins = ['visuel' => 'Visuel', 'moteur' => 'Moteur', 'auditif' => 'Auditif'];
$besoin = $_GET['besoin'] ?? '';
if (!isset($besoins[$besoin])) {
    $besoin = '';
}

$utilisateurController = new UtilisateurController();
$utilisateurs = $utilisateurController->readUtilisateursParBesoin($besoin);
$compteurs = $utilisateurController->compterParBesoin();
$tailles = ['normal' => 'Normal', 'grand' => 'Grand', 'tres-grand' => 'Très grand'];

$titre = 'Utilisateurs par besoin';
$menu = 'par-besoin';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-md-4">
            <div class="small-box text-bg-vivora">
              <div class="inner"><h3><?= (int) $compteurs['visuel'] ?></h3><p>Besoin visuel</p></div>
              <i class="small-box-icon bi bi-eye"></i>
              <a href="utilisateurs-par-besoin.php?besoin=visuel" class="small-box-footer link-light link-underline-opacity-0">Filtrer <i class="bi bi-funnel"></i></a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="small-box text-bg-info">
              <div class="inner"><h3><?= (int) $compteurs['moteur'] ?></h3><p>Besoin moteur</p></div>
              <i class="small-box-icon bi bi-person-wheelchair"></i>
              <a href="utilisateurs-par-besoin.php?besoin=moteur" class="small-box-footer link-light link-underline-opacity-0">Filtrer <i class="bi bi-funnel"></i></a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="small-box text-bg-secondary">
              <div class="inner"><h3><?= (int) $compteurs['auditif'] ?></h3><p>Besoin auditif</p></div>
              <i class="small-box-icon bi bi-ear"></i>
              <a href="utilisateurs-par-besoin.php?besoin=auditif" class="small-box-footer link-light link-underline-opacity-0">Filtrer <i class="bi bi-funnel"></i></a>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">
              <?= $besoin === '' ? 'Tous les utilisateurs ayant au moins un besoin' : 'Besoin ' . htmlspecialchars(strtolower($besoins[$besoin])) ?>
              (<?= count($utilisateurs) ?>)
            </h3>
            <div class="card-tools">
              <a href="utilisateurs-par-besoin.php" class="btn btn-sm <?= $besoin === '' ? 'btn-vivora' : 'btn-outline-secondary' ?>">Tous</a>
              <?php foreach ($besoins as $cle => $libelle): ?>
                <a href="utilisateurs-par-besoin.php?besoin=<?= $cle ?>" class="btn btn-sm <?= $besoin === $cle ? 'btn-vivora' : 'btn-outline-secondary' ?>"><?= $libelle ?></a>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Nom et prénom</th>
                  <th>E-mail</th>
                  <th>Visuel</th>
                  <th>Moteur</th>
                  <th>Auditif</th>
                  <th>Taille du texte</th>
                  <th>Contraste fort</th>
                  <th>Lecture facile</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($utilisateurs)): ?>
                  <tr><td colspan="9" class="text-center">Aucun utilisateur.</td></tr>
                <?php endif; ?>
                <?php foreach ($utilisateurs as $u): ?>
                  <tr>
                    <td><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['besoin_visuel'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '-' ?></td>
                    <td><?= $u['besoin_moteur'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '-' ?></td>
                    <td><?= $u['besoin_auditif'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '-' ?></td>
                    <td><?= htmlspecialchars($tailles[$u['taille_texte']]) ?></td>
                    <td><?= $u['contraste_fort'] ? 'Oui' : 'Non' ?></td>
                    <td><?= $u['lecture_facile'] ? 'Oui' : 'Non' ?></td>
                    <td><a href="utilisateur-detail.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
