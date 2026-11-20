<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class UtilisateurController
{
    public function readUtilisateur()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM utilisateur ORDER BY id DESC');
            return $liste->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function getUtilisateurById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM utilisateur WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function getUtilisateurByEmail($email)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM utilisateur WHERE email = :email');
            $query->execute(['email' => $email]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Create(Utilisateur $utilisateur)
    {
        $sql = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role, statut)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :role, :statut)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'mot_de_passe' => $utilisateur->getMotDePasse(),
                'telephone' => $utilisateur->getTelephone(),
                'role' => $utilisateur->getRole(),
                'statut' => $utilisateur->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(Utilisateur $utilisateur, $id)
    {
        $sql = 'UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email,
                telephone = :telephone, role = :role, statut = :statut WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'telephone' => $utilisateur->getTelephone(),
                'role' => $utilisateur->getRole(),
                'statut' => $utilisateur->getStatut()
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
            $query = $db->prepare('DELETE FROM utilisateur WHERE id = :id');
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function changerMotDePasse($id, $motDePasse)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE id = :id');
            $query->execute([
                'id' => $id,
                'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT)
            ]);
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
            $query = $db->prepare('UPDATE utilisateur SET statut = :statut WHERE id = :id');
            $query->execute(['id' => $id, 'statut' => $statut]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function verifierConnexion($email, $motDePasse)
    {
        $utilisateur = $this->getUtilisateurByEmail($email);
        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return $utilisateur;
        }
        return false;
    }

    public function emailExiste($email, $idExclu = null)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = :email AND id <> :id');
            $query->execute(['email' => $email, 'id' => $idExclu === null ? 0 : $idExclu]);
            return $query->fetchColumn() > 0;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function rechercherUtilisateurs($recherche, $role, $statut, $tri)
    {
        $tris = [
            'recent' => 'u.date_inscription DESC',
            'ancien' => 'u.date_inscription ASC',
            'nom' => 'u.nom ASC, u.prenom ASC',
            'email' => 'u.email ASC'
        ];
        $ordre = isset($tris[$tri]) ? $tris[$tri] : $tris['recent'];

        $sql = 'SELECT u.*
                FROM utilisateur u
                WHERE 1 = 1';
        $params = [];
        if ($recherche !== '') {
            $sql .= ' AND (u.nom LIKE :r1 OR u.prenom LIKE :r2 OR u.email LIKE :r3)';
            $params['r1'] = '%' . $recherche . '%';
            $params['r2'] = '%' . $recherche . '%';
            $params['r3'] = '%' . $recherche . '%';
        }
        if ($role !== '') {
            $sql .= ' AND u.role = :role';
            $params['role'] = $role;
        }
        if ($statut !== '') {
            $sql .= ' AND u.statut = :statut';
            $params['statut'] = $statut;
        }
        $sql .= ' ORDER BY ' . $ordre;

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

    public function compterUtilisateurs()
    {
        $db = config::getConnexion();
        try {
            return $db->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function compterBloques()
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM utilisateur WHERE statut = :statut');
            $query->execute(['statut' => 'bloque']);
            return $query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return 0;
        }
    }

    public function validerMotDePasse($motDePasse, $confirmation)
    {
        $erreurs = [];
        if (strlen($motDePasse) < 8 || !preg_match('/[A-Z]/', $motDePasse) || !preg_match('/[0-9]/', $motDePasse)) {
            $erreurs['mot_de_passe'] = 'Au moins 8 caractères, une majuscule et un chiffre.';
        }
        if ($motDePasse !== $confirmation) {
            $erreurs['confirmation'] = 'La confirmation ne correspond pas au mot de passe.';
        }
        return $erreurs;
    }

    public function valider($donnees, $idExclu = null, $avecMotDePasse = true)
    {
        $erreurs = [];
        $nom = trim($donnees['nom'] ?? '');
        $prenom = trim($donnees['prenom'] ?? '');
        $email = trim($donnees['email'] ?? '');
        $telephone = trim($donnees['telephone'] ?? '');

        if (!preg_match('/^[\p{L}\' -]{2,50}$/u', $nom)) {
            $erreurs['nom'] = 'Le nom doit contenir de 2 à 50 lettres.';
        }
        if (!preg_match('/^[\p{L}\' -]{2,50}$/u', $prenom)) {
            $erreurs['prenom'] = 'Le prénom doit contenir de 2 à 50 lettres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        } elseif ($this->emailExiste($email, $idExclu)) {
            $erreurs['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }
        if (!preg_match('/^[0-9]{8}$/', $telephone)) {
            $erreurs['telephone'] = 'Le téléphone doit contenir 8 chiffres.';
        }
        if (isset($donnees['role']) && !in_array($donnees['role'], ['utilisateur', 'admin'], true)) {
            $erreurs['role'] = 'Rôle invalide.';
        }
        if (isset($donnees['statut']) && !in_array($donnees['statut'], ['actif', 'bloque'], true)) {
            $erreurs['statut'] = 'Statut invalide.';
        }
        if ($avecMotDePasse) {
            $erreurs = array_merge($erreurs, $this->validerMotDePasse($donnees['mot_de_passe'] ?? '', $donnees['confirmation'] ?? ''));
        }
        return $erreurs;
    }
}
