<?php

namespace App\Service\da;

use App\Entity\dw\DwFacBl;
use App\Entity\dw\DwBcAppro;
use App\Entity\dw\DwDaDirect;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\dw\DwDaDirectRepository;
use App\Repository\dw\DwFactureBonLivraisonRepository;

class DwDataService
{
    private DwBcApproRepository $dwBcApproRepository;
    private DwDaDirectRepository $dwDaDirectRepository;
    private DwFactureBonLivraisonRepository $dwFacBlRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->dwFacBlRepository    = $em->getRepository(DwFacBl::class);
        $this->dwBcApproRepository  = $em->getRepository(DwBcAppro::class);
        $this->dwDaDirectRepository = $em->getRepository(DwDaDirect::class);
    }
}
