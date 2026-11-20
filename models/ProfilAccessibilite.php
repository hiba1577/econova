<?php

class ProfilAccessibilite
{
    private $id;
    private $userId;
    private $besoinVisuel;
    private $besoinMoteur;
    private $besoinAuditif;
    private $tailleTexte;
    private $contrasteFort;
    private $lectureFacile;

    public function __construct($id, $userId, $besoinVisuel = 0, $besoinMoteur = 0, $besoinAuditif = 0, $tailleTexte = 'normal', $contrasteFort = 0, $lectureFacile = 0)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->besoinVisuel = $besoinVisuel;
        $this->besoinMoteur = $besoinMoteur;
        $this->besoinAuditif = $besoinAuditif;
        $this->tailleTexte = $tailleTexte;
        $this->contrasteFort = $contrasteFort;
        $this->lectureFacile = $lectureFacile;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getBesoinVisuel()
    {
        return $this->besoinVisuel;
    }

    public function setBesoinVisuel($besoinVisuel)
    {
        $this->besoinVisuel = $besoinVisuel;
    }

    public function getBesoinMoteur()
    {
        return $this->besoinMoteur;
    }

    public function setBesoinMoteur($besoinMoteur)
    {
        $this->besoinMoteur = $besoinMoteur;
    }

    public function getBesoinAuditif()
    {
        return $this->besoinAuditif;
    }

    public function setBesoinAuditif($besoinAuditif)
    {
        $this->besoinAuditif = $besoinAuditif;
    }

    public function getTailleTexte()
    {
        return $this->tailleTexte;
    }

    public function setTailleTexte($tailleTexte)
    {
        $this->tailleTexte = $tailleTexte;
    }

    public function getContrasteFort()
    {
        return $this->contrasteFort;
    }

    public function setContrasteFort($contrasteFort)
    {
        $this->contrasteFort = $contrasteFort;
    }

    public function getLectureFacile()
    {
        return $this->lectureFacile;
    }

    public function setLectureFacile($lectureFacile)
    {
        $this->lectureFacile = $lectureFacile;
    }
}
