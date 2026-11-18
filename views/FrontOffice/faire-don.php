<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireAuth();

$partenaireController = new PartenaireController();
$donController = new DonController();
$partenaires = $partenaireController->readPartenairesActifs();
$idsValides = array_map('strval', array_column($partenaires, 'id'));

$erreurs = [];
$valeurs = ['partenaire_id' => $_GET['partenaire'] ?? '', 'moyen_paiement' => 'carte'];
$moyens = ['carte' => 'Carte bancaire', 'virement' => 'Virement', 'especes' => 'Espèces'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $valeurs = $_POST;
    $erreurs = $donController->valider($_POST, $idsValides);

    if (empty($erreurs)) {
        $message = trim($_POST['message'] ?? '');
        $don = new Don(
            null,
            $_SESSION['user']['id'],
            (int) $_POST['partenaire_id'],
            round((float) str_replace(',', '.', $_POST['montant']), 2),
            null,
            $_POST['moyen_paiement'],
            $message === '' ? null : $message,
            'en_attente'
        );
        if ($donController->Create($don)) {
            $_SESSION['message'] = 'Merci pour votre don ! Il sera validé par notre équipe.';
            header('Location: mes-dons.php');
            exit;
        }
    }
}

$titre = 'Faire un don';
$sousTitre = 'Soutenez un partenaire de Vivora';
$pageActive = 'dons';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-7">
            <form method="post" action="faire-don.php" class="formulaire-vivora" novalidate>
              <?= champCsrf() ?>
              <div class="mb-3">
                <label for="partenaire_id" class="form-label">Partenaire</label>
                <select id="partenaire_id" name="partenaire_id" class="form-select" data-regle="choix">
                  <option value="">-- Choisir un partenaire --</option>
                  <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) ($valeurs['partenaire_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['type']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['partenaire_id'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="montant" class="form-label">Montant (DT)</label>
                <input type="text" id="montant" name="montant" class="form-control" data-regle="montant" inputmode="decimal" value="<?= htmlspecialchars($valeurs['montant'] ?? '') ?>">
                <div class="form-text">Entre 1 et 10000 DT.</div>
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['montant'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <span class="form-label d-block">Moyen de paiement</span>
                <?php foreach ($moyens as $cle => $libelle): ?>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="moyen_<?= $cle ?>" name="moyen_paiement" value="<?= $cle ?>" <?= ($valeurs['moyen_paiement'] ?? '') === $cle ? 'checked' : '' ?>>
                    <label class="form-check-label" for="moyen_<?= $cle ?>"><?= htmlspecialchars($libelle) ?></label>
                  </div>
                <?php endforeach; ?>
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['moyen_paiement'] ?? '') ?></div>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message (facultatif)</label>
                <textarea id="message" name="message" class="form-control" rows="3" data-regle="message"><?= htmlspecialchars($valeurs['message'] ?? '') ?></textarea>
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['message'] ?? '') ?></div>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-vivora btn-lg"><i class="bi bi-heart"></i> Valider mon don</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
