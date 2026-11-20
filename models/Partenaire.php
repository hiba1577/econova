<?php

class Partenaire
{
    private $id;
    private $nom;
    private $type;
    private $email;
    private $telephone;
    private $adresse;
    private $description;
    private $logo;
    private $statut;
    private $dateDebut;

    public function __construct($id, $nom, $type, $email, $telephone, $adresse, $description, $logo = null, $statut = 'actif', $dateDebut = null)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->type = $type;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->adresse = $adresse;
        $this->description = $description;
        $this->logo = $logo;
        $this->statut = $statut;
        $this->dateDebut = $dateDebut;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    }
}
