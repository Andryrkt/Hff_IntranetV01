<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RmqRepository;


  /**
 *   @ORM\Table(name="rmq")
 * @ORM\Entity(repositoryClass=RmqRepository::class)
 * @ORM\HasLifecycleCallbacks
 */

class Rmq
{
    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private string $description;
}