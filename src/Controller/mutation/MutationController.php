<?php

namespace App\Controller\mutation;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\mutation\Mutation;
use App\Model\mutation\MutationModel;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\MutationSearch;
use App\Form\mutation\MutationFormType;
use App\Controller\Traits\MutationTrait;
use App\Form\mutation\MutationSearchType;
use Symfony\Component\HttpFoundation\Request;
use App\Service\genererPdf\GeneratePdfMutation;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationMUTService;
use App\Service\FusionPdf;

/**
 * @Route("/rh/mutation")
 */
class MutationController extends Controller
{
    use MutationTrait;
    private $historiqueOperation;
    private $fusionPdf;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationMUTService($this->getEntityManager());
        $this->fusionPdf = new FusionPdf();
    }

    /**
     * @Route("/new", name="mutation_nouvelle_demande")
     */
    public function nouveau(Request $request)
    {
        $mutation = new Mutation;
        $this->initialisationMutation($mutation, $this->getEntityManager());

        $form = $this->getFormFactory()->createBuilder(MutationFormType::class, $mutation)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mutationModel = new MutationModel;
            /** 
             * @var Mutation $data
             */
            $data = $form->getData();
            $dateDebut = $data->getDateDebut()->format('Y-m-d');
            $dateFin = $data->getDateFin() ? $data->getDateFin()->format('Y-m-d') : '';
            $matricule = $data->getMatricule();
            if ((int) $mutationModel->getNombreOM($dateDebut, $dateFin, $matricule) > 0) {
                $this->historiqueOperation->sendNotificationCreation("La demande de mutation a échoué car le matricule '$matricule' est déjà rattaché à une demande d'ordre de mission entre les plages de dates.", '-', 'mutation_liste', false);
            } else if ((int) $mutationModel->getNombreDM($dateDebut, $dateFin, $matricule) > 0) {
                $this->historiqueOperation->sendNotificationCreation("La demande de mutation a échoué car le matricule '$matricule' est déjà rattaché à une demande de mutation entre les plages de dates.", '-', 'mutation_liste', false);
            } else {
                $mutation = $this->enregistrementValeurDansMutation($form, $this->getEntityManager());
                $generatePdf = new GeneratePdfMutation;
                $generatePdf->genererPDF($this->donneePourPdf($form));
                $this->envoyerPieceJointes($form, $this->fusionPdf);
                $generatePdf->copyInterneToDOCUWARE($mutation->getNumeroMutation(), $mutation->getAgenceEmetteur()->getCodeAgence() . $mutation->getServiceEmetteur()->getCodeService());
                $this->historiqueOperation->sendNotificationCreation('La demande de mutation a été enregistrée avec succès', $mutation->getNumeroMutation(), 'mutation_liste', true);
            }
        }

        return $this->render('mutation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/liste", name="mutation_liste")
     */
    public function listeMutation(Request $request)
    {
        $mutationSearch = new MutationSearch();

        $form = $this->getFormFactory()->createBuilder(MutationSearchType::class, $mutationSearch, [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mutationSearch = $form->getData();
        }

        $criteria = [];
        //transformer l'objet ditSearch en tableau
        $criteria = $mutationSearch->toArray();

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository = $this->getEntityManager()->getRepository(Mutation::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $mutationSearch);

        //enregistre le critère dans la session
        $this->getSessionService()->set('mutation_search_criteria', $criteria);

        return $this->render(
            'mutation/list.html.twig',
            [
                'form'        => $form->createView(),
                'data'        => $paginationData['data'],
                'currentPage' => $paginationData['currentPage'],
                'lastPage'    => $paginationData['lastPage'],
                'resultat'    => $paginationData['totalItems'],
                'criteria'    => $criteria,
            ]
        );
    }

    /**
     * @Route("/detail/{id}", name="mutation_detail")
     */
    public function detailMutation($id)
    {
        /** 
         * @var Mutation entité correspondant à l'id $id
         */
        $mutation = $this->getEntityManager()->getRepository(Mutation::class)->find($id);

        $avanceSurIndemnite = !($mutation->getNombreJourAvance() === null);
        $tabModePaiement = explode(':', $mutation->getModePaiement());
        $modePaiement = $tabModePaiement[0];
        $modePaiementLabel = $modePaiement === 'MOBILE MONEY' ? 'TEL' : 'CPT';
        $modePaiementValue = $tabModePaiement[1];

        $mutation = [
            'nom'                           => $mutation->getNom(),
            'prenom'                        => $mutation->getPrenom(),
            'matricule'                     => $mutation->getMatricule(),
            'categorie'                     => $mutation->getCategorie()->getDescription(),
            'agenceEmetteur'                => $mutation->getAgenceEmetteur()->getCodeAgence() . ' ' . $mutation->getAgenceEmetteur()->getLibelleAgence(),
            'serviceEmetteur'               => $mutation->getServiceEmetteur()->getCodeService() . ' ' . $mutation->getServiceEmetteur()->getLibelleService(),
            'agenceDebiteur'                => $mutation->getAgenceDebiteur()->getCodeAgence() . ' ' . $mutation->getAgenceDebiteur()->getLibelleAgence(),
            'serviceDebiteur'               => $mutation->getServiceDebiteur()->getCodeService() . ' ' . $mutation->getServiceDebiteur()->getLibelleService(),
            'dateDebutLabel'                => $avanceSurIndemnite ? "Date de début d'avance sur indemnité de chantier" : 'Date de début de mutation',
            'dateDebut'                     => $mutation->getDateDebut() === null ? '' : $mutation->getDateDebut()->format('d/m/Y'),
            'dateFin'                       => $mutation->getDateFin() === null ? '' : $mutation->getDateFin()->format('d/m/Y'),
            'site'                          => $mutation->getSite()->getNomZone(),
            'lieuMutation'                  => $mutation->getLieuMutation(),
            'client'                        => $mutation->getClient(),
            'motifMutation'                 => $mutation->getMotifMutation(),
            'avanceSurIndemnite'            => $avanceSurIndemnite ? 'OUI' : 'NON',
            'nombreJourAvance'              => $mutation->getNombreJourAvance(),
            'indemniteForfaitaire'          => $mutation->getIndemniteForfaitaire(),
            'supplementJournaliere'         => '',
            'totalIndemniteForfaitaire'     => $mutation->getTotalIndemniteForfaitaire(),
            'autresDepense1'                => $mutation->getAutresDepense1(),
            'autresDepense2'                => $mutation->getAutresDepense2(),
            'totalAutresDepenses'           => $mutation->getTotalAutresDepenses(),
            'motifAutresDepense1'           => $mutation->getMotifAutresDepense1(),
            'motifAutresDepense2'           => $mutation->getMotifAutresDepense2(),
            'totalGeneralPayer'             => $mutation->getTotalGeneralPayer(),
            'modePaiement'                  => $modePaiement,
            'modePaiementLabel'             => $modePaiementLabel,
            'modePaiementValue'             => $modePaiementValue,
            'pieceJoint01'                  => $mutation->getPieceJoint01(),
            'pieceJoint02'                  => $mutation->getPieceJoint02(),
        ];
        return $this->render(
            'mutation/detail.html.twig',
            [
                'mutation' => $mutation
            ]
        );
    }
}
