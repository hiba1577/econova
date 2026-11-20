<?php

class DonPhysique
{
    private $id;
    private $donId;
    private $categorie;
    private $groupeSanguin;
    private $dateDisponibilite;
    private $typeEquipement;
    private $etat;
    private $quantite;
    private $nomMedicament;
    private $dateExpiration;
    private $emballageFerme;
    private $domaineService;
    private $disponibilites;
    private $heuresParMois;
    private $modeRemise;
    private $adresseRecuperation;
    private $description;
    private $autreArticle;

    public function __construct($id, $donId, $categorie, $groupeSanguin = null, $dateDisponibilite = null, $typeEquipement = null, $etat = null, $quantite = null, $nomMedicament = null, $dateExpiration = null, $emballageFerme = null, $domaineService = null, $disponibilites = null, $heuresParMois = null, $modeRemise = null, $adresseRecuperation = null, $description = null, $autreArticle = null)
    {
        $this->id = $id;
        $this->donId = $donId;
        $this->categorie = $categorie;
        $this->groupeSanguin = $groupeSanguin;
        $this->dateDisponibilite = $dateDisponibilite;
        $this->typeEquipement = $typeEquipement;
        $this->etat = $etat;
        $this->quantite = $quantite;
        $this->nomMedicament = $nomMedicament;
        $this->dateExpiration = $dateExpiration;
        $this->emballageFerme = $emballageFerme;
        $this->domaineService = $domaineService;
        $this->disponibilites = $disponibilites;
        $this->heuresParMois = $heuresParMois;
        $this->modeRemise = $modeRemise;
        $this->adresseRecuperation = $adresseRecuperation;
        $this->description = $description;
        $this->autreArticle = $autreArticle;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDonId()
    {
        return $this->donId;
    }

    public function setDonId($donId)
    {
        $this->donId = $donId;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
    }

    public function getGroupeSanguin()
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin($groupeSanguin)
    {
        $this->groupeSanguin = $groupeSanguin;
    }

    public function getDateDisponibilite()
    {
        return $this->dateDisponibilite;
    }

    public function setDateDisponibilite($dateDisponibilite)
    {
        $this->dateDisponibilite = $dateDisponibilite;
    }

    public function getTypeEquipement()
    {
        return $this->typeEquipement;
    }

    public function setTypeEquipement($typeEquipement)
    {
        $this->typeEquipement = $typeEquipement;
    }

    public function getEtat()
    {
        return $this->etat;
    }

    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;
    }

    public function getNomMedicament()
    {
        return $this->nomMedicament;
    }

    public function setNomMedicament($nomMedicament)
    {
        $this->nomMedicament = $nomMedicament;
    }

    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = $dateExpiration;
    }

    public function getEmballageFerme()
    {
        return $this->emballageFerme;
    }

    public function setEmballageFerme($emballageFerme)
    {
        $this->emballageFerme = $emballageFerme;
    }

    public function getDomaineService()
    {
        return $this->domaineService;
    }

    public function setDomaineService($domaineService)
    {
        $this->domaineService = $domaineService;
    }

    public function getDisponibilites()
    {
        return $this->disponibilites;
    }

    public function setDisponibilites($disponibilites)
    {
        $this->disponibilites = $disponibilites;
    }

    public function getHeuresParMois()
    {
        return $this->heuresParMois;
    }

    public function setHeuresParMois($heuresParMois)
    {
        $this->heuresParMois = $heuresParMois;
    }

    public function getModeRemise()
    {
        return $this->modeRemise;
    }

    public function setModeRemise($modeRemise)
    {
        $this->modeRemise = $modeRemise;
    }

    public function getAdresseRecuperation()
    {
        return $this->adresseRecuperation;
    }

    public function setAdresseRecuperation($adresseRecuperation)
    {
        $this->adresseRecuperation = $adresseRecuperation;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getAutreArticle()
    {
        return $this->autreArticle;
    }

    public function setAutreArticle($autreArticle)
    {
        $this->autreArticle = $autreArticle;
    }
}
