<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

exigerAdmin();

$utilisateurController = new UtilisateurController();
$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$donController = new DonController();

$nbUtilisateurs = $utilisateurController->compterUtilisateurs();
$nbBloques = $utilisateurController->compterBloques();
$nbPartenaires = $partenaireController->compterPartenaires();
$nbPublicites = $publiciteController->compterActives();
$totalDons = $donController->totalValide();
$nbEnAttente = $donController->compterEnAttente();
$derniersDons = $donController->derniersDons(5);
$besoins = $utilisateurController->compterParBesoin();
$donPhysiqueController = new DonPhysiqueController();
$nbPhysiques = $donController->compterPhysiques();
$statistiquesPhysiques = $donPhysiqueController->statistiquesParCategorie();

$statuts = [
    'en_attente' => ['En attente', 'text-bg-warning'],
    'valide' => ['Validé', 'text-bg-success'],
    'refuse' => ['Refusé', 'text-bg-danger'],
    'recu' => ['Reçu', 'text-bg-info']
];

$titre = 'Tableau de bord';
$menu = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-vivora">
              <div class="inner">
                <h3><?= (int) $nbUtilisateurs ?></h3>
                <p>Utilisateurs inscrits</p>
              </div>
              <i class="small-box-icon bi bi-people-fill"></i>
              <a href="utilisateurs-liste.php" class="small-box-footer link-light link-underline-opacity-0">Voir la liste <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-danger">
              <div class="inner">
                <h3><?= (int) $nbBloques ?></h3>
                <p>Comptes bloqués</p>
              </div>
              <i class="small-box-icon bi bi-person-lock"></i>
              <a href="utilisateurs-liste.php?statut=bloque" class="small-box-footer link-light link-underline-opacity-0">Voir les comptes <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-info">
              <div class="inner">
                <h3><?= (int) $nbPartenaires ?></h3>
                <p>Partenaires</p>
              </div>
              <i class="small-box-icon bi bi-building"></i>
              <a href="partenaires-liste.php" class="small-box-footer link-light link-underline-opacity-0">Voir les partenaires <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-primary">
              <div class="inner">
                <h3><?= (int) $nbPublicites ?></h3>
                <p>Publicités actives</p>
              </div>
              <i class="small-box-icon bi bi-megaphone"></i>
              <a href="publicites-liste.php" class="small-box-footer link-light link-underline-opacity-0">Voir les publicités <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-success">
              <div class="inner">
                <h3><?= htmlspecialchars(number_format($totalDons, 2, ',', ' ')) ?> <sup class="fs-5">DT</sup></h3>
                <p>Total des dons financiers validés</p>
              </div>
              <i class="small-box-icon bi bi-cash-coin"></i>
              <a href="dons-par-partenaire.php" class="small-box-footer link-light link-underline-opacity-0">Dons par partenaire <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-warning">
              <div class="inner">
                <h3><?= (int) $nbEnAttente ?></h3>
                <p>Dons en attente</p>
              </div>
              <i class="small-box-icon bi bi-hourglass-split"></i>
              <a href="dons-liste.php?statut=en_attente" class="small-box-footer link-dark link-underline-opacity-0">Traiter les dons <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="small-box text-bg-vivora-corail">
              <div class="inner">
                <h3><?= (int) $nbPhysiques ?></h3>
                <p>Dons physiques</p>
              </div>
              <i class="small-box-icon bi bi-box2-heart"></i>
              <a href="dons-liste.php?type=physique" class="small-box-footer link-light link-underline-opacity-0">Voir les dons physiques <i class="bi bi-arrow-right-circle"></i></a>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-8">
            <div class="card mb-4">
              <div class="card-header">
                <h3 class="card-title">Derniers dons</h3>
                <div class="card-tools"><a href="dons-liste.php" class="btn btn-sm btn-vivora">Tous les dons</a></div>
              </div>
              <div class="card-body p-0">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr><th>Date</th><th>Donateur</th><th>Partenaire</th><th>Montant / détail</th><th>Statut</th></tr>
                  </thead>
                  <tbody>
                    <?php if (empty($derniersDons)): ?>
                      <tr><td colspan="5" class="text-center">Aucun don.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($derniersDons as $d): ?>
                      <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($d['date_don']))) ?></td>
                        <td><?= $d['utilisateur_nom'] !== null ? htmlspecialchars($d['utilisateur_prenom'] . ' ' . $d['utilisateur_nom']) : '<em>Compte supprimé</em>' ?></td>
                        <td><?= htmlspecialchars($d['partenaire_nom']) ?></td>
                        <td><a href="don-detail.php?id=<?= (int) $d['id'] ?>"><?= htmlspecialchars($donController->detail($d)) ?></a></td>
                        <td><span class="badge <?= $statuts[$d['statut']][1] ?>"><?= $statuts[$d['statut']][0] ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card mb-4">
              <div class="card-header"><h3 class="card-title">Besoins d'accessibilité</h3></div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-eye"></i> Visuel</span><span class="badge text-bg-vivora"><?= (int) $besoins['visuel'] ?></span></li>
                  <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-person-wheelchair"></i> Moteur</span><span class="badge text-bg-vivora"><?= (int) $besoins['moteur'] ?></span></li>
                  <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-ear"></i> Auditif</span><span class="badge text-bg-vivora"><?= (int) $besoins['auditif'] ?></span></li>
                </ul>
                <a href="utilisateurs-par-besoin.php" class="btn btn-outline-vivora btn-sm mt-3">Voir le détail</a>
              </div>
            </div>
            <div class="card mb-4">
              <div class="card-header"><h3 class="card-title">Dons physiques par catégorie</h3></div>
              <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                  <thead><tr><th>Catégorie</th><th class="text-end">Dons</th><th class="text-end">En attente</th><th class="text-end">Reçus</th></tr></thead>
                  <tbody>
                    <?php foreach ($statistiquesPhysiques as $cle => $s): ?>
                      <tr>
                        <td><a href="dons-liste.php?categorie=<?= $cle ?>"><?= htmlspecialchars($s['libelle']) ?></a></td>
                        <td class="text-end"><?= $s['nombre'] ?></td>
                        <td class="text-end"><?= $s['nombre_attente'] ?></td>
                        <td class="text-end"><?= $s['nombre_recus'] ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
