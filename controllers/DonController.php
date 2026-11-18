<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Don.php';

class DonController
{
    public function readDon($statut = '', $partenaireId = '')
    {
        $sql = 'SELECT d.*, u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, u.email AS utilisateur_email,
                p.nom AS partenaire_nom
                FROM don d
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id
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

    public function readDonsByUtilisateur($utilisateurId)
    {
        $sql = 'SELECT d.*, p.nom AS partenaire_nom, p.type AS partenaire_type
                FROM don d
                INNER JOIN partenaire p ON p.id = d.partenaire_id
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
        $sql = 'INSERT INTO don (utilisateur_id, partenaire_id, montant, moyen_paiement, message, statut)
                VALUES (:utilisateur_id, :partenaire_id, :montant, :moyen_paiement, :message, :statut)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'utilisateur_id' => $don->getUtilisateurId(),
                'partenaire_id' => $don->getPartenaireId(),
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

    public function Update(Don $don, $id)
    {
        $sql = 'UPDATE don SET partenaire_id = :partenaire_id, montant = :montant, moyen_paiement = :moyen_paiement,
                message = :message, statut = :statut WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'partenaire_id' => $don->getPartenaireId(),
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
            $query = $db->prepare('SELECT COALESCE(SUM(montant), 0) FROM don WHERE statut = :statut');
            $query->execute(['statut' => 'valide']);
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

    public function derniersDons($nombre)
    {
        $sql = 'SELECT d.*, u.nom AS utilisateur_nom, u.prenom AS utilisateur_prenom, p.nom AS partenaire_nom
                FROM don d
                LEFT JOIN utilisateur u ON u.id = d.utilisateur_id
                INNER JOIN partenaire p ON p.id = d.partenaire_id
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
                COALESCE(SUM(CASE WHEN d.statut = :valide1 THEN d.montant ELSE 0 END), 0) AS total_valide,
                COALESCE(SUM(CASE WHEN d.statut = :attente THEN d.montant ELSE 0 END), 0) AS total_attente,
                SUM(CASE WHEN d.statut = :valide2 THEN 1 ELSE 0 END) AS nombre_valides
                FROM partenaire p
                LEFT JOIN don d ON d.partenaire_id = p.id
                GROUP BY p.id, p.nom, p.type
                ORDER BY total_valide DESC';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['valide1' => 'valide', 'attente' => 'en_attente', 'valide2' => 'valide']);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function valider($donnees, $partenairesValides)
    {
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
