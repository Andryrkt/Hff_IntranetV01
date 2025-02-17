<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Form\ddp\DemandePaiementType;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DemandePaiementController extends Controller
{
    private $typeDemandeRepository;
    private $demandePaiementModel;
    private $cdeFnrRepository;
    private $demandePaiementRepository;
    
    public function __construct()
    {
        parent::__construct();

        $this->typeDemandeRepository = self::$em->getRepository(TypeDemande::class);
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->cdeFnrRepository = self::$em->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementRepository  = self::$em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/demande-paiement/{id}", name="demande_paiement")
     */
    public function afficheForm(Request $request, $id)
    {
        $form = self::$validator->createBuilder(DemandePaiementType::class, null)->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data = $this->ajoutTypeDemande($data, $id);

            dd($data);

        }

        self::$twig->display('ddp/demandePaiementNew.html.twig', [
            'id' => $id,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter l'entité type de demande dans l'entité Demande de paiement
     *
     * @param DemandePaiement $data
     * @param integer $id
     * @return DemandePaiement
     */
    private function ajoutTypeDemande(DemandePaiement $data, int $id): DemandePaiement
    {
        $typeDemande = $this->typeDemandeRepository->find($id);
            return  $data->setTypeDemandeid($typeDemande);
    } 
    
    private function recupererNumCdeFournisseur($numeroFournisseur)
    {
        $nbrLigne = $this->demandePaiementRepository->CompteNbrligne($numeroFournisseur);
        
        if ($nbrLigne <= 0) {
            $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

            $data = [
                'numCdes' => $numCdes,
            ];
        } 
    }
}