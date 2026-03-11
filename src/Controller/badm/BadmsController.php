<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Model\badm\BadmModel;
use App\Controller\Controller;
use App\Form\badm\BadmForm1Type;
use App\Constants\admin\ApplicationConstant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationBADMService;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmsController extends Controller
{
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
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        /** INITIALISATION*/
        $badm = new Badm();
        $agenceServiceIps = $this->agenceServiceIpsString();

        $badm
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setCodeSociete($codeSociete)
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
                $data = $this->badm->findAll($badm->getIdMateriel(),  $badm->getNumParc(), $badm->getNumSerie(), $badm->getCodeSociete());

                $codeAgenceMateriel = $data[0]["agence"];
                $codeServiceMateriel = $data[0]["code_service"] === null || $data[0]["code_service"] === '' ? "COM" : $data[0]["code_service"];

                if (empty($data)) {
                    $message = "Matériel déjà vendu ou L'information saisie n'est pas correcte.";

                    $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
                } else {
                    //recuperation du materiel dan sl abase de donner sqlserver
                    $materiel = $this->getEntityManager()->getRepository(Badm::class)->findOneBy(['idMateriel' => $data[0]['num_matricule'], 'codeSociete' => $codeSociete], ['numBadm' => 'DESC']);

                    // si le materiel n'est pas encore dans la base de donnée on donne la valeur 0 pour l'idType de mouvementMateriel
                    $idTypeMouvementMateriel = $materiel === null ? 0 : $materiel->getTypeMouvement()->getId();

                    //condition de blocage
                    $conditionTypeMouvStatut = $idTypeMouvement === $idTypeMouvementMateriel && in_array($materiel->getStatutDemande()->getId(), [15, 16, 21, 46, 23, 25, 29, 30]);
                    $conditionEntreeParc = $idTypeMouvement === 1 && $data[0]['code_affect'] !== 'VTE';
                    $conditionChangementAgServ_1 = $idTypeMouvement === 2 && $data[0]['code_affect'] === 'VTE';
                    $conditionChangementAgServ_2 = $idTypeMouvement === 2 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                    $conditionCessionActif = $idTypeMouvement === 4 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                    $conditionMiseAuRebut = $idTypeMouvement === 5 && $data[0]['code_affect'] === 'CAS';

                    // Le couple code Agence - code Service du matériel n'existe pas dans la liste des agences services autorisés de l'app BADM 
                    $agenceServicesAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_BADM, false);
                    $conditionAgenceServiceAutoriser = !isset($agenceServicesAutorises[$codeAgenceMateriel . "-" . $codeServiceMateriel]);
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
                $message = 'Ce matériel est en cours de traitement pour ce type de mouvement ';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } else {

                $badm
                    ->setIdMateriel($data[0]['num_matricule'])
                    ->setNumParc($data[0]['num_parc'])
                    ->setNumSerie($data[0]['num_serie'])
                ;
                if ($conditionAgenceServiceAutoriser) {
                    $message = "Vous n'êtes pas autoriser à consulter ce matériel";

                    $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
                } else {
                    $formData = [
                        'idMateriel' => $badm->getIdMateriel(),
                        'numParc' => $badm->getNumParc(),
                        'numSerie' => $badm->getNumSerie(),
                        'typeMouvemnt' => $badm->getTypeMouvement(),
                        'codeSociete' => $badm->getCodeSociete(),
                    ];
                    //envoie des donner dan la session
                    $this->getSessionService()->set('badmform1Data', $formData);
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
