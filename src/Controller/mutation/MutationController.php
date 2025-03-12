<?php

namespace App\Controller\mutation;

use App\Controller\Controller;
use App\Controller\Traits\MutationTrait;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\Mutation;
use App\Entity\mutation\MutationSearch;
use App\Form\mutation\MutationFormType;
use App\Form\mutation\MutationSearchType;
use App\Model\mutation\MutationModel;
use App\Service\genererPdf\GeneratePdfMutation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MutationController extends Controller
{
    use MutationTrait;

    /**
     * @Route("/mutation/new", name="mutation_nouvelle_demande")
     */
    public function nouveau(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recuperation de l'utilisateur connecter
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        $mutation = new Mutation;
        $this->initialisationMutation($mutation, self::$em);

        $form = self::$validator->createBuilder(MutationFormType::class, $mutation)->getForm();

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
            } else if ((int) $mutationModel->getNombreDM($dateDebut, $dateFin, $matricule) > 0) {
            } else {
                $mutation = $this->enregistrementValeurDansMutation($form, self::$em, $user);
                $generatePdf = new GeneratePdfMutation;
                $generatePdf->genererPDF($this->donneePourPdf($form, $user));
                $this->envoyerPieceJointes($form, $this->fusionPdf);
                $generatePdf->copyInterneToDOCUWARE($mutation->getNumeroMutation(), $mutation->getAgenceEmetteur()->getCodeAgence() . $mutation->getServiceEmetteur()->getCodeService());
                $this->redirectToRoute("mutation_liste");
            }
        }

        self::$twig->display('mutation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mutation/list", name="mutation_liste")
     */
    public function listeMutation(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $mutationSearch = new MutationSearch();

        $form = self::$validator->createBuilder(MutationSearchType::class, $mutationSearch, [
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

        $repository = self::$em->getRepository(Mutation::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $mutationSearch);

        //enregistre le critère dans la session
        $this->sessionService->set('mutation_search_criteria', $criteria);

        self::$twig->display(
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
     * @Route("/mutation/detail/{id}", name="mutation_detail")
     */
    public function detailMutation($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** 
         * @var Mutation entité correspondant à l'id $id
         */
        $mutation = self::$em->getRepository(Mutation::class)->find($id);

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
        self::$twig->display(
            'mutation/detail.html.twig',
            [
                'mutation' => $mutation
            ]
        );
    }
}
