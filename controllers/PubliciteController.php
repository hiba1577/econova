<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Publicite.php';

class PubliciteController
{
    public function readPublicite($partenaireId = '', $statut = '')
    {
        $sql = 'SELECT pu.*, pa.nom AS partenaire_nom
                FROM publicite pu
                INNER JOIN partenaire pa ON pa.id = pu.partenaire_id
                WHERE 1 = 1';
        $params = [];
        if ($partenaireId !== '') {
            $sql .= ' AND pu.partenaire_id = :partenaire_id';
            $params['partenaire_id'] = $partenaireId;
        }
        if ($statut !== '') {
            $sql .= ' AND pu.statut = :statut';
            $params['statut'] = $statut;
        }
        $sql .= ' ORDER BY pu.date_debut DESC';
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

    public function readPublicitesActives($partenaireId = null)
    {
        $sql = 'SELECT pu.*, pa.nom AS partenaire_nom, pa.type AS partenaire_type
                FROM publicite pu
                INNER JOIN partenaire pa ON pa.id = pu.partenaire_id
                WHERE pu.statut = :statut AND pa.statut = :statut_partenaire
                AND CURDATE() BETWEEN pu.date_debut AND pu.date_fin';
        $params = ['statut' => 'active', 'statut_partenaire' => 'actif'];
        if ($partenaireId !== null) {
            $sql .= ' AND pu.partenaire_id = :partenaire_id';
            $params['partenaire_id'] = $partenaireId;
        }
        $sql .= ' ORDER BY pu.date_fin';
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

    public function getPubliciteById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM publicite WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Create(Publicite $publicite)
    {
        $sql = 'INSERT INTO publicite (partenaire_id, titre, description, image, lien, date_debut, date_fin, statut)
                VALUES (:partenaire_id, :titre, :description, :image, :lien, :date_debut, :date_fin, :statut)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'partenaire_id' => $publicite->getPartenaireId(),
                'titre' => $publicite->getTitre(),
                'description' => $publicite->getDescription(),
                'image' => $publicite->getImage(),
                'lien' => $publicite->getLien(),
                'date_debut' => $publicite->getDateDebut(),
                'date_fin' => $publicite->getDateFin(),
                'statut' => $publicite->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(Publicite $publicite, $id)
    {
        $sql = 'UPDATE publicite SET partenaire_id = :partenaire_id, titre = :titre, description = :description,
                image = :image, lien = :lien, date_debut = :date_debut, date_fin = :date_fin, statut = :statut
                WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'partenaire_id' => $publicite->getPartenaireId(),
                'titre' => $publicite->getTitre(),
                'description' => $publicite->getDescription(),
                'image' => $publicite->getImage(),
                'lien' => $publicite->getLien(),
                'date_debut' => $publicite->getDateDebut(),
                'date_fin' => $publicite->getDateFin(),
                'statut' => $publicite->getStatut()
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
            $query = $db->prepare('DELETE FROM publicite WHERE id = :id');
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
            $query = $db->prepare('UPDATE publicite SET statut = :statut WHERE id = :id');
            $query->execute(['id' => $id, 'statut' => $statut]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function compterActives()
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM publicite WHERE statut = :statut AND CURDATE() BETWEEN date_debut AND date_fin');
            $query->execute(['statut' => 'active']);
            return $query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function uploadImage($fichier)
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
        $nom = 'pub_' . bin2hex(random_bytes(8)) . '.' . $types[$mime];
        if (!move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../uploads/' . $nom)) {
            return ['nom' => null, 'erreur' => 'Impossible d\'enregistrer l\'image.'];
        }
        return ['nom' => $nom, 'erreur' => null];
    }

    public function valider($donnees, $partenairesValides)
    {
        $erreurs = [];
        if (!in_array((string) ($donnees['partenaire_id'] ?? ''), $partenairesValides, true)) {
            $erreurs['partenaire_id'] = 'Veuillez choisir un partenaire.';
        }
        $titre = trim($donnees['titre'] ?? '');
        if (mb_strlen($titre) < 3 || mb_strlen($titre) > 150) {
            $erreurs['titre'] = 'Le titre doit contenir de 3 à 150 caractères.';
        }
        $lien = trim($donnees['lien'] ?? '');
        if (!filter_var($lien, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $lien)) {
            $erreurs['lien'] = 'Veuillez saisir une URL valide (http:// ou https://).';
        }
        $debut = DateTime::createFromFormat('Y-m-d', $donnees['date_debut'] ?? '');
        $fin = DateTime::createFromFormat('Y-m-d', $donnees['date_fin'] ?? '');
        if (!$debut || $debut->format('Y-m-d') !== $donnees['date_debut']) {
            $erreurs['date_debut'] = 'Date de début invalide.';
        }
        if (!$fin || $fin->format('Y-m-d') !== $donnees['date_fin']) {
            $erreurs['date_fin'] = 'Date de fin invalide.';
        } elseif ($debut && $fin <= $debut) {
            $erreurs['date_fin'] = 'La date de fin doit être après la date de début.';
        }
        if (!in_array($donnees['statut'] ?? '', ['active', 'inactive'], true)) {
            $erreurs['statut'] = 'Statut invalide.';
        }
        return $erreurs;
    }
}
