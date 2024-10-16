<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="DW_Tiroir")
 * @ORM\HasLifecycleCallbacks
 */
class DwTiroir
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, name="designation_tiroir")
     */
    private $designationTiroir;

    /** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of designationTiroir
     */ 
    public function getDesignationTiroir()
    {
        return $this->designationTiroir;
    }

    /**
     * Set the value of designationTiroir
     *
     * @return  self
     */ 
    public function setDesignationTiroir($designationTiroir)
    {
        $this->designationTiroir = $designationTiroir;

        return $this;
    }
}