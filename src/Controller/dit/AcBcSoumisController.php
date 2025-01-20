<?php

namespace App\Controller\dit;

use App\Entity\dit\AcSoumis;
use App\Entity\dit\BcSoumis;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Form\dit\AcSoumisType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Service\genererPdf\GenererPdfAcSoumis;
use App\Service\historiqueOperation\HistoriqueOperationBCService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationDEVService;
use App\Service\TableauEnStringService;

class AcBcSoumisController extends Controller
{
    private $acSoumis;
    private $bcSoumis;
    private $bcRepository;
    private $genererPdfAc;
    private $historiqueOperation;
    private $contactAgenceAteRepository;
    private $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->acSoumis = new AcSoumis();
        $this->bcSoumis = new BcSoumis();
        $this->bcRepository = self::$em->getRepository(BcSoumis::class);
        $this->genererPdfAc = new GenererPdfAcSoumis();
        $this->historiqueOperation = new HistoriqueOperationBCService;
        $this->contactAgenceAteRepository = self::$em->getRepository(ContactAgenceAte::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/dit/ac-bc-soumis/{numDit}", name="dit_ac_bc_soumis")
     */
    public function traitementFormulaire(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // $dit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $devis = $this->filtredataDevis($numDit);
        

        if(empty($devis)) {
            $message = "Erreur lors de la soumission, Impossible de soumettre le BC . . . l'information du devis est vide pour le numero {$numDit}";
            $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
        }
        
        $acSoumis = $this->initialisation($devis, $numDit);
        
        $form = self::$validator->createBuilder(AcSoumisType::class, $acSoumis)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $acSoumis = $this->initialisation($devis, $numDit);
            $numBc = $acSoumis->getNumeroBc();
            $numeroVersionMax = $this->bcRepository->findNumeroVersionMax($numBc);
            $bcSoumis = $this->ajoutDonneeBc($acSoumis, $numeroVersionMax);
            
            /** CREATION , FUSION, ENVOIE DW du PDF */
            $acSoumis->setNumeroVersion($bcSoumis->getNumVersion());
            $numClientBcDevis = $this->ditRepository->findNumClient($numDit).'_'.$numBc.'_'.$acSoumis->getNumeroDevis();
            $this->genererPdfAc->genererPdfAc($acSoumis, $numClientBcDevis);
            $fileName= $this->enregistrementEtFusionFichier($form, $numClientBcDevis, $bcSoumis->getNumVersion());
            $this->genererPdfAc->copyToDWAcSoumis($fileName);// copier le fichier dans docuware
            
            /** Envoie des information du bc dans le table bc_soumis */
            $bcSoumis->setNomFichier($fileName);
            $this->envoieBcDansBd($bcSoumis);

            $message = 'Le bon de commande et l\'accusé de reception  ont été soumis avec succès';
            $this->historiqueOperation->sendNotificationCreation($message, $numBc, 'dit_index', true);
        }

        self::$twig->display('dit/AcBcSoumis.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function filtredataDevis($numDit)
    {
        $devi = self::$em->getRepository(DitDevisSoumisAValidation::class)->findInfoDevis($numDit);
        
        return array_filter($devi, function ($item) {
            return $item->getNatureOperation() === 'VTE' && ($item->getMontantItv() - $item->getMontantForfait()) > 0.00 ;
        });
    }

    private function enregistrementEtFusionFichier(FormInterface $form, string $numClientBcDevis, string $numeroVersion)
    {
        $chemin = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/ac_bc/';
        $fileUploader = new FileUploaderService($chemin);
        $prefix = 'bc';
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $prefix, $numClientBcDevis, true, $numeroVersion);

        return $fileName;
    }
    
    private function envoieBcDansBd(BcSoumis $bcSoumis): void
    {
        self::$em->persist($bcSoumis);
        self::$em->flush();
    }

    private function ajoutDonneeBc(AcSoumis $acSoumis, ?int $numeroVersionMax): BcSoumis
    {
        $this->bcSoumis
                ->setNumDit($acSoumis->getNumeroDit())
                ->setNumDevis($acSoumis->getNumeroDevis())
                ->setNumBc($acSoumis->getNumeroBc())
                ->setDateBc($acSoumis->getDateBc())
                ->setDateDevis($acSoumis->getDateDevis())
                ->setMontantDevis($acSoumis->getMontantDevis())
                ->setDateHeureSoumission(new \DateTime())
                ->setNumVersion($this->autoIncrement($numeroVersionMax))
            ;
        return $this->bcSoumis;
    }

    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function initialisation(array $devis, string $numDit): AcSoumis
    {   
        $reparationRealiser = $this->ditRepository->findAteRealiserPar($numDit);
        $atelier = $this->contactAgenceAteRepository->findContactSelonAtelier($reparationRealiser);

        $this->acSoumis
            ->setDateCreation(new \DateTime($this->getDatesystem()))
            ->setNumeroDevis($devis[0]->getNumeroDevis())
            ->setStatutDevis($devis[0]->getStatut())
            ->setNumeroDit($devis[0]->getNumeroDit())
            ->setDateDevis($devis[0]->getDateHeureSoumission())
            ->setMontantDevis($this->calculMontantDevis($devis))
            ->setEmailContactHff($this->emailHff($atelier))
            ->setTelephoneContactHff($this->telephoneHff($atelier))
            ->setDevise($devis[0]->getDevise())
            ->setDateExpirationDevis((clone $devis[0]->getDateHeureSoumission())->modify('+30 days'))
        ;
        return $this->acSoumis;
    }

    private function telephoneHff(array $atelier)
    {
        return TableauEnStringService::TableauEnString(' / ',array_map(fn($el) => $el->getTelephone(), $atelier), '');
    }

    private function emailHff(array $atelier)
    {
        return TableauEnStringService::TableauEnString(' / ',array_map(fn($el)=> $el->getEmailString(), $atelier), '');
    }

    /**
     * METHODE POUR CALCULER LE MONTANT DEVIS
     * le mont devis c'est le mont du vente
     * donc il faut soustraire du montant forfait s'il existe
     *
     * @param array $devis
     * @return void
     */
    private function calculMontantDevis(array $devis): float
    {
        $montantItv = array_reduce($devis, function ($acc, $item) {
            return $acc + $item->getMontantItv();
        }, 0);

        $montantForfait = array_reduce($devis, function ($acc, $item) {
            return $acc + $item->getMontantForfait();
        }, 0);

        return $montantItv - $montantForfait;
    }
}