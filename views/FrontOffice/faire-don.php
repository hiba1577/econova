<?php
require_once __DIR__ . '/../../session.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';
require_once __DIR__ . '/../../controllers/DonController.php';

requireAuth();

$partenaireController = new PartenaireController();
$donController = new DonController();
$donPhysiqueController = new DonPhysiqueController();

$partenaires = $partenaireController->readPartenairesActifs();
$idsValides = [];
$idsSante = [];
foreach ($partenaires as $p) {
    $idsValides[] = (string) $p['id'];
    if (in_array($p['type'], ['clinique', 'pharmacie'], true)) {
        $idsSante[] = (string) $p['id'];
    }
}

$moyens = ['carte' => 'Carte bancaire', 'virement' => 'Virement', 'especes' => 'Espèces'];
$icones = [
    'sang' => 'bi-droplet-fill',
    'equipement' => 'bi-person-wheelchair',
    'medicament' => 'bi-capsule',
    'service' => 'bi-heart-pulse',
    'autre' => 'bi-box-seam'
];
$statuts = [
    'en_attente' => ['En attente', 'bg-warning text-dark'],
    'valide' => ['Validé', 'bg-success'],
    'refuse' => ['Refusé', 'bg-danger'],
    'recu' => ['Reçu', 'bg-info text-dark']
];

$erreurs = [];
$valeurs = [
    'type_don' => $_GET['type'] ?? '',
    'partenaire_id' => $_GET['partenaire'] ?? '',
    'moyen_paiement' => 'carte',
    'date_disponibilite' => date('Y-m-d'),
    'quantite_equipement' => '1',
    'quantite_medicament' => '1',
    'mode_remise' => 'depot',
    'jours' => []
];

$confirmation = null;
if (isset($_GET['confirmation'])) {
    $don = $donController->getDonDetail((int) $_GET['confirmation']);
    if ($don && (int) $don['utilisateur_id'] === (int) $_SESSION['user']['id']) {
        $confirmation = $don;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierPost();
    $valeurs = array_merge($valeurs, $_POST);
    if (!isset($_POST['jours']) || !is_array($_POST['jours'])) {
        $valeurs['jours'] = [];
    }
    $erreurs = $donController->valider($_POST, $idsValides, $idsSante);

    if (empty($erreurs)) {
        $id = false;
        if ($_POST['type_don'] === 'financier') {
            $message = trim($_POST['message'] ?? '');
            $don = new Don(
                null,
                $_SESSION['user']['id'],
                (int) $_POST['partenaire_id'],
                round((float) str_replace(',', '.', $_POST['montant']), 2),
                null,
                $_POST['moyen_paiement'],
                $message === '' ? null : $message,
                'en_attente',
                'financier'
            );
            $id = $donController->Create($don);
        } else {
            $don = new Don(null, $_SESSION['user']['id'], (int) $_POST['partenaire_id'], null, null, null, null, 'en_attente', 'physique');
            $id = $donController->CreatePhysique($don, $donPhysiqueController->depuisFormulaire($_POST));
        }
        if ($id) {
            $_SESSION['message'] = 'Merci pour votre don ! Il sera vérifié par notre équipe.';
            header('Location: faire-don.php?confirmation=' . $id);
            exit;
        }
    }
}

$titre = 'Faire un don';
$sousTitre = 'Soutenez un partenaire de Vivora avec un don financier ou physique';
$pageActive = 'dons';
include __DIR__ . '/includes/header.php';
?>

    <section class="section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-9">

          <?php if ($confirmation): ?>
            <div class="formulaire-vivora">
              <h3 class="mb-3"><i class="bi bi-check-circle"></i> Récapitulatif de votre don</h3>
              <dl class="row mb-0">
                <dt class="col-sm-4">Type</dt>
                <dd class="col-sm-8"><?= $confirmation['type_don'] === 'physique' ? 'Don physique' : 'Don financier' ?></dd>
                <dt class="col-sm-4">Partenaire</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($confirmation['partenaire_nom']) ?></dd>
                <dt class="col-sm-4">Date</dt>
                <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($confirmation['date_don']))) ?></dd>
                <dt class="col-sm-4">Statut</dt>
                <dd class="col-sm-8"><span class="badge <?= $statuts[$confirmation['statut']][1] ?>"><?= $statuts[$confirmation['statut']][0] ?></span></dd>
                <?php if ($confirmation['type_don'] === 'financier'): ?>
                  <dt class="col-sm-4">Montant</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars($donController->detail($confirmation)) ?></dd>
                  <dt class="col-sm-4">Paiement</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars($moyens[$confirmation['moyen_paiement']] ?? '') ?></dd>
                  <?php if (!empty($confirmation['message'])): ?>
                    <dt class="col-sm-4">Message</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($confirmation['message']) ?></dd>
                  <?php endif; ?>
                <?php else: ?>
                  <dt class="col-sm-4">Catégorie</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars(DonPhysiqueController::CATEGORIES[$confirmation['categorie']]) ?></dd>
                  <dt class="col-sm-4">Détail</dt>
                  <dd class="col-sm-8"><?= htmlspecialchars($donController->detail($confirmation)) ?></dd>
                  <?php if ($confirmation['date_disponibilite']): ?>
                    <dt class="col-sm-4">Disponible le</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y', strtotime($confirmation['date_disponibilite']))) ?></dd>
                  <?php endif; ?>
                  <?php if ($confirmation['date_expiration']): ?>
                    <dt class="col-sm-4">Expiration</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y', strtotime($confirmation['date_expiration']))) ?></dd>
                  <?php endif; ?>
                  <?php if ($confirmation['mode_remise']): ?>
                    <dt class="col-sm-4">Remise</dt>
                    <dd class="col-sm-8">
                      <?= htmlspecialchars(DonPhysiqueController::MODES_REMISE[$confirmation['mode_remise']]) ?>
                      <?php if ($confirmation['adresse_recuperation']): ?>, <?= htmlspecialchars($confirmation['adresse_recuperation']) ?><?php endif; ?>
                    </dd>
                  <?php endif; ?>
                  <?php if ($confirmation['disponibilites']): ?>
                    <dt class="col-sm-4">Jours</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(str_replace(',', ', ', $confirmation['disponibilites'])) ?></dd>
                  <?php endif; ?>
                  <?php if ($confirmation['description']): ?>
                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($confirmation['description'])) ?></dd>
                  <?php endif; ?>
                  <?php if ($confirmation['autre_article']): ?>
                    <dt class="col-sm-4">Article non mentionné</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($confirmation['autre_article'])) ?></dd>
                  <?php endif; ?>
                <?php endif; ?>
              </dl>
              <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="mes-dons.php" class="btn btn-vivora">Voir mes dons</a>
                <a href="faire-don.php" class="btn btn-outline-vivora">Faire un autre don</a>
              </div>
            </div>
          <?php else: ?>

            <form method="post" action="faire-don.php" class="formulaire-vivora" novalidate>
              <?= champCsrf() ?>

              <fieldset class="mb-4">
                <legend class="h5">Étape 1 : quel type de don ?</legend>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="carte-choix">
                      <input type="radio" name="type_don" value="financier" data-regle="choix" <?= ($valeurs['type_don'] ?? '') === 'financier' ? 'checked' : '' ?>>
                      <span class="carte-choix-contenu">
                        <i class="bi bi-cash-coin" aria-hidden="true"></i>
                        <strong>Don financier</strong>
                        <small>Un montant par carte, virement ou espèces</small>
                      </span>
                    </label>
                  </div>
                  <div class="col-md-6">
                    <label class="carte-choix">
                      <input type="radio" name="type_don" value="physique" <?= ($valeurs['type_don'] ?? '') === 'physique' ? 'checked' : '' ?>>
                      <span class="carte-choix-contenu">
                        <i class="bi bi-box2-heart" aria-hidden="true"></i>
                        <strong>Don physique</strong>
                        <small>Sang, équipement, médicaments, temps bénévole...</small>
                      </span>
                    </label>
                  </div>
                </div>
                <div class="erreur-champ" id="erreur-type_don"><?= htmlspecialchars($erreurs['type_don'] ?? '') ?></div>
              </fieldset>

              <div class="mb-4">
                <label for="partenaire_id" class="form-label">Partenaire bénéficiaire</label>
                <select id="partenaire_id" name="partenaire_id" class="form-select" data-regle="choix" data-restreindre="1">
                  <option value="">-- Choisir un partenaire --</option>
                  <?php foreach ($partenaires as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= in_array($p['type'], ['clinique', 'pharmacie'], true) ? 'data-sante="1"' : '' ?> <?= (string) ($valeurs['partenaire_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['nom']) ?> (<?= htmlspecialchars($p['type']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text" data-aide-sante>Pour le sang et les médicaments, seuls les cliniques et les pharmacies sont acceptées.</div>
                <div class="erreur-champ"><?= htmlspecialchars($erreurs['partenaire_id'] ?? '') ?></div>
              </div>

              <div class="bloc-don" data-si-nom="type_don" data-si-valeur="financier">
                <h3 class="mb-3"><i class="bi bi-cash-coin"></i> Don financier</h3>
                <div class="mb-3">
                  <label for="montant" class="form-label">Montant (DT)</label>
                  <input type="text" id="montant" name="montant" class="form-control" data-regle="montant" inputmode="decimal" value="<?= htmlspecialchars($valeurs['montant'] ?? '') ?>">
                  <div class="form-text">Entre 1 et 10000 DT.</div>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['montant'] ?? '') ?></div>
                </div>
                <fieldset class="mb-3">
                  <legend class="form-label fs-6">Moyen de paiement</legend>
                  <?php $premier = true; foreach ($moyens as $cle => $libelle): ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="moyen_<?= $cle ?>" name="moyen_paiement" value="<?= $cle ?>" <?= $premier ? 'data-regle="choix"' : '' ?> <?= ($valeurs['moyen_paiement'] ?? '') === $cle ? 'checked' : '' ?>>
                      <label class="form-check-label" for="moyen_<?= $cle ?>"><?= htmlspecialchars($libelle) ?></label>
                    </div>
                  <?php $premier = false; endforeach; ?>
                  <div class="erreur-champ" id="erreur-moyen_paiement"><?= htmlspecialchars($erreurs['moyen_paiement'] ?? '') ?></div>
                </fieldset>
                <div class="mb-3">
                  <label for="message" class="form-label">Message (facultatif)</label>
                  <textarea id="message" name="message" class="form-control" rows="3" data-regle="message"><?= htmlspecialchars($valeurs['message'] ?? '') ?></textarea>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['message'] ?? '') ?></div>
                </div>
              </div>

              <div class="bloc-don" data-si-nom="type_don" data-si-valeur="physique">
                <fieldset class="mb-4">
                  <legend class="h5">Étape 2 : quelle catégorie ?</legend>
                  <div class="row g-3">
                    <?php $premier = true; foreach (DonPhysiqueController::CATEGORIES as $cle => $libelle): ?>
                      <div class="col-6 col-md-4 col-lg">
                        <label class="carte-choix petite">
                          <input type="radio" name="categorie" value="<?= $cle ?>" <?= $premier ? 'data-regle="choix"' : '' ?> <?= ($valeurs['categorie'] ?? '') === $cle ? 'checked' : '' ?>>
                          <span class="carte-choix-contenu">
                            <i class="bi <?= $icones[$cle] ?>" aria-hidden="true"></i>
                            <strong><?= htmlspecialchars($libelle) ?></strong>
                          </span>
                        </label>
                      </div>
                    <?php $premier = false; endforeach; ?>
                  </div>
                  <div class="erreur-champ" id="erreur-categorie"><?= htmlspecialchars($erreurs['categorie'] ?? '') ?></div>
                </fieldset>

                <div class="bloc-categorie" data-si-nom="categorie" data-si-valeur="sang">
                  <h3 class="mb-3"><i class="bi bi-droplet-fill"></i> Don de sang</h3>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="groupe_sanguin" class="form-label">Groupe sanguin</label>
                      <select id="groupe_sanguin" name="groupe_sanguin" class="form-select" data-regle="choix">
                        <option value="">-- Choisir --</option>
                        <?php foreach (DonPhysiqueController::GROUPES_SANGUINS as $groupe): ?>
                          <option value="<?= $groupe ?>" <?= ($valeurs['groupe_sanguin'] ?? '') === $groupe ? 'selected' : '' ?>><?= $groupe ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['groupe_sanguin'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="date_disponibilite" class="form-label">Date de disponibilité</label>
                      <input type="date" id="date_disponibilite" name="date_disponibilite" class="form-control" data-regle="datefuture" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($valeurs['date_disponibilite'] ?? '') ?>">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['date_disponibilite'] ?? '') ?></div>
                    </div>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="age_18" name="age_18" value="1" data-regle="case" <?= !empty($valeurs['age_18']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="age_18">J'ai plus de 18 ans</label>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['age_18'] ?? '') ?></div>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="poids_50" name="poids_50" value="1" data-regle="case" <?= !empty($valeurs['poids_50']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="poids_50">Je pèse plus de 50 kg</label>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['poids_50'] ?? '') ?></div>
                  </div>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="delai_3_mois" name="delai_3_mois" value="1" data-regle="case" <?= !empty($valeurs['delai_3_mois']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="delai_3_mois">Je n'ai pas donné mon sang depuis 3 mois</label>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['delai_3_mois'] ?? '') ?></div>
                  </div>
                </div>

                <div class="bloc-categorie" data-si-nom="categorie" data-si-valeur="equipement">
                  <h3 class="mb-3"><i class="bi bi-person-wheelchair"></i> Équipement médical</h3>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="type_equipement" class="form-label">Type d'équipement</label>
                      <select id="type_equipement" name="type_equipement" class="form-select" data-regle="choix">
                        <option value="">-- Choisir --</option>
                        <?php foreach (DonPhysiqueController::EQUIPEMENTS as $cle => $libelle): ?>
                          <option value="<?= $cle ?>" <?= ($valeurs['type_equipement'] ?? '') === $cle ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['type_equipement'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="quantite_equipement" class="form-label">Quantité</label>
                      <input type="number" id="quantite_equipement" name="quantite_equipement" class="form-control" min="1" max="20" data-regle="entier" data-min="1" data-max="20" value="<?= htmlspecialchars($valeurs['quantite_equipement'] ?? '') ?>">
                      <div class="form-text">De 1 à 20.</div>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['quantite_equipement'] ?? '') ?></div>
                    </div>
                  </div>
                  <fieldset class="mb-3">
                    <legend class="form-label fs-6">État</legend>
                    <?php $premier = true; foreach (DonPhysiqueController::ETATS as $cle => $libelle): ?>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="etat_<?= $cle ?>" name="etat" value="<?= $cle ?>" <?= $premier ? 'data-regle="choix"' : '' ?> <?= ($valeurs['etat'] ?? '') === $cle ? 'checked' : '' ?>>
                        <label class="form-check-label" for="etat_<?= $cle ?>"><?= htmlspecialchars($libelle) ?></label>
                      </div>
                    <?php $premier = false; endforeach; ?>
                    <div class="erreur-champ" id="erreur-etat"><?= htmlspecialchars($erreurs['etat'] ?? '') ?></div>
                  </fieldset>
                  <fieldset class="mb-3">
                    <legend class="form-label fs-6">Mode de remise</legend>
                    <?php $premier = true; foreach (DonPhysiqueController::MODES_REMISE as $cle => $libelle): ?>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="mode_<?= $cle ?>" name="mode_remise" value="<?= $cle ?>" <?= $premier ? 'data-regle="choix"' : '' ?> <?= ($valeurs['mode_remise'] ?? '') === $cle ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mode_<?= $cle ?>"><?= htmlspecialchars($libelle) ?></label>
                      </div>
                    <?php $premier = false; endforeach; ?>
                    <div class="erreur-champ" id="erreur-mode_remise"><?= htmlspecialchars($erreurs['mode_remise'] ?? '') ?></div>
                  </fieldset>
                  <div class="mb-3" data-si-nom="mode_remise" data-si-valeur="recuperation">
                    <label for="adresse_recuperation" class="form-label">Adresse de récupération</label>
                    <input type="text" id="adresse_recuperation" name="adresse_recuperation" class="form-control" data-regle="texte" data-min="5" data-max="200" value="<?= htmlspecialchars($valeurs['adresse_recuperation'] ?? '') ?>">
                    <div class="form-text">Obligatoire seulement pour une récupération à domicile.</div>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['adresse_recuperation'] ?? '') ?></div>
                  </div>
                </div>

                <div class="bloc-categorie" data-si-nom="categorie" data-si-valeur="medicament">
                  <h3 class="mb-3"><i class="bi bi-capsule"></i> Médicaments</h3>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="nom_medicament" class="form-label">Nom du médicament</label>
                      <input type="text" id="nom_medicament" name="nom_medicament" class="form-control" data-regle="texte" data-min="2" data-max="100" value="<?= htmlspecialchars($valeurs['nom_medicament'] ?? '') ?>">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['nom_medicament'] ?? '') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="quantite_medicament" class="form-label">Nombre de boîtes</label>
                      <input type="number" id="quantite_medicament" name="quantite_medicament" class="form-control" min="1" max="50" data-regle="entier" data-min="1" data-max="50" value="<?= htmlspecialchars($valeurs['quantite_medicament'] ?? '') ?>">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['quantite_medicament'] ?? '') ?></div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="date_expiration" class="form-label">Date d'expiration</label>
                      <input type="date" id="date_expiration" name="date_expiration" class="form-control" data-regle="expiration" min="<?= date('Y-m-d', strtotime('+3 months')) ?>" value="<?= htmlspecialchars($valeurs['date_expiration'] ?? '') ?>">
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['date_expiration'] ?? '') ?></div>
                    </div>
                  </div>
                  <p class="form-text mt-0">Le médicament doit expirer au moins 3 mois après aujourd'hui.</p>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="emballage_ferme" name="emballage_ferme" value="1" data-regle="case" <?= !empty($valeurs['emballage_ferme']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="emballage_ferme">Emballage fermé et non entamé</label>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['emballage_ferme'] ?? '') ?></div>
                  </div>
                </div>

                <div class="bloc-categorie" data-si-nom="categorie" data-si-valeur="service">
                  <h3 class="mb-3"><i class="bi bi-heart-pulse"></i> Service de santé bénévole</h3>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="domaine_service" class="form-label">Domaine</label>
                      <select id="domaine_service" name="domaine_service" class="form-select" data-regle="choix">
                        <option value="">-- Choisir --</option>
                        <?php foreach (DonPhysiqueController::DOMAINES as $cle => $libelle): ?>
                          <option value="<?= $cle ?>" <?= ($valeurs['domaine_service'] ?? '') === $cle ? 'selected' : '' ?>><?= htmlspecialchars($libelle) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['domaine_service'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="heures_par_mois" class="form-label">Heures par mois</label>
                      <input type="number" id="heures_par_mois" name="heures_par_mois" class="form-control" min="1" max="100" data-regle="entier" data-min="1" data-max="100" value="<?= htmlspecialchars($valeurs['heures_par_mois'] ?? '') ?>">
                      <div class="form-text">De 1 à 100 heures.</div>
                      <div class="erreur-champ"><?= htmlspecialchars($erreurs['heures_par_mois'] ?? '') ?></div>
                    </div>
                  </div>
                  <fieldset class="mb-3">
                    <legend class="form-label fs-6">Jours disponibles</legend>
                    <?php $premier = true; foreach (DonPhysiqueController::JOURS as $cle => $libelle): ?>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="jour_<?= $cle ?>" name="jours[]" value="<?= $cle ?>" <?= $premier ? 'data-regle="jours"' : '' ?> <?= is_array($valeurs['jours']) && in_array($cle, $valeurs['jours'], true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="jour_<?= $cle ?>"><?= $libelle ?></label>
                      </div>
                    <?php $premier = false; endforeach; ?>
                    <div class="erreur-champ" id="erreur-jours"><?= htmlspecialchars($erreurs['jours'] ?? '') ?></div>
                  </fieldset>
                  <div class="mb-3">
                    <label for="description_service" class="form-label">Expérience ou qualifications</label>
                    <textarea id="description_service" name="description_service" class="form-control" rows="3" data-regle="texte" data-min="10" data-max="500"><?= htmlspecialchars($valeurs['description_service'] ?? '') ?></textarea>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['description_service'] ?? '') ?></div>
                  </div>
                </div>

                <div class="bloc-categorie" data-si-nom="categorie" data-si-valeur="autre">
                  <h3 class="mb-3"><i class="bi bi-box-seam"></i> Autre don</h3>
                  <div class="mb-3">
                    <label for="description_autre" class="form-label">Description du don</label>
                    <textarea id="description_autre" name="description_autre" class="form-control" rows="3" data-regle="texte" data-min="10" data-max="500"><?= htmlspecialchars($valeurs['description_autre'] ?? '') ?></textarea>
                    <div class="form-text">De 10 à 500 caractères.</div>
                    <div class="erreur-champ"><?= htmlspecialchars($erreurs['description_autre'] ?? '') ?></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="autre_article" class="form-label">Autre type ou article non mentionné (facultatif)</label>
                  <textarea id="autre_article" name="autre_article" class="form-control" rows="2" maxlength="300" data-regle="texte" data-min="0" data-max="300" data-optionnel="1" data-compteur="300"><?= htmlspecialchars($valeurs['autre_article'] ?? '') ?></textarea>
                  <div class="erreur-champ"><?= htmlspecialchars($erreurs['autre_article'] ?? '') ?></div>
                </div>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-vivora btn-lg"><i class="bi bi-heart"></i> Valider mon don</button>
              </div>
            </form>
          <?php endif; ?>

          </div>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
