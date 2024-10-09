<?php

namespace App\Entity\admin\dit;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\Entity
 * @ORM\Table(name="DW_Rapport_Intervention")
 * @ORM\HasLifecycleCallbacks
 */
class DwRapportIntervention
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_ri")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=50, name="numero_ri")
     */
    private $numeroRi;

    /**
     * @ORM\Column(type="string", length=100, name="id_tiroir")
     */
    private $idTiroir;

    /**
     * @ORM\Column(type="string", length=8, name="numero_or")
     */
    private $numeroOR;

    /**
     * @ORM\Column(type="date", name="date_creation")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="time", name="heure_creation")
     */
    private $heureCreation;

    /**
     * @ORM\Column(type="date", name="date_derniere")
     */
    private $dateDreniereModification;

}