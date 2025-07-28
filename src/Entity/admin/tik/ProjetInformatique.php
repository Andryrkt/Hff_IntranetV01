<?php

namespace App\Entity\admin\tik;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetInformatiqueRepository::class)
 * @ORM\Table(name="Projet_Informatique")
 */
class ProjetInformatique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Projet_Informatique")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=11, nullable=false)
     */
    private $numeroDemandeur;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $mailDemandeur;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $codeSociete;

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $idTkiCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiSousCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $idTkiSousCategorie;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $agenceServiceEmetteur;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $agenceServiceDebiteur;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $nomIntervenant;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $mailIntervenant;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $objetDemande;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $detailDemande;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $pieceJointe1;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $pieceJointe2;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $pieceJointe3;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDebPlanning;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFinPlanning;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $avancementProjet;

    // ... (getters et setters)
}
