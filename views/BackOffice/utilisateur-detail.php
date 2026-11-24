<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/UtilisateurController.php';

exigerAdmin();

$utilisateurController = new UtilisateurController();

$id = (int) ($_GET['id'] ?? 0);
$utilisateur = $utilisateurController->getUtilisateurById($id);
if (!$utilisateur) {
    $_SESSION['erreur'] = 'Utilisateur introuvable.';
    header('Location: utilisateurs-liste.php');
    exit;
}
$moiMeme = $id === (int) $_SESSION['user']['id'];

$titre = 'Détail de l\'utilisateur';
$menu = 'utilisateurs';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-lg-8">
            <div class="card card-vivora mb-4">
              <div class="card-header">
                <h3 class="card-title"><?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></h3>
              </div>
              <div class="card-body">
                <dl class="row mb-0">
                  <dt class="col-sm-4">Identifiant</dt><dd class="col-sm-8"><?= (int) $utilisateur['id'] ?></dd>
                  <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><?= htmlspecialchars($utilisateur['email']) ?></dd>
                  <dt class="col-sm-4">Téléphone</dt><dd class="col-sm-8"><?= htmlspecialchars($utilisateur['telephone']) ?></dd>
                  <dt class="col-sm-4">Rôle</dt><dd class="col-sm-8"><span class="badge <?= $utilisateur['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-secondary' ?>"><?= htmlspecialchars($utilisateur['role']) ?></span></dd>
                  <dt class="col-sm-4">Statut</dt><dd class="col-sm-8"><span class="badge <?= $utilisateur['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $utilisateur['statut'] === 'actif' ? 'Actif' : 'Bloqué' ?></span></dd>
                  <dt class="col-sm-4">Inscription</dt><dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($utilisateur['date_inscription']))) ?></dd>
                </dl>
              </div>
              <div class="card-footer d-flex flex-wrap gap-2">
                <a href="utilisateur-modifier.php?id=<?= $id ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Modifier</a>
                <?php if (!$moiMeme): ?>
                  <form method="post" action="utilisateur-statut.php">
                    <?= champJeton() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <?php if ($utilisateur['statut'] === 'actif'): ?>
                      <input type="hidden" name="statut" value="bloque">
                      <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-lock"></i> Bloquer</button>
                    <?php else: ?>
                      <input type="hidden" name="statut" value="actif">
                      <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-unlock"></i> Réactiver</button>
                    <?php endif; ?>
                  </form>
                  <form method="post" action="utilisateur-supprimer.php" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                    <?= champJeton() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                  </form>
                <?php endif; ?>
                <a href="utilisateurs-liste.php" class="btn btn-sm btn-outline-secondary ms-auto">Retour à la liste</a>
              </div>
            </div>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
