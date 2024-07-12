<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SiteRepository;


  /**
 *   @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Site
{
    use DateTrait;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="nom_zone")
     *
     * @var string
     */
    private string $nomZone;
}