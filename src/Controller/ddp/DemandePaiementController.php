<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Form\ddp\DemandePaiementType;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Entity\admin\ddp\DocDemandePaiement;
use App\Entity\ddp\DemandePaiementLigne;
use App\Repository\admin\ddp\TypeDemandeRepository;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Repository\ddp\DemandePaiementRepository;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DemandePaiementController extends Controller
{
    private TypeDemandeRepository $typeDemandeRepository;
    private DemandePaiementModel $demandePaiementModel;
    private CdefnrSoumisAValidationRepository $cdeFnrRepository;
    private DemandePaiementRepository $demandePaiementRepository;
    private DemandePaiementLigne $demandePaiementLigne;
    private HistoriqueOperationDDPService $historiqueOperation;
    private GeneratePdfDdp $generatePdfDdp;
    
    public function __construct()
    {
        parent::__construct();

        $this->typeDemandeRepository = self::$em->getRepository(TypeDemande::class);
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->cdeFnrRepository = self::$em->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementRepository  = self::$em->getRepository(DemandePaiement::class);
        $this->demandePaiementLigne = new DemandePaiementLigne();
        $this->historiqueOperation = new HistoriqueOperationDDPService();
        $this->generatePdfDdp = new GeneratePdfDdp();
    }

    /**
     * @Route("/demande-paiement/{id}", name="demande_paiement")
     */
    public function afficheForm(Request $request, $id)
    {
         //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(DemandePaiementType::class, null, ['id_type' => $id])->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numDdp = $this->autoDecrementDIT('DDP'); // decrementation du numero DDP
            $this->modificationDernierIdApp($numDdp); //modification de la dernière numero DDP

            $data = $form->getData();//recupération des donnnées
            
            $data->setNumeroDdp($numDdp);// ajout du numero DDP dans l'entity DDP
            $data = $this->ajoutTypeDemande($data, $id);
            $data->setAgenceDebiter($data->getAgence()->getCodeAgence());
            $data->setServiceDebiter($data->getService()->getCodeService());
            $data->setEmailUserConnect($this->getEmail());
            

            // $docDdp = new DocDemandePaiement();
            // $docDdp->setNumeroDdp($numDdp);

            /** ENREGISTREMENT DAN BD */
            $this->EnregistrementBdDdp($data); // enregistrement des données dans la table demande_paiement
            $this->EnregistrementBdDdpl($data);// enregistrement des données dans la table demande_paiement_ligne
            

            /** GENERATION DE PDF */
            $this->generatePdfDdp->genererPDF($data);

            $this->historiqueOperation->sendNotificationSoumission('Le document a été généré avec succès', $numDdp, 'profil_acceuil', true);
        }

        self::$twig->display('ddp/demandePaiementNew.html.twig', [
            'id_type' => $id,
            'form' => $form->createView()
        ]);
    }

    private function transformChaineEnNombre(string $nombre): float
    {
        $nombre = str_replace(' ', '', $nombre); // Supprimer les espaces
        $nombre = str_replace(',', '.', $nombre); // Remplacer la virgule par un point pour le format décimal

        $nombre_formaté = number_format((float)$nombre, 2, '.', ''); // Conversion en float et formatage
        return  $nombre_formaté; // Affiche : 11124522.46
    }
    
    private function EnregistrementBdDdpl($data)
    {
        $demandePaiementLigne = $this->recuperationDonnerDdpl($data);

        if(count($demandePaiementLigne) > 1) {
            foreach ($demandePaiementLigne as $value) {
                self::$em->persist($value);
            }       
        }  else {
            self::$em->persist($demandePaiementLigne[0]);
        }

        self::$em->flush();
    }

    /**
     * TODO:: A RECTIFIER
     *
     * @param  DemandePaiement $data
     * @return array
     */
    private function recuperationDonnerDdpl(DemandePaiement $data): array
    {
        $demandePaiementLigne = [];
        
        for ($i=0; $i < count($data->getNumeroCommande()); $i++) { 
            $demandePaiementLigne[] = $this->demandePaiementLigne
                ->setNumeroDdp($data->getNumeroDdp())
                ->setNumeroLigne($i)
                ->setNumeroCommande($data->getNumeroCommande()[$i])
                ->setNumeroFacture(null !== $data->getNumeroFacture() || isset($data->getNumeroFacture()[$i]) ? $data->getNumeroFacture()[$i] : '-')
                ->setMontantFacture($this->transformChaineEnNombre($data->getMontantAPayer()))
            ;
        }

        return $demandePaiementLigne;
    }



    /**
     * methode qui permet d'enregestrer les données dans la table demande_paiement
     */
    private function EnregistrementBdDdp(DemandePaiement $data)
    {
        self::$em->persist($data);
        self::$em->flush();
    }

    /**
     * modification du dernier id de l'application dans la table application
     *
     * @param string $numDdp
     * @return void
     */
    private function modificationDernierIdApp(string $numDdp)
    {
        $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
        $application->setDerniereId($numDdp);
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        self::$em->persist($application);
        self::$em->flush();
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
    
    // private function recupererNumCdeFournisseur($numeroFournisseur)
    // {
    //     $nbrLigne = $this->demandePaiementRepository->CompteNbrligne($numeroFournisseur);
        
    //     if ($nbrLigne <= 0) {
    //         $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
    //         $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

    //         $data = [
    //             'numCdes' => $numCdes,
    //         ];
    //     } 
    // }
}