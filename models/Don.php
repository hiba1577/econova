<?php

class Don
{
    private $id;
    private $utilisateurId;
    private $partenaireId;
    private $montant;
    private $dateDon;
    private $moyenPaiement;
    private $message;
    private $statut;
    private $typeDon;

    public function __construct($id, $utilisateurId, $partenaireId, $montant, $dateDon, $moyenPaiement, $message, $statut = 'en_attente', $typeDon = 'financier')
    {
        $this->id = $id;
        $this->utilisateurId = $utilisateurId;
        $this->partenaireId = $partenaireId;
        $this->montant = $montant;
        $this->dateDon = $dateDon;
        $this->moyenPaiement = $moyenPaiement;
        $this->message = $message;
        $this->statut = $statut;
        $this->typeDon = $typeDon;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUtilisateurId()
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId($utilisateurId)
    {
        $this->utilisateurId = $utilisateurId;
    }

    public function getPartenaireId()
    {
        return $this->partenaireId;
    }

    public function setPartenaireId($partenaireId)
    {
        $this->partenaireId = $partenaireId;
    }

    public function getMontant()
    {
        return $this->montant;
    }

    public function setMontant($montant)
    {
        $this->montant = $montant;
    }

    public function getDateDon()
    {
        return $this->dateDon;
    }

    public function setDateDon($dateDon)
    {
        $this->dateDon = $dateDon;
    }

    public function getMoyenPaiement()
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement($moyenPaiement)
    {
        $this->moyenPaiement = $moyenPaiement;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function getTypeDon()
    {
        return $this->typeDon;
    }

    public function setTypeDon($typeDon)
    {
        $this->typeDon = $typeDon;
    }
}
