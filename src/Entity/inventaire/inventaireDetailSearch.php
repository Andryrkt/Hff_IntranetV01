<?php 
namespace App\Entity\inventaire;
class InventaireDetailSearch{
    private $numinv;

    /**
     * Get the value of numinv
     */ 
    public function getNuminv()
    {
        return $this->numinv;
    }

    /**
     * Set the value of numinv
     *
     * @return  self
     */ 
    public function setNuminv($numinv)
    {
        $this->numinv = $numinv;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'numinv' => $this->numinv,
        ];
    }
    public function arrayToObjet(array $criteriaTab)
    {
        $this
            ->setNuminv($criteriaTab['numinv'])
            
        ;
    }
}