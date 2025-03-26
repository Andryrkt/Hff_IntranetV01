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
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;
use Symfony\Component\Form\Test\FormInterface;
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
    private DocDemandePaiement $docDemandePaiement;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    
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
        $this->docDemandePaiement = new DocDemandePaiement();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] .'/ddp';
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

            /** 
             * @var UploadedFile|null $file 
            * recupération et copie dans le dossier de destination
             */
            $file = $form->get('pieceJoint01')->getData();
            $nomDeFichier = $file->getClientOriginalName();
            $this->traitementDeFichier->upload($file, $this->cheminDeBase, $nomDeFichier);
            

            $this->ajoutDesInfoNecessaire($data, $numDdp, $id, $nomDeFichier);
            
            /** ENREGISTREMENT DANS BD */
            $this->EnregistrementBdDdp($data); // enregistrement des données dans la table demande_paiement
            $this->EnregistrementBdDdpl($data);// enregistrement des données dans la table demande_paiement_ligne
            $this->enregisterDdpF($data);


            if(copy('//192.168.0.15/GCOT_DATA/TRANSIT/DD0070A25/PDV_10236125.PDF','C:\wamp64\www\Upload\ddpanarana.pdf')) {

                dd("Le fichier a été copié avec succès.");
} else {
    dd("Erreur lors de la copie du fichier.");
}
            /** COPIER LES FICHIERS */
            // $this->copierFichierDistant($data);

            /** GENERATION DE PDF */
            $this->generatePdfDdp->genererPDF($data);

            $this->historiqueOperation->sendNotificationSoumission('Le document a été généré avec succès', $numDdp, 'ddp_liste', true);
        }

        self::$twig->display('ddp/demandePaiementNew.html.twig', [
            'id_type' => $id,
            'form' => $form->createView()
        ]);
    }

    
    private function ajoutDesInfoNecessaire(DemandePaiement $data, string $numDdp, int $id, string $nomDeFichier)
    {
        $data = $this->ajoutTypeDemande($data, $id);
        $lesFichiers = $this->ajoutDesFichiers($data, $nomDeFichier);

        $data
            ->setNumeroDdp($numDdp)// ajout du numero DDP dans l'entity DDP
            ->setAgenceDebiter($data->getAgence()->getCodeAgence())
            ->setServiceDebiter($data->getService()->getCodeService())
            ->setAdresseMailDemandeur($this->getEmail())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setStatut('OUVERT')
            ->setMontantAPayers((float)$this->transformChaineEnNombre($data->getMontantAPayer()))
            ->setLesFichiers($lesFichiers)
        ;
    }

    /**
     * TODO: ARECITIFER
     *
     * @param DemandePaiement $data
     * @return void
     */
    private function copierFichierDistant(DemandePaiement $data)
    {
        $cheminDeFichiers = $this->recupCheminFichierDistant($data);
        $cheminDestination = $_ENV['BASE_PATH_FICHIER'] . '/ddp/fichiers';

        foreach ($cheminDeFichiers as $cheminDeFichier) {
            $nomFichier = $this->nomFichier($cheminDeFichier);
            $destinationFinal = $cheminDestination.'/'.$nomFichier;
            copy($cheminDeFichier, $destinationFinal);
        }
    }

    private function enregisterDdpF(DemandePaiement $data) : void
    {
        $donners = $this->recuperationDonnerDdpF($data);
        foreach ($donners as $value) {
            self::$em->persist($value);
        }

        self::$em->flush();
    }

    private function ajoutDesFichiers(DemandePaiement $data, string $fichierTelechargerName): array
    {
        $lesCheminsFichiers = $this-> recupCheminFichierDistant($data);

        $lesFichiers = [];
        foreach ($lesCheminsFichiers as $value) {
            $lesFichiers[] = $this->nomFichier($value);
        }

        $lesFichiers[] = $fichierTelechargerName;

        return $lesFichiers;
        
    }

    private function recupCheminFichierDistant(DemandePaiement $data): array
    {
        $numFrs = $data->getNumeroFournisseur();
        $numCde = $data->getNumeroCommande();

        $numCdesString = TableauEnStringService::TableauEnString(',', $numCde);
        
        $listeGcot = $this->demandePaiementModel->findListeGcot($numFrs, $numCdesString);

        $cheminDeFichiers = [];
        foreach ($listeGcot as $value) {
            $numDocDouane = $value['Numero_Dossier_Douane'];

            $dossiers = $this->demandePaiementModel->findListeDoc($numDocDouane);
            foreach ($dossiers as  $dossier) {
                $cheminDeFichiers[] = $dossier['Nom_Fichier'];
            }
        }

        return $cheminDeFichiers;
    }

    private function recuperationDonnerDdpF(DemandePaiement $data): array
    {
        $numDdp = $data->getNumeroDdp();
        $cheminDeFichiers =$this->recupCheminFichierDistant($data);

        $donners = [];
        foreach ($cheminDeFichiers as $cheminDeFichier) {
            $nomFichier = $this->nomFichier($cheminDeFichier);
            $docDemandePaiement = new DocDemandePaiement();
            $donners[] = $docDemandePaiement
                ->setNumeroDdp($numDdp)
                ->setTypeDocumentId($data->getTypeDemandeId())
                ->setNomFichier($nomFichier)
            ;
        }

        return $donners;
    }

    private function nomFichier(string $cheminFichier): string 
    {
        $motExacteASupprimer = [
            '\\\\192.168.0.15',
            '\\GCOT_DATA',
            '\\TRANSIT',
        ];
    
        $motCommenceASupprimer = ['\\DD'];
    
        return $this->enleverPartiesTexte($cheminFichier, $motExacteASupprimer, $motCommenceASupprimer);
    }
    
    private function enleverPartiesTexte(string $texte, array $motsExacts, array $motsCommencent): string 
    {
        // Supprimer les correspondances exactes
        foreach ($motsExacts as $mot) {
            $texte = str_replace($mot, '', $texte);
        }
    
        // Supprimer les parties qui commencent par un mot donné
        foreach ($motsCommencent as $motDebut) {
            $pattern = '/' . preg_quote($motDebut, '/') . '[^\\\\]*/';
            $texte = preg_replace($pattern, '', $texte);
        }
    
        // Supprimer les éventuels slashes de début
        return ltrim($texte, '\\/');
    }
    

    private function transformChaineEnNombre(string $nombre): float
    {
        $nombre = str_replace(' ', '', $nombre); // Supprimer les espaces
        $nombre = str_replace(',', '.', $nombre); // Remplacer la virgule par un point pour le format décimal

        $nombre_formaté = number_format((float)$nombre, 2, '.', ''); // Conversion en float et formatage
        return  $nombre_formaté; // Affiche : 11124522.46
    }
    
    private function EnregistrementBdDdpl($data): void
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
        $demandePaiementLignes = [];
        
        for ($i=0; $i < count($data->getNumeroCommande()); $i++) { 
            $demandePaiementLigne = new DemandePaiementLigne();
            $demandePaiementLignes[] = $demandePaiementLigne
                ->setNumeroDdp($data->getNumeroDdp())
                ->setNumeroLigne($i)
                ->setNumeroCommande($data->getNumeroCommande()[$i])
                ->setNumeroFacture(null !== $data->getNumeroFacture() || isset($data->getNumeroFacture()[$i]) ? $data->getNumeroFacture()[$i] : '-')
                ->setMontantFacture($this->transformChaineEnNombre($data->getMontantAPayer()))
            ;
        }

        return $demandePaiementLignes;
    }



    /**
     * methode qui permet d'enregestrer les données dans la table demande_paiement
     */
    private function EnregistrementBdDdp(DemandePaiement $data): void
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
    private function modificationDernierIdApp(string $numDdp): void
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