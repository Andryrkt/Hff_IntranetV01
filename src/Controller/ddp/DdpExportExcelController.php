<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Dto\ddp\DdpSearchDto;
use App\Entity\ddp\DemandePaiement;
use App\Form\ddp\DdpSearchType;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\ddp\DemandePaiementRepository;
use App\Service\ExcelService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DdpExportExcelController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;
    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
    }

    /**
     * Export Excel de la liste, avec les mêmes filtres que la consultation (query string du formulaire GET)
     *
     * @Route("/export-excel", name="ddp_export_excel")
     */
    public function exportExcel(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(DdpSearchType::class, new DdpSearchDto(), [
            'method' => 'GET'
        ])->getForm();
        $criteria = $this->traitementFormulaire($form, $request);

        $ddps = $this->demandePaiementRepository->findDemandePaiement($criteria, $this->getSecurityService()->estFinance());
        $dtos = DemandePaiementMapper::mapInverse($ddps);

        $data = [[
            'Agence débité', 'Service débité', 'N° CLA', 'N° demande', 'Date DDP', 'Type', 'Statut DDP', 'Statut dossier de régul',
            'Motif', 'N° facture IPS', 'N° facture', 'N° Commande', 'Bénéficiaire', 'Montant HT', 'Devise', 'Mode de paiement', 'Utilisateur'
        ]];
        foreach ($dtos as $dto) {
            $data[] = [
                $dto->codeAgence,
                $dto->codeService,
                $dto->numeroCla,
                $dto->numeroDdp,
                $dto->dateDemande ? $dto->dateDemande->format('d/m/Y') : '',
                $dto->typeDemande ? $dto->typeDemande->getLibelle() : '',
                $dto->statut,
                $dto->statutDossierRegul,
                $dto->motif,
                $dto->numeroFactureIps,
                $dto->numeroFacture,
                $dto->numeroCommande,
                $dto->beneficiaire,
                (float) $dto->montantAPayer,
                $dto->devise,
                $dto->modePaiement,
                $dto->demandeur,
            ];
        }

        (new ExcelService())->createSpreadsheet($data, 'demandes_de_paiement_' . date('Ymd_His'));
    }

    private function traitementFormulaire(FormInterface $form, Request $request): DdpSearchDto
    {
        $form->handleRequest($request);
        $criteria = new DdpSearchDto();
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }

        return $criteria;
    }
}
