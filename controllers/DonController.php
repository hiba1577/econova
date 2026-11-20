<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/DonPhysiqueController.php';

class DonController
{
    const COLONNES_PHYSIQUE = 'dp.categorie, dp.groupe_sanguin, dp.type_equipement, dp.etat, dp.quantite,
                dp.nom_medicament, dp.domaine_service, dp.heures_par_mois, dp.description';

    public function readDon($statut = '', $partenaireId = '', $type = '', $categorie = '')
    {
        $sql = 'SELECT d.*, u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, u.email AS utilisateur_email,
                p.nom AS partenaire_nom, ' . self::COLONNES_PHYSIQUE . '
                FROM don d
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id
                LEFT JOIN don_physique dp ON dp.don_id = d.id
                WHERE 1 = 1';
        $params = [];
        if ($statut !== '') {
            $sql .= ' AND d.statut = :statut';
            $params['statut'] = $statut;
        }
        if ($partenaireId !== '') {
            $sql .= ' AND d.partenaire_id = :partenaire_id';
            $params['partenaire_id'] = $partenaireId;
        }
        if ($type !== '') {
            $sql .= ' AND d.type_don = :type_don';
            $params['type_don'] = $type;
        }
        if ($categorie !== '') {
            $sql .= ' AND dp.categorie = :categorie';
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

    public function getDonById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM don WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function getDonDetail($id)
    {
        $sql = 'SELECT d.*, dp.id AS don_physique_id, dp.categorie, dp.groupe_sanguin, dp.date_disponibilite,
                dp.type_equipement, dp.etat, dp.quantite, dp.nom_medicament, dp.date_expiration, dp.emballage_ferme,
                dp.domaine_service, dp.disponibilites, dp.heures_par_mois, dp.mode_remise, dp.adresse_recuperation,
                dp.description, dp.autre_article,
                u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, u.email AS utilisateur_email,
                u.telephone AS utilisateur_telephone,
                p.nom AS partenaire_nom, p.type AS partenaire_type, p.adresse AS partenaire_adresse
                FROM don d
                LEFT JOIN don_physique dp ON dp.don_id = d.id
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id
                WHERE d.id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function readDonsByUtilisateur($utilisateurId)
    {
        $sql = 'SELECT d.*, p.nom AS partenaire_nom, p.type AS partenaire_type, ' . self::COLONNES_PHYSIQUE . '
                FROM don d
                INNER JOIN partenaire p ON p.id = d.partenaire_id
                LEFT JOIN don_physique dp ON dp.don_id = d.id
                WHERE d.utilisateur_id = :utilisateur_id
                ORDER BY d.date_don DESC';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['utilisateur_id' => $utilisateurId]);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function Create(Don $don)
    {
        $sql = 'INSERT INTO don (utilisateur_id, partenaire_id, type_don, montant, moyen_paiement, message, statut)
                VALUES (:utilisateur_id, :partenaire_id, :type_don, :montant, :moyen_paiement, :message, :statut)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'utilisateur_id' => $don->getUtilisateurId(),
                'partenaire_id' => $don->getPartenaireId(),
                'type_don' => $don->getTypeDon(),
                'montant' => $don->getMontant(),
                'moyen_paiement' => $don->getMoyenPaiement(),
                'message' => $don->getMessage(),
                'statut' => $don->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function CreatePhysique(Don $don, DonPhysique $donPhysique)
    {
        $db = config::getConnexion();
        $donPhysiqueController = new DonPhysiqueController();
        $don->setTypeDon('physique');
        $don->setMontant(null);
        $don->setMoyenPaiement(null);
        try {
            $db->beginTransaction();
            $donId = $this->Create($don);
            if (!$donId) {
                throw new Exception('le don n\'a pas pu être enregistré.');
            }
            $donPhysique->setDonId($donId);
            if (!$donPhysiqueController->Create($donPhysique)) {
                throw new Exception('le détail du don physique n\'a pas pu être enregistré.');
            }
            $db->commit();
            return $donId;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(Don $don, $id)
    {
        $sql = 'UPDATE don SET partenaire_id = :partenaire_id, type_don = :type_don, montant = :montant,
                moyen_paiement = :moyen_paiement, message = :message, statut = :statut WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'partenaire_id' => $don->getPartenaireId(),
                'type_don' => $don->getTypeDon(),
                'montant' => $don->getMontant(),
                'moyen_paiement' => $don->getMoyenPaiement(),
                'message' => $don->getMessage(),
                'statut' => $don->getStatut()
            ]);
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
            $query = $db->prepare('DELETE FROM don WHERE id = :id');
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function changerStatut($id, $statut)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE don SET statut = :statut WHERE id = :id');
            $query->execute(['id' => $id, 'statut' => $statut]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function totalValide()
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COALESCE(SUM(montant), 0) FROM don WHERE statut = :statut AND type_don = :type_don');
            $query->execute(['statut' => 'valide', 'type_don' => 'financier']);
            return $query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function compterEnAttente()
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM don WHERE statut = :statut');
            $query->execute(['statut' => 'en_attente']);
            return $query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function compterPhysiques()
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM don WHERE type_don = :type_don');
            $query->execute(['type_don' => 'physique']);
            return $query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function derniersDons($nombre)
    {
        $sql = 'SELECT d.*, u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, p.nom AS partenaire_nom, ' . self::COLONNES_PHYSIQUE . '
                FROM don d
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id
                LEFT JOIN don_physique dp ON dp.don_id = d.id
                ORDER BY d.date_don DESC LIMIT :nombre';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue('nombre', (int) $nombre, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function donsParPartenaire()
    {
        $sql = 'SELECT p.id, p.nom, p.type, COUNT(d.id) AS nombre_dons,
                COALESCE(SUM(CASE WHEN d.statut = :valide1 AND d.type_don = :financier1 THEN d.montant ELSE 0 END), 0) AS total_valide,
                COALESCE(SUM(CASE WHEN d.statut = :attente AND d.type_don = :financier2 THEN d.montant ELSE 0 END), 0) AS total_attente,
                SUM(CASE WHEN d.statut = :valide2 THEN 1 ELSE 0 END) AS nombre_valides,
                SUM(CASE WHEN d.type_don = :physique THEN 1 ELSE 0 END) AS nombre_physiques
                FROM partenaire p
                LEFT JOIN don d ON d.partenaire_id = p.id
                GROUP BY p.id, p.nom, p.type
                ORDER BY total_valide DESC';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'valide1' => 'valide',
                'financier1' => 'financier',
                'attente' => 'en_attente',
                'financier2' => 'financier',
                'valide2' => 'valide',
                'physique' => 'physique'
            ]);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function detail($d)
    {
        if ($d['type_don'] === 'physique' && $d['categorie'] !== null) {
            $donPhysiqueController = new DonPhysiqueController();
            return $donPhysiqueController->resume($d);
        }
        return number_format((float) $d['montant'], 2, ',', ' ') . ' DT';
    }

    public function valider($donnees, $partenairesValides, $partenairesSante = [])
    {
        $type = $donnees['type_don'] ?? '';
        if (!in_array($type, ['financier', 'physique'], true)) {
            return ['type_don' => 'Veuillez choisir un type de don.'];
        }
        if ($type === 'physique') {
            $donPhysiqueController = new DonPhysiqueController();
            return $donPhysiqueController->valider($donnees, $partenairesValides, $partenairesSante);
        }

        $erreurs = [];
        if (!in_array((string) ($donnees['partenaire_id'] ?? ''), $partenairesValides, true)) {
            $erreurs['partenaire_id'] = 'Veuillez choisir un partenaire actif.';
        }
        $montant = str_replace(',', '.', trim($donnees['montant'] ?? ''));
        if (!is_numeric($montant) || $montant < 1 || $montant > 10000) {
            $erreurs['montant'] = 'Le montant doit être compris entre 1 et 10000 DT.';
        }
        if (!in_array($donnees['moyen_paiement'] ?? '', ['carte', 'virement', 'especes'], true)) {
            $erreurs['moyen_paiement'] = 'Veuillez choisir un moyen de paiement valide.';
        }
        if (mb_strlen(trim($donnees['message'] ?? '')) > 500) {
            $erreurs['message'] = 'Le message ne doit pas dépasser 500 caractères.';
        }
        return $erreurs;
    }
}
