<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 */
trait DateTrait
{
    /**
     * @ORM\Column(type="datetime", name="date_creation")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="datetime", name="date_modification")
     */
    private $dateModification;

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $timezone = new \DateTimeZone('Indian/Antananarivo');
        $this->dateCreation = new \DateTime('now', $timezone);
        $this->dateModification = new \DateTime('now', $timezone);
        error_log('PrePersist called');
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $timezone = new \DateTimeZone('Indian/Antananarivo');
        $this->dateModification = new \DateTime('now', $timezone);
        error_log('PreUpdate called');
    }
}
