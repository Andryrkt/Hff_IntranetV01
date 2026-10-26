<?php

namespace App\Controller\da\ddp;

use App\Service\ExcelService;
use App\Controller\Controller;
use App\Dto\Da\ddp\BapSearchDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Form\da\ddp\BonApayerType;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;

/**
 * @Route("/demande-appro")
 */
class BonApayerController extends Controller
{
    private DemandePaiementRepository $demandePaiementRepository;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->demandePaiementRepository = $em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/consultation-facture", name="da_bon_a_payer" )
     */
    public function index(Request $request)
    {
        $bapSearchDto = new BapSearchDto();

        // Création du formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(BonApayerType::class, $bapSearchDto, ['method' => 'GET'])->getForm();

        // Traitement du formulaire de recherche
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BapSearchDto $bapSearchDto */
            $bapSearchDto = $form->getData();
        }

        // Récupération des données dans la table demande_paiement
        $ddp = $this->demandePaiementRepository->findByConsultationFactureCriteria($bapSearchDto);

        // transformation en DTO (DemandePaiementDto)
        $dtos = DemandePaiementMapper::mapInverse($ddp);

        return $this->render('da/ddp/bon_a_payer.html.twig', [
            'dtos'     => $dtos,
            'form'     => $form->createView(),
            'criteria' => $bapSearchDto->toArray()
        ]);
    }

    /** 
     * @Route("/export-excel/consultation-facture", name="export_excel_consultation_facture")
     */
    public function exportExcel(Request $request)
    {
        $requestData = $request->query->all();
        $bapSearchDto = BapSearchDto::fromArray($requestData);

        // Récupération des données dans la table demande_paiement
        $ddp = $this->demandePaiementRepository->findByConsultationFactureCriteria($bapSearchDto);

        // transformation en DTO (DemandePaiementDto)
        $dtos = DemandePaiementMapper::mapInverse($ddp, true);

        // Génération du tableau des données
        $data = $this->generateTableData($dtos);

        // Crée le fichier Excel
        (new ExcelService())->createSpreadsheet($data, "extraction_consultation_facture_" . date('Y-m-d_H-i-s'));
    }

    /** 
     * Génération du tableau des données à partir des dtos
     * 
     * @param DemandePaiementDto[] $dtos
     * 
     * @return array
     */
    private function generateTableData(array $dtos): array
    {
        $data[] = [
            "Numéro CLA",
            "Numéro DA",
            "Numéro DDP/BAP",
            "Type DDP",
            "Fournisseur",
            "Numéro cde",
            "Numéro livraison",
            "Facture BL",
            "Statut",
            "Transmise le",
            "Montant DDP",
        ];

        foreach ($dtos as $dto) {
            $data[] = [
                $dto->numeroCla ?? "-",
                $dto->numeroDemandeAppro ?? "-",
                $dto->numeroDdp ?? "-",
                $dto->typeDemande,
                $dto->getFournisseur(),
                $dto->numeroCommande ?? "-",
                $dto->numeroLivraison ?? "-",
                $dto->numeroFacture ?? "-",
                $dto->statut,
                $dto->dateSoumissionCompta ?? "-",
                $dto->montantAPayer,
            ];
        }

        return $data;
    }
}
