<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\BadmRepository")
 * @ORM\Table(name="Demande_Mouvement_Materiel")
 * @ORM\HasLifecycleCallbacks
 */
class Casier
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Demande_Mouvement_Materiel")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=11, name="agence_rattacher")
     *
     * @var string
     */
    private string $agence;

    private string $casier;

    private string $nomSessionUtilisateur;

    private DateTime $dateCreation;

    private string $numeroCas;
}