<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Partenaire.php';

class PartenaireController
{
    public function readPartenaire()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM partenaire ORDER BY nom');
            return $liste->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function readPartenairesActifs($type = '')
    {
        $sql = 'SELECT * FROM partenaire WHERE statut = :statut';
        $params = ['statut' => 'actif'];
        if ($type !== '') {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }
        $sql .= ' ORDER BY nom';
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

    public function getPartenaireById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM partenaire WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Create(Partenaire $partenaire)
    {
        $sql = 'INSERT INTO partenaire (nom, type, email, telephone, adresse, description, logo, statut, date_debut)
                VALUES (:nom, :type, :email, :telephone, :adresse, :description, :logo, :statut, :date_debut)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $partenaire->getNom(),
                'type' => $partenaire->getType(),
                'email' => $partenaire->getEmail(),
                'telephone' => $partenaire->getTelephone(),
                'adresse' => $partenaire->getAdresse(),
                'description' => $partenaire->getDescription(),
                'logo' => $partenaire->getLogo(),
                'statut' => $partenaire->getStatut(),
                'date_debut' => $partenaire->getDateDebut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(Partenaire $partenaire, $id)
    {
        $sql = 'UPDATE partenaire SET nom = :nom, type = :type, email = :email, telephone = :telephone,
                adresse = :adresse, description = :description, logo = :logo, statut = :statut,
                date_debut = :date_debut WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom' => $partenaire->getNom(),
                'type' => $partenaire->getType(),
                'email' => $partenaire->getEmail(),
                'telephone' => $partenaire->getTelephone(),
                'adresse' => $partenaire->getAdresse(),
                'description' => $partenaire->getDescription(),
                'logo' => $partenaire->getLogo(),
                'statut' => $partenaire->getStatut(),
                'date_debut' => $partenaire->getDateDebut()
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
            $query = $db->prepare('DELETE FROM partenaire WHERE id = :id');
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function compterPartenaires()
    {
        $db = config::getConnexion();
        try {
            return $db->query('SELECT COUNT(*) FROM partenaire')->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function uploadLogo($fichier)
    {
        if (!isset($fichier) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
            return ['nom' => null, 'erreur' => null];
        }
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            return ['nom' => null, 'erreur' => 'Erreur lors de l\'envoi du fichier (2 Mo maximum).'];
        }
        if ($fichier['size'] > 2 * 1024 * 1024) {
            return ['nom' => null, 'erreur' => 'L\'image ne doit pas dépasser 2 Mo.'];
        }
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
        if (!isset($types[$mime]) || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return ['nom' => null, 'erreur' => 'Seules les images jpg, png ou webp sont acceptées.'];
        }
        $nom = 'logo_' . bin2hex(random_bytes(8)) . '.' . $types[$mime];
        if (!move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../uploads/' . $nom)) {
            return ['nom' => null, 'erreur' => 'Impossible d\'enregistrer l\'image.'];
        }
        return ['nom' => $nom, 'erreur' => null];
    }

    public function valider($donnees)
    {
        $erreurs = [];
        $nom = trim($donnees['nom'] ?? '');
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $erreurs['nom'] = 'Le nom doit contenir de 2 à 100 caractères.';
        }
        if (!in_array($donnees['type'] ?? '', ['association', 'clinique', 'pharmacie', 'sponsor'], true)) {
            $erreurs['type'] = 'Veuillez choisir un type valide.';
        }
        if (!filter_var(trim($donnees['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        }
        if (!preg_match('/^[0-9]{8}$/', trim($donnees['telephone'] ?? ''))) {
            $erreurs['telephone'] = 'Le téléphone doit contenir 8 chiffres.';
        }
        if (mb_strlen(trim($donnees['adresse'] ?? '')) < 5) {
            $erreurs['adresse'] = 'L\'adresse doit contenir au moins 5 caractères.';
        }
        if (!in_array($donnees['statut'] ?? '', ['actif', 'inactif'], true)) {
            $erreurs['statut'] = 'Statut invalide.';
        }
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_debut'] ?? '');
        if (!$date || $date->format('Y-m-d') !== $donnees['date_debut']) {
            $erreurs['date_debut'] = 'Date de début invalide.';
        }
        return $erreurs;
    }
}
