<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/PubliciteController.php';

requireRole('admin');

$utilisateurController = new UtilisateurController();
$partenaireController = new PartenaireController();
$publiciteController = new PubliciteController();
$nbPartenaires = $partenaireController->compterPartenaires();
$nbPublicites = $publiciteController->compterActives();

$nbUtilisateurs = $utilisateurController->compterUtilisateurs();
$nbBloques = $utilisateurController->compterBloques();
$nbActifs = $nbUtilisateurs - $nbBloques;
$derniersInscrits = array_slice($utilisateurController->rechercherUtilisateurs('', '', '', 'recent'), 0, 5);
$besoins = $utilisateurController->compterParBesoin();

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
            <div class="small-box text-bg-success">
              <div class="inner">
                <h3><?= (int) $nbActifs ?></h3>
                <p>Comptes actifs</p>
              </div>
              <i class="small-box-icon bi bi-person-check"></i>
              <a href="utilisateurs-liste.php?statut=actif" class="small-box-footer link-light link-underline-opacity-0">Voir les comptes <i class="bi bi-arrow-right-circle"></i></a>
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
        </div>

        <div class="row">
          <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">Derniers inscrits</h3>
            <div class="card-tools"><a href="utilisateur-ajout.php" class="btn btn-sm btn-vivora"><i class="bi bi-person-plus"></i> Ajouter</a></div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped mb-0">
              <thead>
                <tr><th>Inscription</th><th>Nom et prénom</th><th>E-mail</th><th>Rôle</th><th>Statut</th></tr>
              </thead>
              <tbody>
                <?php if (empty($derniersInscrits)): ?>
                  <tr><td colspan="5" class="text-center">Aucun utilisateur.</td></tr>
                <?php endif; ?>
                <?php foreach ($derniersInscrits as $u): ?>
                  <tr>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['date_inscription']))) ?></td>
                    <td><a href="utilisateur-detail.php?id=<?= (int) $u['id'] ?>"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></a></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-secondary' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><span class="badge <?= $u['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $u['statut'] === 'actif' ? 'Actif' : 'Bloqué' ?></span></td>
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
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
