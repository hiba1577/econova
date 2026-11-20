<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/ProfilAccessibilite.php';

class ProfilAccessibiliteController
{
    public function readProfilAccessibilite()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query('SELECT * FROM profil_accessibilite ORDER BY id');
            return $liste->fetchAll();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return [];
        }
    }

    public function getProfilAccessibiliteById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM profil_accessibilite WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function getProfilByUserId($userId)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM profil_accessibilite WHERE user_id = :user_id');
            $query->execute(['user_id' => $userId]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Create(ProfilAccessibilite $profil)
    {
        $sql = 'INSERT INTO profil_accessibilite (user_id, besoin_visuel, besoin_moteur, besoin_auditif, taille_texte, contraste_fort, lecture_facile)
                VALUES (:user_id, :besoin_visuel, :besoin_moteur, :besoin_auditif, :taille_texte, :contraste_fort, :lecture_facile)';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'user_id' => $profil->getUserId(),
                'besoin_visuel' => $profil->getBesoinVisuel(),
                'besoin_moteur' => $profil->getBesoinMoteur(),
                'besoin_auditif' => $profil->getBesoinAuditif(),
                'taille_texte' => $profil->getTailleTexte(),
                'contraste_fort' => $profil->getContrasteFort(),
                'lecture_facile' => $profil->getLectureFacile()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function Update(ProfilAccessibilite $profil, $id)
    {
        $sql = 'UPDATE profil_accessibilite SET besoin_visuel = :besoin_visuel, besoin_moteur = :besoin_moteur,
                besoin_auditif = :besoin_auditif, taille_texte = :taille_texte, contraste_fort = :contraste_fort,
                lecture_facile = :lecture_facile WHERE id = :id';
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'besoin_visuel' => $profil->getBesoinVisuel(),
                'besoin_moteur' => $profil->getBesoinMoteur(),
                'besoin_auditif' => $profil->getBesoinAuditif(),
                'taille_texte' => $profil->getTailleTexte(),
                'contraste_fort' => $profil->getContrasteFort(),
                'lecture_facile' => $profil->getLectureFacile()
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
            $query = $db->prepare('DELETE FROM profil_accessibilite WHERE id = :id');
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error ' . $e->getMessage();
            return false;
        }
    }

    public function enregistrer(ProfilAccessibilite $profil)
    {
        $existant = $this->getProfilByUserId($profil->getUserId());
        if ($existant) {
            return $this->Update($profil, $existant['id']);
        }
        return $this->Create($profil);
    }

    public function depuisFormulaire($donnees, $userId)
    {
        return new ProfilAccessibilite(
            null,
            $userId,
            isset($donnees['besoin_visuel']) ? 1 : 0,
            isset($donnees['besoin_moteur']) ? 1 : 0,
            isset($donnees['besoin_auditif']) ? 1 : 0,
            $donnees['taille_texte'] ?? 'normal',
            isset($donnees['contraste_fort']) ? 1 : 0,
            isset($donnees['lecture_facile']) ? 1 : 0
        );
    }

    public function valider($donnees)
    {
        $erreurs = [];
        if (!in_array($donnees['taille_texte'] ?? '', ['normal', 'grand', 'tres-grand'], true)) {
            $erreurs['taille_texte'] = 'Taille de texte invalide.';
        }
        return $erreurs;
    }
}
