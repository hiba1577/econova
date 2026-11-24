<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/DonController.php';

exigerAdmin();

$donController = new DonController();
$id = (int) ($_GET['id'] ?? 0);
$don = $donController->getDonDetail($id);
if (!$don) {
    $_SESSION['erreur'] = 'Don introuvable.';
    header('Location: dons-liste.php');
    exit;
}

$physique = $don['type_don'] === 'physique' && $don['categorie'] !== null;
$statuts = [
    'en_attente' => ['En attente', 'text-bg-warning'],
    'valide' => ['Validé', 'text-bg-success'],
    'refuse' => ['Refusé', 'text-bg-danger'],
    'recu' => ['Reçu', 'text-bg-info']
];
$moyens = ['carte' => 'Carte bancaire', 'virement' => 'Virement', 'especes' => 'Espèces'];
$actions = [
    'valide' => ['Valider', 'btn-success', 'bi-check-lg'],
    'refuse' => ['Refuser', 'btn-warning', 'bi-x-lg'],
    'recu' => ['Marquer comme reçu', 'btn-info', 'bi-box-arrow-in-down']
];
if (!$physique) {
    unset($actions['recu']);
}

$titre = 'Détail du don n° ' . $id;
$menu = $physique ? 'dons-physiques' : 'dons';
include __DIR__ . '/includes/header.php';
?>

        <div class="row">
          <div class="col-lg-7">
            <div class="card card-vivora mb-4">
              <div class="card-header">
                <h3 class="card-title">
                  <?= $physique ? 'Don physique : ' . htmlspecialchars(DonPhysiqueController::CATEGORIES[$don['categorie']]) : 'Don financier' ?>
                </h3>
              </div>
              <div class="card-body">
                <dl class="row mb-0">
                  <dt class="col-sm-4">Statut</dt>
                  <dd class="col-sm-8"><span class="badge <?= $statuts[$don['statut']][1] ?>"><?= $statuts[$don['statut']][0] ?></span></dd>
                  <dt class="col-sm-4">Date du don</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($don['date_don']))) ?></dd>
                  <dt class="col-sm-4">Résumé</dt>
                  <dd class="col-sm-8"><strong><?= htmlspecialchars($donController->detail($don)) ?></strong></dd>

                  <?php if (!$physique): ?>
                    <dt class="col-sm-4">Moyen de paiement</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($moyens[$don['moyen_paiement']] ?? '-') ?></dd>
                    <dt class="col-sm-4">Message</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($don['message'] ?? '-')) ?></dd>
                  <?php endif; ?>

                  <?php if ($physique && $don['categorie'] === 'sang'): ?>
                    <dt class="col-sm-4">Groupe sanguin</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($don['groupe_sanguin']) ?></dd>
                    <dt class="col-sm-4">Disponible le</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y', strtotime($don['date_disponibilite']))) ?></dd>
                    <dt class="col-sm-4">Conditions</dt>
                    <dd class="col-sm-8">Plus de 18 ans, plus de 50 kg, pas de don depuis 3 mois (déclaré)</dd>
                  <?php endif; ?>

                  <?php if ($physique && $don['categorie'] === 'equipement'): ?>
                    <dt class="col-sm-4">Équipement</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(DonPhysiqueController::EQUIPEMENTS[$don['type_equipement']]) ?></dd>
                    <dt class="col-sm-4">État</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(DonPhysiqueController::ETATS[$don['etat']]) ?></dd>
                    <dt class="col-sm-4">Quantité</dt>
                    <dd class="col-sm-8"><?= (int) $don['quantite'] ?></dd>
                    <dt class="col-sm-4">Mode de remise</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(DonPhysiqueController::MODES_REMISE[$don['mode_remise']]) ?></dd>
                    <?php if ($don['adresse_recuperation']): ?>
                      <dt class="col-sm-4">Adresse</dt>
                      <dd class="col-sm-8"><?= htmlspecialchars($don['adresse_recuperation']) ?></dd>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if ($physique && $don['categorie'] === 'medicament'): ?>
                    <dt class="col-sm-4">Médicament</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($don['nom_medicament']) ?></dd>
                    <dt class="col-sm-4">Boîtes</dt>
                    <dd class="col-sm-8"><?= (int) $don['quantite'] ?></dd>
                    <dt class="col-sm-4">Expiration</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y', strtotime($don['date_expiration']))) ?></dd>
                    <dt class="col-sm-4">Emballage</dt>
                    <dd class="col-sm-8"><?= $don['emballage_ferme'] ? 'Fermé et non entamé' : 'Non précisé' ?></dd>
                  <?php endif; ?>

                  <?php if ($physique && $don['categorie'] === 'service'): ?>
                    <dt class="col-sm-4">Domaine</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(DonPhysiqueController::DOMAINES[$don['domaine_service']]) ?></dd>
                    <dt class="col-sm-4">Jours</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(str_replace(',', ', ', $don['disponibilites'])) ?></dd>
                    <dt class="col-sm-4">Heures par mois</dt>
                    <dd class="col-sm-8"><?= (int) $don['heures_par_mois'] ?> h</dd>
                  <?php endif; ?>

                  <?php if ($physique && $don['description']): ?>
                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($don['description'])) ?></dd>
                  <?php endif; ?>
                  <?php if ($physique): ?>
                    <dt class="col-sm-4">Article non mentionné</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($don['autre_article'] ?? '-')) ?></dd>
                  <?php endif; ?>
                </dl>
              </div>
              <div class="card-footer d-flex flex-wrap gap-2">
                <?php foreach ($actions as $nouveau => $a): ?>
                  <?php if ($don['statut'] !== $nouveau): ?>
                    <form method="post" action="don-statut.php">
                      <?= champJeton() ?>
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <input type="hidden" name="statut" value="<?= $nouveau ?>">
                      <input type="hidden" name="retour" value="detail">
                      <button type="submit" class="btn btn-sm <?= $a[1] ?>"><i class="bi <?= $a[2] ?>"></i> <?= $a[0] ?></button>
                    </form>
                  <?php endif; ?>
                <?php endforeach; ?>
                <a href="dons-liste.php<?= $physique ? '?type=physique' : '' ?>" class="btn btn-sm btn-outline-secondary ms-auto">Retour à la liste</a>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="card mb-4">
              <div class="card-header"><h3 class="card-title">Donateur</h3></div>
              <div class="card-body">
                <?php if ($don['utilisateur_nom'] !== null): ?>
                  <p class="mb-1"><strong><?= htmlspecialchars($don['utilisateur_prenom'] . ' ' . $don['utilisateur_nom']) ?></strong></p>
                  <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($don['utilisateur_email']) ?></p>
                  <p class="mb-2"><i class="bi bi-telephone"></i> <?= htmlspecialchars($don['utilisateur_telephone']) ?></p>
                  <a href="utilisateur-detail.php?id=<?= (int) $don['utilisateur_id'] ?>" class="btn btn-sm btn-outline-secondary">Voir l'utilisateur</a>
                <?php else: ?>
                  <p class="mb-0"><em>Compte supprimé</em></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="card mb-4">
              <div class="card-header"><h3 class="card-title">Partenaire</h3></div>
              <div class="card-body">
                <p class="mb-1"><strong><?= htmlspecialchars($don['partenaire_nom']) ?></strong> (<?= htmlspecialchars($don['partenaire_type']) ?>)</p>
                <p class="mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($don['partenaire_adresse']) ?></p>
                <a href="partenaire-detail.php?id=<?= (int) $don['partenaire_id'] ?>" class="btn btn-sm btn-outline-secondary">Voir le partenaire</a>
              </div>
            </div>
          </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
