<?php

namespace App\Entity\da;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\dom\Rmq;
use App\Entity\admin\StatutDemande;
use App\Repository\dom\DomRepository;
use App\Entity\Traits\AgenceServiceTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\Traits\AgenceServiceEmetteurTrait;
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DemandeApproLRepository::class)
 * @ORM\Table(name="Demande_Appro_L")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproL {}
