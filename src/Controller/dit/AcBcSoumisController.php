<?php

namespace App\Controller\dit;

use App\Entity\dit\AcSoumis;
use App\Entity\dit\BcSoumis;
use App\Controller\Controller;
use App\Form\dit\AcSoumisType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
use App\Service\fichier\FileUploaderService;
use App\Entity\dit\DitDevisSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use App\Service\genererPdf\GenererPdfAcSoumis;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationDEVService;

class AcBcSoumisController extends Controller
{
    private $acSoumis;
    private $bcSoumis;
    private $bcRepository;
    private $genererPdfAc;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();

        $this->acSoumis = new AcSoumis();
        $this->bcSoumis = new BcSoumis();
        $this->bcRepository = self::$em->getRepository(BcSoumis::class);
        $this->genererPdfAc = new GenererPdfAcSoumis();
        $this->historiqueOperation = new HistoriqueOperationDEVService;

    }

    /**
     * @Route("/dit/ac-bc-soumis/{numDit}", name="dit_ac_bc_soumis")
     */
    public function traitementFormulaire(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        $devis = self::$em->getRepository(DitDevisSoumisAValidation::class)->findInfoDevis($numDit);
        // $dit = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        // dd($devis, $dit);
        $acSoumis = $this->initialisation($devis);
        
        $form = self::$validator->createBuilder(AcSoumisType::class, $acSoumis)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $acSoumis = $this->initialisation($devis);
            $numBc = $acSoumis->getNumeroBc();
            $numeroVersionMax = $this->bcRepository->findNumeroVersionMax($numBc);
            $bcSoumis = $this->ajoutDonneeBc($acSoumis, $numeroVersionMax);
            $this->envoieBcDansBd($bcSoumis);


            /** CREATION , FUSION, ENVOIE DW du PDF */
            $acSoumis->setNumeroVersion($bcSoumis->getNumVersion());
            $this->genererPdfAc->genererPdfAc($acSoumis);
            $fileName= $this->enregistrementEtFusionFichier($form, $numBc, $bcSoumis->getNumVersion());
            $this->genererPdfAc->copyToDWAcSoumis($fileName);// copier le fichier dans docuware

            $message = 'Le bon de commande et l\'accusé de reception  ont été soumis avec succès';
            $this->historiqueOperation->sendNotificationCreation($message, $numBc, 'dit_index', true);
        }

        self::$twig->display('dit/AcBcSoumis.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function enregistrementEtFusionFichier(FormInterface $form, string $numBc, string $numeroVersion)
    {
        $chemin = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/ac_bc/';
        $fileUploader = new FileUploaderService($chemin);
        $prefix = 'bc';
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $prefix, $numBc, true, $numeroVersion);

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

    private function initialisation(array $devis): AcSoumis
    {
        $this->acSoumis
            ->setDateCreation(new \DateTime($this->getDatesystem()))
            ->setNumeroDevis($devis[0]->getNumeroDevis())
            ->setStatutDevis($devis[0]->getStatut())
            ->setNumeroDit($devis[0]->getNumeroDit())
            ->setDateDevis($devis[0]->getDateHeureSoumission())
            ->setMontantDevis(0.00)
            ->setEmailContactHff('')
            ->setTelephoneContactHff('')
            ->setDevise($devis[0]->getDevise())
            ->setDateExpirationDevis((clone $devis[0]->getDateHeureSoumission())->modify('+30 days'))
        ;
        return $this->acSoumis;
    }
}