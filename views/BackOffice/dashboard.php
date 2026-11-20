<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

requireRole('admin');

$utilisateurController = new UtilisateurController();

$nbUtilisateurs = $utilisateurController->compterUtilisateurs();
$nbBloques = $utilisateurController->compterBloques();
$nbActifs = $nbUtilisateurs - $nbBloques;
$derniersInscrits = array_slice($utilisateurController->rechercherUtilisateurs('', '', '', 'recent'), 0, 5);

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
        </div>

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

<?php include __DIR__ . '/includes/footer.php'; ?>
