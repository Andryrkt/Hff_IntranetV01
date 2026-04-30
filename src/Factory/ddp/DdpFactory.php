<?php

namespace App\Factory\ddp;

use App\Dto\ddp\DdpDto;
use App\Model\ddp\DdpModel;

class DdpFactory
{
    private DdpModel $ddpModel;

    public function __construct(DdpModel $ddpModel)
    {
        $this->ddpModel = $ddpModel;
    }

    public function initialisation(int $typeDdp): DdpDto
    {
        $dto = new DdpDto();

        $dto->choiceModePaiement = $this->modePaiement();
        $dto->choiceDevise = $this->devise();

        return $dto;
    }

    private function modePaiement()
    {
        $modePaiement = $this->ddpModel->getModePaiement();
        return array_combine($modePaiement, $modePaiement);
    }

    private function devise()
    {
        $devisess = $this->ddpModel->getDevise();

        $devises = [
            '' => '',
        ];

        foreach ($devisess as $devise) {
            $devises[$devise['adevlib']] = $devise['adevcode'];
        }

        return $devises;
    }
}
