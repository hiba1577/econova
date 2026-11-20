<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/DonPhysique.php';

class DonPhysiqueController
{
    const CATEGORIES = [
        'sang' => 'Sang',
        'equipement' => 'Équipement médical',
        'medicament' => 'Médicaments',
        'service' => 'Service de santé bénévole',
        'autre' => 'Autre'
    ];

    const GROUPES_SANGUINS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    const EQUIPEMENTS = [
        'fauteuil_roulant' => 'Fauteuil roulant',
        'bequilles' => 'Béquilles',
        'deambulateur' => 'Déambulateur',
        'lit_medicalise' => 'Lit médicalisé',
        'appareil_auditif' => 'Appareil auditif',
        'lunettes' => 'Lunettes',
        'autre' => 'Autre équipement'
    ];

    const ETATS = ['neuf' => 'Neuf', 'bon' => 'Bon état', 'usage' => 'Usagé'];

    const DOMAINES = [
        'medecin' => 'Médecin',
        'infirmier' => 'Infirmier',
        'kinesitherapeute' => 'Kinésithérapeute',
        'psychologue' => 'Psychologue',
        'aide_domicile' => 'Aide à domicile',
        'accompagnement' => 'Accompagnement',
        'transport' => 'Transport',
        'autre' => 'Autre'
    ];

    const JOURS = [
        'lundi' => 'Lundi',
        'mardi' => 'Mardi',
        'mercredi' => 'Mercredi',
        'jeudi' => 'Jeudi',
        'vendredi' => 'Vendredi',
        'samedi' => 'Samedi',
        'dimanche' => 'Dimanche'
    ];

    const MODES_REMISE = ['depot' => 'Dépôt chez le partenaire', 'recuperation' => 'Récupération à domicile'];

    public function readDonPhysique($categorie = '')
    {
        $sql = 'SELECT dp.*, d.statut, d.date_don, d.utilisateur_id, d.partenaire_id,
                u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, p.nom AS partenaire_nom
                FROM don_physique dp
                INNER JOIN don d ON d.id = dp.don_id
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id';
        $params = [];
        if ($categorie !== '') {
            $sql .= ' WHERE dp.categorie = :categorie';
            $params['categorie'] = $categorie;
        }
        $sql .= ' ORDER BY d.date_don DESC';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function getDonPhysiqueById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM don_physique WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function getByDonId($donId)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM don_physique WHERE don_id = :don_id');
            $query->execute(['don_id' => $donId]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Create(DonPhysique $d)
    {
        $sql = 'INSERT INTO don_physique (don_id, categorie, groupe_sanguin, date_disponibilite, type_equipement, etat, quantite,
                nom_medicament, date_expiration, emballage_ferme, domaine_service, disponibilites, heures_par_mois, mode_remise,
                adresse_recuperation, description, autre_article)
                VALUES (:don_id, :categorie, :groupe_sanguin, :date_disponibilite, :type_equipement, :etat, :quantite,
                :nom_medicament, :date_expiration, :emballage_ferme, :domaine_service, :disponibilites, :heures_par_mois, :mode_remise,
                :adresse_recuperation, :description, :autre_article)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(array_merge(['don_id' => $d->getDonId()], $this->parametres($d)));
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(DonPhysique $d, $id)
    {
        $sql = 'UPDATE don_physique SET categorie = :categorie, groupe_sanguin = :groupe_sanguin,
                date_disponibilite = :date_disponibilite, type_equipement = :type_equipement, etat = :etat,
                quantite = :quantite, nom_medicament = :nom_medicament, date_expiration = :date_expiration,
                emballage_ferme = :emballage_ferme, domaine_service = :domaine_service, disponibilites = :disponibilites,
                heures_par_mois = :heures_par_mois, mode_remise = :mode_remise, adresse_recuperation = :adresse_recuperation,
                description = :description, autre_article = :autre_article WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(array_merge(['id' => $id], $this->parametres($d)));
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Delete($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM don_physique WHERE id = :id');
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    private function parametres(DonPhysique $d)
    {
        return [
            'categorie' => $d->getCategorie(),
            'groupe_sanguin' => $d->getGroupeSanguin(),
            'date_disponibilite' => $d->getDateDisponibilite(),
            'type_equipement' => $d->getTypeEquipement(),
            'etat' => $d->getEtat(),
            'quantite' => $d->getQuantite(),
            'nom_medicament' => $d->getNomMedicament(),
            'date_expiration' => $d->getDateExpiration(),
            'emballage_ferme' => $d->getEmballageFerme(),
            'domaine_service' => $d->getDomaineService(),
            'disponibilites' => $d->getDisponibilites(),
            'heures_par_mois' => $d->getHeuresParMois(),
            'mode_remise' => $d->getModeRemise(),
            'adresse_recuperation' => $d->getAdresseRecuperation(),
            'description' => $d->getDescription(),
            'autre_article' => $d->getAutreArticle()
        ];
    }

    public function statistiquesParCategorie()
    {
        $sql = 'SELECT dp.categorie, COUNT(*) AS nombre,
                SUM(CASE WHEN d.statut = :recu THEN 1 ELSE 0 END) AS nombre_recus,
                SUM(CASE WHEN d.statut = :attente THEN 1 ELSE 0 END) AS nombre_attente
                FROM don_physique dp
                INNER JOIN don d ON d.id = dp.don_id
                GROUP BY dp.categorie';
        $statistiques = [];
        foreach (self::CATEGORIES as $cle => $libelle) {
            $statistiques[$cle] = ['libelle' => $libelle, 'nombre' => 0, 'nombre_recus' => 0, 'nombre_attente' => 0];
        }
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['recu' => 'recu', 'attente' => 'en_attente']);
            foreach ($query->fetchAll() as $ligne) {
                $statistiques[$ligne['categorie']]['nombre'] = (int) $ligne['nombre'];
                $statistiques[$ligne['categorie']]['nombre_recus'] = (int) $ligne['nombre_recus'];
                $statistiques[$ligne['categorie']]['nombre_attente'] = (int) $ligne['nombre_attente'];
            }
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
        }
        return $statistiques;
    }

    public function resume($d)
    {
        if ($d['categorie'] === 'sang') {
            return 'Sang ' . $d['groupe_sanguin'];
        }
        if ($d['categorie'] === 'equipement') {
            $etats = ['neuf' => 'neuf', 'bon' => 'bon état', 'usage' => 'usagé'];
            return self::EQUIPEMENTS[$d['type_equipement']] . ' × ' . $d['quantite'] . ' (' . $etats[$d['etat']] . ')';
        }
        if ($d['categorie'] === 'medicament') {
            return $d['nom_medicament'] . ' × ' . $d['quantite'] . ($d['quantite'] > 1 ? ' boîtes' : ' boîte');
        }
        if ($d['categorie'] === 'service') {
            return 'Bénévolat ' . mb_strtolower(self::DOMAINES[$d['domaine_service']]) . ', ' . $d['heures_par_mois'] . ' h/mois';
        }
        return 'Autre : ' . mb_strimwidth($d['description'] ?? '', 0, 60, '...');
    }

    public function depuisFormulaire($donnees, $donId = null)
    {
        $categorie = $donnees['categorie'];
        $d = new DonPhysique(null, $donId, $categorie);
        if ($categorie === 'sang') {
            $d->setGroupeSanguin($donnees['groupe_sanguin']);
            $d->setDateDisponibilite($donnees['date_disponibilite']);
        }
        if ($categorie === 'equipement') {
            $d->setTypeEquipement($donnees['type_equipement']);
            $d->setEtat($donnees['etat']);
            $d->setQuantite((int) $donnees['quantite_equipement']);
            $d->setModeRemise($donnees['mode_remise']);
            if ($donnees['mode_remise'] === 'recuperation') {
                $d->setAdresseRecuperation(trim($donnees['adresse_recuperation']));
            }
        }
        if ($categorie === 'medicament') {
            $d->setNomMedicament(trim($donnees['nom_medicament']));
            $d->setQuantite((int) $donnees['quantite_medicament']);
            $d->setDateExpiration($donnees['date_expiration']);
            $d->setEmballageFerme(1);
        }
        if ($categorie === 'service') {
            $d->setDomaineService($donnees['domaine_service']);
            $d->setDisponibilites(implode(',', $donnees['jours']));
            $d->setHeuresParMois((int) $donnees['heures_par_mois']);
            $d->setDescription(trim($donnees['description_service']));
        }
        if ($categorie === 'autre') {
            $d->setDescription(trim($donnees['description_autre']));
        }
        $autre = trim($donnees['autre_article'] ?? '');
        $d->setAutreArticle($autre === '' ? null : $autre);
        return $d;
    }

    private function dateValide($valeur)
    {
        $date = DateTime::createFromFormat('!Y-m-d', $valeur);
        if (!$date || $date->format('Y-m-d') !== $valeur) {
            return false;
        }
        return $date;
    }

    private function entierEntre($valeur, $min, $max)
    {
        return preg_match('/^[0-9]+$/', $valeur) && (int) $valeur >= $min && (int) $valeur <= $max;
    }

    public function valider($donnees, $partenairesValides, $partenairesSante)
    {
        $erreurs = [];
        $categorie = $donnees['categorie'] ?? '';
        $partenaire = (string) ($donnees['partenaire_id'] ?? '');
        $aujourdhui = new DateTime('today');

        if (!isset(self::CATEGORIES[$categorie])) {
            $erreurs['categorie'] = 'Veuillez choisir une catégorie de don.';
        }
        if (!in_array($partenaire, $partenairesValides, true)) {
            $erreurs['partenaire_id'] = 'Veuillez choisir un partenaire actif.';
        } elseif (in_array($categorie, ['sang', 'medicament'], true) && !in_array($partenaire, $partenairesSante, true)) {
            $erreurs['partenaire_id'] = 'Pour le sang et les médicaments, choisissez une clinique ou une pharmacie.';
        }

        if ($categorie === 'sang') {
            if (!in_array($donnees['groupe_sanguin'] ?? '', self::GROUPES_SANGUINS, true)) {
                $erreurs['groupe_sanguin'] = 'Veuillez choisir votre groupe sanguin.';
            }
            $date = $this->dateValide($donnees['date_disponibilite'] ?? '');
            if (!$date) {
                $erreurs['date_disponibilite'] = 'Date de disponibilité invalide.';
            } elseif ($date < $aujourdhui) {
                $erreurs['date_disponibilite'] = 'La date doit être aujourd\'hui ou plus tard.';
            }
            if (empty($donnees['age_18'])) {
                $erreurs['age_18'] = 'Vous devez avoir plus de 18 ans.';
            }
            if (empty($donnees['poids_50'])) {
                $erreurs['poids_50'] = 'Vous devez peser plus de 50 kg.';
            }
            if (empty($donnees['delai_3_mois'])) {
                $erreurs['delai_3_mois'] = 'Il faut attendre 3 mois entre deux dons de sang.';
            }
        }

        if ($categorie === 'equipement') {
            if (!isset(self::EQUIPEMENTS[$donnees['type_equipement'] ?? ''])) {
                $erreurs['type_equipement'] = 'Veuillez choisir un type d\'équipement.';
            }
            if (!isset(self::ETATS[$donnees['etat'] ?? ''])) {
                $erreurs['etat'] = 'Veuillez choisir l\'état de l\'équipement.';
            }
            if (!$this->entierEntre($donnees['quantite_equipement'] ?? '', 1, 20)) {
                $erreurs['quantite_equipement'] = 'La quantité doit être comprise entre 1 et 20.';
            }
            if (!isset(self::MODES_REMISE[$donnees['mode_remise'] ?? ''])) {
                $erreurs['mode_remise'] = 'Veuillez choisir un mode de remise.';
            } elseif ($donnees['mode_remise'] === 'recuperation') {
                $adresse = trim($donnees['adresse_recuperation'] ?? '');
                if (mb_strlen($adresse) < 5 || mb_strlen($adresse) > 200) {
                    $erreurs['adresse_recuperation'] = 'L\'adresse est obligatoire pour une récupération (5 à 200 caractères).';
                }
            }
        }

        if ($categorie === 'medicament') {
            $nom = trim($donnees['nom_medicament'] ?? '');
            if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
                $erreurs['nom_medicament'] = 'Le nom du médicament doit contenir de 2 à 100 caractères.';
            }
            if (!$this->entierEntre($donnees['quantite_medicament'] ?? '', 1, 50)) {
                $erreurs['quantite_medicament'] = 'Le nombre de boîtes doit être compris entre 1 et 50.';
            }
            $date = $this->dateValide($donnees['date_expiration'] ?? '');
            $limite = (new DateTime('today'))->modify('+3 months');
            if (!$date) {
                $erreurs['date_expiration'] = 'Date d\'expiration invalide.';
            } elseif ($date < $limite) {
                $erreurs['date_expiration'] = 'Le médicament doit expirer au moins 3 mois après aujourd\'hui.';
            }
            if (empty($donnees['emballage_ferme'])) {
                $erreurs['emballage_ferme'] = 'L\'emballage doit être fermé et non entamé.';
            }
        }

        if ($categorie === 'service') {
            if (!isset(self::DOMAINES[$donnees['domaine_service'] ?? ''])) {
                $erreurs['domaine_service'] = 'Veuillez choisir un domaine.';
            }
            $jours = $donnees['jours'] ?? [];
            if (!is_array($jours) || count($jours) === 0 || count(array_diff($jours, array_keys(self::JOURS))) > 0) {
                $erreurs['jours'] = 'Veuillez cocher au moins un jour de disponibilité.';
            }
            if (!$this->entierEntre($donnees['heures_par_mois'] ?? '', 1, 100)) {
                $erreurs['heures_par_mois'] = 'Le nombre d\'heures par mois doit être compris entre 1 et 100.';
            }
            $description = trim($donnees['description_service'] ?? '');
            if (mb_strlen($description) < 10 || mb_strlen($description) > 500) {
                $erreurs['description_service'] = 'Décrivez votre expérience en 10 à 500 caractères.';
            }
        }

        if ($categorie === 'autre') {
            $description = trim($donnees['description_autre'] ?? '');
            if (mb_strlen($description) < 10 || mb_strlen($description) > 500) {
                $erreurs['description_autre'] = 'La description doit contenir de 10 à 500 caractères.';
            }
        }

        if (mb_strlen(trim($donnees['autre_article'] ?? '')) > 300) {
            $erreurs['autre_article'] = 'Le commentaire ne doit pas dépasser 300 caractères.';
        }
        return $erreurs;
    }
}
