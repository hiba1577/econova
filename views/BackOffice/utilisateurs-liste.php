<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

requireRole('admin');

$recherche = trim($_GET['recherche'] ?? '');
$role = $_GET['role'] ?? '';
$statut = $_GET['statut'] ?? '';
$tri = $_GET['tri'] ?? 'recent';
if (!in_array($role, ['utilisateur', 'admin'], true)) {
    $role = '';
}
if (!in_array($statut, ['actif', 'bloque'], true)) {
    $statut = '';
}

$utilisateurController = new UtilisateurController();
$utilisateurs = $utilisateurController->rechercherUtilisateurs($recherche, $role, $statut, $tri);

$titre = 'Liste des utilisateurs';
$menu = 'utilisateurs';
include __DIR__ . '/includes/header.php';
?>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">Rechercher, filtrer et trier</h3>
          </div>
          <div class="card-body">
            <form method="get" action="utilisateurs-liste.php" class="row g-3 align-items-end">
              <div class="col-md-4">
                <label for="recherche" class="form-label">Recherche</label>
                <input type="text" id="recherche" name="recherche" class="form-control" placeholder="Nom, prénom ou e-mail" value="<?= htmlspecialchars($recherche) ?>">
              </div>
              <div class="col-md-2">
                <label for="role" class="form-label">Rôle</label>
                <select id="role" name="role" class="form-select">
                  <option value="">Tous</option>
                  <option value="utilisateur" <?= $role === 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                  <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
              </div>
              <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                  <option value="">Tous</option>
                  <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                  <option value="bloque" <?= $statut === 'bloque' ? 'selected' : '' ?>>Bloqué</option>
                </select>
              </div>
              <div class="col-md-2">
                <label for="tri" class="form-label">Trier par</label>
                <select id="tri" name="tri" class="form-select">
                  <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                  <option value="ancien" <?= $tri === 'ancien' ? 'selected' : '' ?>>Plus anciens</option>
                  <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
                  <option value="email" <?= $tri === 'email' ? 'selected' : '' ?>>E-mail</option>
                </select>
              </div>
              <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-vivora w-100"><i class="bi bi-search"></i> Filtrer</button>
                <a href="utilisateurs-liste.php" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
              </div>
            </form>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title"><?= count($utilisateurs) ?> utilisateur(s)</h3>
            <div class="card-tools">
              <a href="utilisateur-ajout.php" class="btn btn-sm btn-vivora"><i class="bi bi-person-plus"></i> Ajouter</a>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom et prénom</th>
                  <th>E-mail</th>
                  <th>Téléphone</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Besoins</th>
                  <th>Inscription</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($utilisateurs)): ?>
                  <tr><td colspan="9" class="text-center">Aucun utilisateur trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($utilisateurs as $u): ?>
                  <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['telephone']) ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-secondary' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><span class="badge <?= $u['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $u['statut'] === 'actif' ? 'Actif' : 'Bloqué' ?></span></td>
                    <td>
                      <?php if (!empty($u['besoin_visuel'])): ?><i class="bi bi-eye" title="Visuel"></i><?php endif; ?>
                      <?php if (!empty($u['besoin_moteur'])): ?><i class="bi bi-person-wheelchair" title="Moteur"></i><?php endif; ?>
                      <?php if (!empty($u['besoin_auditif'])): ?><i class="bi bi-ear" title="Auditif"></i><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['date_inscription']))) ?></td>
                    <td class="text-end text-nowrap">
                      <a href="utilisateur-detail.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Détail"><i class="bi bi-eye"></i></a>
                      <a href="utilisateur-modifier.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                      <?php if ((int) $u['id'] !== (int) $_SESSION['user']['id']): ?>
                        <form method="post" action="utilisateur-statut.php" class="d-inline">
                          <?= champCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                          <?php if ($u['statut'] === 'actif'): ?>
                            <input type="hidden" name="statut" value="bloque">
                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Bloquer"><i class="bi bi-lock"></i></button>
                          <?php else: ?>
                            <input type="hidden" name="statut" value="actif">
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Réactiver"><i class="bi bi-unlock"></i></button>
                          <?php endif; ?>
                        </form>
                        <form method="post" action="utilisateur-supprimer.php" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur et son profil ?');">
                          <?= champCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
