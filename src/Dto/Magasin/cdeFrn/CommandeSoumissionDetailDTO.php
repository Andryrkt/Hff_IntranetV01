<?php

namespace App\Dto\Magasin\cdeFrn;

class CommandeSoumissionDetailDTO
{
    public ?string    $numDoc       = null;
    public ?string    $refClient    = null;
    public ?string    $numClient    = null;
    public ?string    $nomClient    = null;
    public ?string    $rmqClient    = null;
    public ?\DateTime $datePlanning = null;

    public function getRefSplitted(int $max = 50): string
    {
        $text = $this->refClient;

        return mb_strlen($text, 'UTF-8') > $max
            ? mb_substr($text, 0, $max - 3, 'UTF-8') . '...'
            : $text;
    }

    public function getClient(): string
    {
        return "{$this->numClient} - {$this->nomClient}";
    }

    public function getDatePlanningFormatted(): string
    {
        if (!$this->datePlanning) return "";

        return "(" . $this->datePlanning->format("d/m/Y") . ")";
    }
}
