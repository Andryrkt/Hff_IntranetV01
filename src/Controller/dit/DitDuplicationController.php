<?php

namespace App\Controller\dit;

use App\Model\dit\DitModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\DitTrait;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Form\dit\demandeInterventionType;
use App\Service\genererPdf\GenererPdfDit;
use App\Controller\Traits\AutorisationTrait;
use App\Dto\Dit\DemandeInterventionDto;
use App\Factory\Dit\DemandeInterventionFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationDITService;
use App\Service\FusionPdf;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitDuplicationController extends Controller
{
    use DitTrait;
    use FormatageTrait;
    use AutorisationTrait;

    private $historiqueOperation;
    private $fusionPdf;
    private $demandeInterventionFactory;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDITService($this->getEntityManager());
        $this->fusionPdf = new FusionPdf();
        $this->demandeInterventionFactory = new DemandeInterventionFactory($this->getEntityManager(), $this->getDitModel(), $this->historiqueOperation);
    }

    /**
     * @Route("/dit-duplication/{id<\d+>}/{numDit<\w+>}", name="dit_duplication")
     */
    public function Duplication($numDit, $id, Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DIT);

        $user = $this->getUser();
        $dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->find($id);

        // Simplification de la logique de duplication
        $demandeInterventions = new DemandeIntervention();
        $demandeInterventions
            ->setAgence($dit->getAgence())
            ->setService($dit->getService())
            ->setTypeDocument($dit->getTypeDocument())
            ->setTypeReparation($dit->getTypeReparation())
            ->setReparationRealise($dit->getReparationRealise())
            ->setCategorieDemande($dit->getCategorieDemande())
            ->setInternetExterne($dit->getInternetExterne())
            ->setNomClient($dit->getNomClient())
            ->setNumeroTel($dit->getNumeroTel())
            ->setDatePrevueTravaux($dit->getDatePrevueTravaux())
            ->setDemandeDevis($dit->getDemandeDevis())
            ->setIdNiveauUrgence($dit->getIdNiveauUrgence())
            ->setAvisRecouvrement($dit->getAvisRecouvrement())
            ->setClientSousContrat($dit->getClientSousContrat())
            ->setObjetDemande($dit->getObjetDemande())
            ->setDetailDemande($dit->getDetailDemande())
            ->setLivraisonPartiel($dit->getLivraisonPartiel())
            ->setNumParc($dit->getNumParc())
            ->setNumSerie($dit->getNumSerie())
            ->setIdMateriel($dit->getIdMateriel())
            ->setConstructeur($dit->getConstructeur())
            ->setModele($dit->getModele())
            ->setDesignation($dit->getDesignation())
            ->setCasier($dit->getCasier())
            ->setKm($dit->getKm())
            ->setHeure($dit->getHeure())
        ;

        $form = $this->getFormFactory()->createBuilder(demandeInterventionType::class, $demandeInterventions)->getForm();
        $this->traitementFormulaire($form, $request, $user);

        $this->logUserVisit('dit_duplication', ['id' => $id, 'numDit' => $numDit]);

        return $this->render('dit/duplication.html.twig', [
            'form' => $form->createView(),
            'dit' => $dit,
            'estAvoir' => $this->estAvoir($dit),
            'estRefactorisation' => $this->estRefacturation($dit)
        ]);
    }

    private function estAvoir(DemandeIntervention $dit): bool
    {
        $position = $this->getDitModel()->getPosition($dit->getNumeroDemandeIntervention());
        if (!empty($position)) {
            $positionOR =  in_array($position[0], ['FC', 'CP']); //l'OR rattaché à la DIT initale est facturé / comptabilisé (seor_pos in ('FC','CP')
            $statutDit = $dit->getIdStatutDemande()->getId() === DemandeIntervention::STATUT_CLOTUREE_VALIDER; // le dernier statut de la DIT inital est 'Validé'
            $numeroAvoir = $dit->getNumeroDemandeDitAvoit() === null;
            return $positionOR && $statutDit && $numeroAvoir;
        }

        return false;
    }

    private function estRefacturation(DemandeIntervention $dit): bool
    {
        $position = $this->getDitModel()->getPosition($dit->getNumeroDemandeIntervention());
        if (!empty($position)) {
            $niAvoirNiRefac = $dit->getEstDitAvoir() === false && $dit->getEstDitRefacturation() === false; //b. la DIT initiale n'est ni une DIT d'avoir, ni une DIT de refacturation 
            $positionOR =  in_array($position[0], ['FC', 'CP']); //c. l'OR rattaché à la DIT initale est facturé / comptabilisé (seor_pos in ('FC','CP')
            $numeroAvoir = $dit->getNumeroDemandeDitAvoit() <> null;
            return $positionOR && $niAvoirNiRefac && $numeroAvoir;
        }

        return false;
    }

    private function traitementFormulaire($form, Request $request, $user)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeIntervention $ditFromForm */
            $ditFromForm = $form->getData();

            if (empty($ditFromForm->getIdMateriel())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du matériel.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
                return;
            }

            if ($ditFromForm->getInternetExterne() === "EXTERNE" && empty($ditFromForm->getNomClient()) && empty($ditFromForm->getNumeroClient())) {
                $message = 'Échec lors de la création de la DIT... Impossible de récupérer les informations du client.';
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'dit_index');
                return;
            }

            // 1. Créer le DTO
            $dto = DemandeInterventionDto::createFromEntity($ditFromForm);

            // 2. Enrichir le DTO (logique de infoEntrerManuel)
            $em = $this->getEntityManager();
            $dto->utilisateurDemandeur = $user->getNomUtilisateur();
            $dto->heureDemande = $this->getTime();
            $dto->dateDemande = new \DateTime($this->getDatesystem());
            $dto->idStatutDemande = $em->getRepository(\App\Entity\admin\StatutDemande::class)->find(50);
            $dto->numeroDemandeIntervention = $this->autoDecrementDIT('DIT');
            $dto->mailDemandeur = $user->getMail();

            // 3. Créer l'entité via la factory
            $demandeIntervention = $this->createDemandeInterventionFromDto($dto);

            // 4. Mettre à jour le dernier ID et persister
            $this->modificationDernierIdApp($demandeIntervention);
            $this->getEntityManager()->persist($demandeIntervention);
            $this->getEntityManager()->flush();

            // 5. Générer PDF et gérer les pièces jointes
            if (!in_array((int)$demandeIntervention->getIdMateriel(), [14571, 7669, 7670, 7671, 7672, 7673, 7674, 7675, 7677, 9863])) {
                $historiqueMateriel = $this->historiqueInterventionMateriel($demandeIntervention);
            } else {
                $historiqueMateriel = [];
            }

            $genererPdfDit = new GenererPdfDit();
            $genererPdfDit->genererPdfDit($demandeIntervention, $historiqueMateriel);
            $this->envoiePieceJoint($form, $demandeIntervention, $this->fusionPdf);
            $genererPdfDit->copyInterneToDOCUWARE($demandeIntervention->getNumeroDemandeIntervention(), str_replace("-", "", $demandeIntervention->getAgenceServiceEmetteur()));

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $demandeIntervention->getNumeroDemandeIntervention(), 'dit_index', true);
        }
    }



    private function modificationDernierIdApp(DemandeIntervention $demandeIntervention)
    {
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
        $application->setDerniereId($demandeIntervention->getNumeroDemandeIntervention());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();

        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }
}
