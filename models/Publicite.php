<?php

class Publicite
{
    private $id;
    private $partenaireId;
    private $titre;
    private $description;
    private $image;
    private $lien;
    private $dateDebut;
    private $dateFin;
    private $statut;

    public function __construct($id, $partenaireId, $titre, $description, $image, $lien, $dateDebut, $dateFin, $statut = 'active')
    {
        $this->id = $id;
        $this->partenaireId = $partenaireId;
        $this->titre = $titre;
        $this->description = $description;
        $this->image = $image;
        $this->lien = $lien;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->statut = $statut;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPartenaireId()
    {
        return $this->partenaireId;
    }

    public function setPartenaireId($partenaireId)
    {
        $this->partenaireId = $partenaireId;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getLien()
    {
        return $this->lien;
    }

    public function setLien($lien)
    {
        $this->lien = $lien;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }
}
