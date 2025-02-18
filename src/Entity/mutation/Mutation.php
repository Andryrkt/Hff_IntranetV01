<?php

namespace App\Entity\mutation;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\mutation\MutationRepository;

/**
 * @ORM\Entity(repositoryClass=MutationRepository::class)
 * @ORM\Table(name="Demande_de_mutation")
 * @ORM\HasLifecycleCallbacks
 */
class Mutation {}
