<?php

namespace App\Api\da;

use Exception;
use App\Entity\da\DaAfficher;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\da\EmailDaService;
use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Repository\da\DaAfficherRepository;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\application\ApplicationService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/demande-appro/action-sur-non-dispo")
 */
class ActionSurNonDispoApi extends Controller
{
    private $em;
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private EmailDaService $emailDaService;

    public function __construct()
    {
        parent::__construct();

        $this->em                       = $this->getEntityManager();
        $this->daAfficherRepository     = $this->em->getRepository(DaAfficher::class);
        $this->demandeApproLRepository  = $this->em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $this->em->getRepository(DemandeApproLR::class);
        $this->emailDaService           = new EmailDaService($this->getTwig(), $this->getUrlGenerator());
    }

    /**
     * @Route("/delete-articles", name="api_demande_appro_delete_articles", methods={"POST"})
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
     * @Route("/create-new-articles", name="api_demande_appro_create_new_articles", methods={"POST"})
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

            $demandeApproAvant = $daAffichers[0]->getDemandeAppro();
            if (!$demandeApproAvant) throw new Exception("aucun demande appro ne correspond dans la base de donnée.");

            /** 0. Nouveau numéro demande appro et statut */
            $numDa = $this->autoDecrement(ApplicationConstant::CODE_DAP);
            $statutDa = StatutDaConstant::STATUT_SOUMIS_APPRO;

            /** 1. Créer nouveau demande appro avec le nouveau numéro */
            $demandeAppro = $this->nouveauDemandeAppro($demandeApproAvant, $numDa, $statutDa);

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

            // Notification par email du demandeur sur les articles non dispo fournisseur
            $this->emailDaService->envoyerMailPourNonDispoArticle($demandeApproAvant, $daAffichers, $numDa, $this->getUser());

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

    private function nouveauDemandeAppro(DemandeAppro $oldDemandeAppro, string $numDa, string $statutDa): DemandeAppro
    {
        $newDemandeAppro = new DemandeAppro;
        $newDemandeAppro
            ->setNumeroDemandeAppro($numDa)
            ->setNumeroDemandeApproMere($numDa)
            ->setDaTypeId($oldDemandeAppro->getDaTypeId())
            ->setNumeroDemandeDit($oldDemandeAppro->getNumeroDemandeDit())
            ->setObjetDal($oldDemandeAppro->getObjetDal() . ' (Duplicata ' . $oldDemandeAppro->getNumeroDemandeAppro() . ')')
            ->setDetailDal($oldDemandeAppro->getDetailDal())
            ->setAgenceServiceEmetteur($oldDemandeAppro->getAgenceServiceEmetteur())
            ->setAgenceServiceDebiteur($oldDemandeAppro->getAgenceServiceDebiteur())
            ->setDateFinSouhaite($oldDemandeAppro->getDateFinSouhaite())
            ->setStatutDal($statutDa)
            ->setAgenceEmetteur($oldDemandeAppro->getAgenceEmetteur())
            ->setAgenceDebiteur($oldDemandeAppro->getAgenceDebiteur())
            ->setServiceDebiteur($oldDemandeAppro->getServiceDebiteur())
            ->setServiceEmetteur($oldDemandeAppro->getServiceEmetteur())
            ->setDemandeur($oldDemandeAppro->getDemandeur())
            ->setIdMateriel($oldDemandeAppro->getIdMateriel())
            ->setUser($oldDemandeAppro->getUser())
            ->setCodeSociete($oldDemandeAppro->getCodeSociete())
            ->setNiveauUrgence($oldDemandeAppro->getNiveauUrgence())
        ;
        $this->em->persist($newDemandeAppro);
        $this->em->flush();

        if ($newDemandeAppro->getId()) return $newDemandeAppro;
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
            ->setObjetDal($demandeAppro->getObjetDal())
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
            ->setCodeSociete($daAfficher->getCodeSociete())
        ;

        $this->em->persist($newDaAfficher);
    }

    private function updateDaAfficher(DaAfficher $daAfficher): void
    {
        $daAfficher
            ->setStatutCde(StatutBcConstant::STATUT_NON_DISPO)
            ->setNonDispo(true)
        ;
        $this->em->persist($daAfficher);
    }
}
