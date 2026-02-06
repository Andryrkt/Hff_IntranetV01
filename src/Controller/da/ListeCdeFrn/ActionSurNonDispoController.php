<?php

namespace App\Controller\da\ListeCdeFrn;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/demande-appro")
 */
class ActionSurNonDispoController extends Controller
{
    private $em;
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();

        $this->em                       = $this->getEntityManager();
        $this->daAfficherRepository     = $this->em->getRepository(DaAfficher::class);
        $this->demandeApproLRepository  = $this->em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $this->em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/da-list-cde-frn/delete-articles", name="api_list_cde_frn_delete_articles", methods={"POST"})
     */
    public function deleteArticles(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $daAfficherIds = $data['ids'] ?? [];
        $lines = $data['lines'] ?? [];
        $numDa = $data['numDa'] ?? "";

        if (!$daAfficherIds || !$lines || !$numDa) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la suppression',
                'message' => 'Impossible de supprimer. Merci de vérifier les informations et de réessayer.',
            ], 400);
        }

        try {
            $connectedUserName = $this->getUserName();

            $this->daAfficherRepository->markAsDeletedByListId($daAfficherIds, $connectedUserName);
            $this->demandeApproLRepository->deleteByNumDaAndLineNumbers($numDa, $lines);
            $this->demandeApproLRRepository->deleteByNumDaAndLineNumbers($numDa, $lines);

            $count = count($daAfficherIds);
            $label = $count > 1 ? 'articles supprimés' : 'article supprimé';

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Action effectuée',
                'message' => "$count $label avec succès.",
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la suppression',
                'message' => 'Impossible de supprimer certains articles. Merci de réessayer plus tard.<br> Message d\'erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @Route("/da-list-cde-frn/create-new-articles", name="api_list_cde_frn_create_new_articles", methods={"POST"})
     */
    public function createNewDa(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $daAfficherIds = $data['ids'] ?? [];

        if (!$daAfficherIds) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la création',
                'message' => 'Impossible de créer de nouveaux articles. Merci de vérifier les informations et de réessayer.',
            ], 400);
        }

        try {
            /** @var DaAfficher[] $daAffichers tableau d'objets DaAfficher correpondant aux ID dans daAfficherIds */
            $daAffichers = $this->daAfficherRepository->findBy(['id' => $daAfficherIds]); // objets DaAfficher correpondant aux ID dans daAfficherIds

            if (!$daAffichers) throw new Exception("aucun article correspondant dans la base de donnée.");

            $demandeAppro = $daAffichers[0]->getDemandeAppro();
            if (!$demandeAppro) throw new Exception("aucun demande appro ne correspond dans la base de donnée.");

            /** 0. Nouveau numéro demande appro et statut */
            $numDa = $this->autoDecrement('DAP');
            $statutDa = DemandeAppro::STATUT_SOUMIS_APPRO;

            /** 1. Créer nouveau demande appro avec le nouveau numéro */
            $demandeAppro = $this->nouveauDemandeAppro($demandeAppro, $numDa, $statutDa);

            foreach ($daAffichers as $daAfficher) {
                /** 2. Créer DAL à partir de $daAfficher */
                $this->nouveauDemandeApproLine($daAfficher, $demandeAppro, $numDa, $statutDa);

                /** 3. Gérer l'historisation dans DaAfficher */
                $this->ajouterDansDaAfficher($daAfficher, $demandeAppro, $numDa, $statutDa);

                /** 4. Mettre à jour $daAfficher */
                $this->updateDaAfficher($daAfficher);
            }

            /** 5. Modifier la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->em);
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            $this->em->flush();

            $count = count($daAfficherIds);
            $label = $count > 1 ? 'articles ont été ajoutés' : 'article a été ajouté';

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Action réussie',
                'message' => "Succès : $count $label avec succès.<br>Le numéro correspondant : <b>$numDa</b>",
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'title'   => 'Erreur lors de la création',
                'message' => 'Impossible de créer certains articles. Merci de réessayer plus tard.<br> Message d\'erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    private function nouveauDemandeAppro(DemandeAppro $demandeAppro, string $numDa, string $statutDa): DemandeAppro
    {
        $da = new DemandeAppro;
        $da
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeApproMere($numDa)
            ->setDaTypeId($demandeAppro->getDaTypeId())
            ->setNumeroDemandeDit($demandeAppro->getNumeroDemandeDit())
            ->setObjetDal($demandeAppro->getObjetDal())
            ->setDetailDal($demandeAppro->getDetailDal())
            ->setAgenceServiceEmetteur($demandeAppro->getAgenceServiceEmetteur())
            ->setAgenceServiceDebiteur($demandeAppro->getAgenceServiceDebiteur())
            ->setDateFinSouhaite($demandeAppro->getDateFinSouhaite())
            ->setStatutDal($statutDa)
            ->setAgenceEmetteur($demandeAppro->getAgenceEmetteur())
            ->setAgenceDebiteur($demandeAppro->getAgenceDebiteur())
            ->setServiceDebiteur($demandeAppro->getServiceDebiteur())
            ->setServiceEmetteur($demandeAppro->getServiceEmetteur())
            ->setDemandeur($demandeAppro->getDemandeur())
            ->setIdMateriel($demandeAppro->getIdMateriel())
            ->setUser($demandeAppro->getUser())
            ->setNiveauUrgence($demandeAppro->getNiveauUrgence())
        ;
        $this->em->persist($da);
        $this->em->flush();

        if ($da->getId()) return $da;
        else throw new Exception("Erreur lors de la création de la Demande Appro.");
    }

    private function nouveauDemandeApproLine(DaAfficher $daAfficher, DemandeAppro $demandeAppro, string $numDa, string $statutDa): void
    {
        $dal = new DemandeApproL;
        $dal
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroLigne($daAfficher->getNumeroLigne())
            ->setQteDem($daAfficher->getQteDem())
            ->setArtConstp($daAfficher->getArtConstp())
            ->setArtRefp($daAfficher->getArtRefp())
            ->setArtDesi($daAfficher->getArtDesi())
            ->setArtFams1($daAfficher->getArtFams1())
            ->setArtFams2($daAfficher->getArtFams2())
            ->setCodeFams1($daAfficher->getCodeFams1())
            ->setCodeFams2($daAfficher->getCodeFams2())
            ->setNumeroFournisseur($daAfficher->getNumeroFournisseur())
            ->setNomFournisseur($daAfficher->getNomFournisseur())
            ->setDateFinSouhaite($daAfficher->getDateFinSouhaite())
            ->setCommentaire($daAfficher->getCommentaire())
            ->setStatutDal($statutDa)
            ->setCatalogue($daAfficher->getCatalogue())
            ->setDemandeAppro($demandeAppro)
            ->setPrixUnitaire($daAfficher->getPrixUnitaire())
            ->setNumeroDit($daAfficher->getNumeroDemandeDit())
            ->setJoursDispo($daAfficher->getJoursDispo())
        ;
        $this->em->persist($dal);
    }

    private function ajouterDansDaAfficher(DaAfficher $daAfficher, DemandeAppro $demandeAppro, string $numDa, string $statutDa): void
    {
        $newDaAfficher = new DaAfficher;
        $newDaAfficher
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeApproMere($numDa)
            ->setNumeroDemandeDit($daAfficher->getNumeroDemandeDit())
            ->setStatutDal($statutDa)
            ->setObjetDal($daAfficher->getObjetDal())
            ->setDetailDal($daAfficher->getDetailDal())
            ->setNumeroLigne($daAfficher->getNumeroLigne())
            ->setQteDem($daAfficher->getQteDem())
            ->setArtRefp($daAfficher->getArtRefp())
            ->setArtConstp($daAfficher->getArtConstp())
            ->setArtDesi($daAfficher->getArtDesi())
            ->setArtFams1($daAfficher->getArtFams1())
            ->setArtFams2($daAfficher->getArtFams2())
            ->setCodeFams1($daAfficher->getCodeFams1())
            ->setCodeFams2($daAfficher->getCodeFams2())
            ->setNumeroFournisseur($daAfficher->getNumeroFournisseur())
            ->setNomFournisseur($daAfficher->getNomFournisseur())
            ->setDateFinSouhaite($daAfficher->getDateFinSouhaite())
            ->setCommentaire($daAfficher->getCommentaire())
            ->setPrixUnitaire($daAfficher->getPrixUnitaire())
            ->setTotal($daAfficher->getTotal())
            ->setCatalogue($daAfficher->getCatalogue())
            ->setNumeroVersion(1)
            ->setNiveauUrgence($daAfficher->getNiveauUrgence())
            ->setJoursDispo($daAfficher->getJoursDispo())
            ->setDemandeur($daAfficher->getDemandeur())
            ->setDaTypeId($daAfficher->getDaTypeId())
            ->setDateDemande($demandeAppro->getDateCreation())
            ->setAgenceEmetteur($daAfficher->getAgenceEmetteur())
            ->setAgenceDebiteur($daAfficher->getAgenceDebiteur())
            ->setServiceDebiteur($daAfficher->getServiceDebiteur())
            ->setServiceEmetteur($daAfficher->getServiceEmetteur())
            ->setDemandeAppro($demandeAppro)
            ->setDit($daAfficher->getDit())
        ;

        $this->em->persist($newDaAfficher);
    }

    private function updateDaAfficher(DaAfficher $daAfficher): void
    {
        $daAfficher
            ->setStatutCde(DaSoumissionBc::STATUT_NON_DISPO)
            ->setNonDispo(true)
        ;
        $this->em->persist($daAfficher);
    }
}
