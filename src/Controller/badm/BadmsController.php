<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\badm\BadmForm1Type;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationBADMService;
use App\Model\badm\BadmModel;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmsController extends Controller
{
    use AutorisationTrait;

    private $historiqueOperation;
    private $badm;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationBADMService($this->getEntityManager());
        $this->badm = new BadmModel();
    }

    /**
     * @Route("/badm-form1", name="badms_newForm1")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_BADM);
        /** FIN AUtorisation acées */

        /** RECUPERATION ID USER CONNECTER */
        $user = $this->getUser();
        /** INITIALISATION*/
        $badm = new Badm();
        $agenceServiceIps = $this->agenceServiceIpsString();


        $badm
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
        ;

        $form = $this->getFormFactory()->createBuilder(BadmForm1Type::class, $badm)->getForm();


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            if ($badm->getTypeMouvement() === null) {
                $message = " choisir une type de mouvement";

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            }

            if ($badm->getIdMateriel() === null &&  $badm->getNumParc() === null && $badm->getNumSerie() === null) {
                $message = " Renseigner l'un des champs (Id Matériel, numéro Série et numéro Parc)";

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } else {
                //recuperation de l'id du type de mouvement
                $idTypeMouvement = $badm->getTypeMouvement()->getId();

                //recuperation des information du materiel dans la base de donnée informix
                $data = $this->badm->findAll($badm->getIdMateriel(),  $badm->getNumParc(), $badm->getNumSerie());

                if (empty($data)) {
                    $message = "Matériel déjà vendu ou L'information saisie n'est pas correcte.";

                    $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
                } else {
                    //recuperation du materiel dan sl abase de donner sqlserver
                    $materiel = $this->getEntityManager()->getRepository(Badm::class)->findOneBy(['idMateriel' => $data[0]['num_matricule']], ['numBadm' => 'DESC']);

                    //si le materiel n'est pas encore dans la base de donner on donne la valeur 0 pour l'idType ld emouvmentMateriel
                    $idTypeMouvementMateriel = $materiel === null ? 0 : $materiel->getTypeMouvement()->getId();


                    $agenceMaterielId = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]])->getId();


                    if ($data[0]["code_service"] === null || $data[0]["code_service"] === '' || $data[0]["code_service"] === null) {
                        $serviceMaterilId =  $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => 'COM'])->getId();
                    } else {
                        $serviceMaterilId =  $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]])->getId();
                    }
                    // dd($agenceMaterielId, $serviceMaterilId);
                    //condition de blocage
                    $conditionTypeMouvStatut = $idTypeMouvement === $idTypeMouvementMateriel && in_array($materiel->getStatutDemande()->getId(), [15, 16, 21, 46, 23, 25, 29, 30]);
                    $conditionEntreeParc = $idTypeMouvement === 1 && $data[0]['code_affect'] !== 'VTE';
                    $conditionChangementAgServ_1 = $idTypeMouvement === 2 && $data[0]['code_affect'] === 'VTE';
                    $conditionChangementAgServ_2 = $idTypeMouvement === 2 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                    $conditionCessionActif = $idTypeMouvement === 4 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                    $conditionMiseAuRebut = $idTypeMouvement === 5 && $data[0]['code_affect'] === 'CAS';
                    $conditionRoleUtilisateur = in_array(1, $user->getRoleIds());
                    $conditionAgenceServiceAutoriser = in_array($agenceMaterielId, $user->getAgenceAutoriserIds()) && in_array($serviceMaterilId, $user->getServiceAutoriserIds());
                }
            }

            if ($conditionEntreeParc) {
                $message = 'Ce matériel est déjà en PARC';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($conditionChangementAgServ_1) {
                $message = "L'agence et le service associés à ce matériel ne peuvent pas être modifiés.";

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($conditionChangementAgServ_2) {
                $message = " l'affectation matériel ne permet pas cette opération";

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($conditionCessionActif) {
                $message = "Ce matériel ne peut pas mise en cession d'actif ";

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($conditionMiseAuRebut) {
                $message = 'Ce matériel ne peut pas être mis au rebut';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($conditionTypeMouvStatut) {
                $message = 'ce matériel est encours de traitement pour ce type de mouvement ';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } else {

                $badm
                    ->setIdMateriel($data[0]['num_matricule'])
                    ->setNumParc($data[0]['num_parc'])
                    ->setNumSerie($data[0]['num_serie'])
                ;

                $formData = [
                    'idMateriel' => $badm->getIdMateriel(),
                    'numParc' => $badm->getNumParc(),
                    'numSerie' => $badm->getNumSerie(),
                    'typeMouvemnt' => $badm->getTypeMouvement()
                ];
                //envoie des donner dan la session
                $this->getSessionService()->set('badmform1Data', $formData);
                if ($conditionRoleUtilisateur) {
                    $this->redirectToRoute("badms_newForm2");
                } elseif (!$conditionAgenceServiceAutoriser) {
                    $message = " vous n'êtes pas autoriser à consulter ce matériel";

                    $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
                } else {
                    $this->redirectToRoute("badms_newForm2");
                }
            }
        }

        $this->logUserVisit('badms_newForm1'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/firstForm.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}
