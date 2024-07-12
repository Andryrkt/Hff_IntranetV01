<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\IdemniteRepository;

  /**
 *   @ORM\Table(name="idemnite")
 * @ORM\Entity(repositoryClass=IdemniteRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Indemnite
{
  

    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private int $montant;
}