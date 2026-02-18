<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\badm\BadmForm2Type;
use App\Entity\admin\Application;
use App\Entity\admin\badm\TypeMouvement;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\BadmsForm2Trait;
use App\Service\genererPdf\GenererPdfBadm;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationBADMService;
use App\Service\FusionPdf;
use App\Model\badm\BadmModel;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmsForm2Controller extends Controller
{
    use FormatageTrait;
    use BadmsForm2Trait;
    use AutorisationTrait;

    private $historiqueOperation;
    private $fusionPdf;
    private $badm;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationBADMService($this->getEntityManager());
        $this->fusionPdf = new FusionPdf();
        $this->badm = new BadmModel();
    }

    /**
     * @Route("/badm-form2", name="badms_newForm2")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_BADM);
        /** FIN AUtorisation acées */

        $badm = new Badm();

        //recupération des donnée qui vient du formulaire 1
        $form1Data = $this->getSessionService()->get('badmform1Data', []);
        // recuperation des information du matériel entrer par l'utilisateur dans le formulaire 1
        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);

        /** INITIALISATION du formulaire 2*/
        $badm = $this->initialisation($badm, $form1Data, $data, $this->getEntityManager());

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(BadmForm2Type::class, $badm)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $badm->setTypeMouvement($this->getEntityManager()->getRepository(TypeMouvement::class)->find($badm->getTypeMouvement()));
            //recuperatin de l'id du type de mouvemnet choisi par l'utilisateur dans le formulaire 1
            $idTypeMouvement = $badm->getTypeMouvement()->getId();


            //condition
            $coditionAgenceService = $badm->getAgenceEmetteur() === $badm->getAgence() && $badm->getServiceEmetteur() === $badm->getService();
            $conditionAgenceServices = $badm->getAgence() === null && $badm->getService() === null || $coditionAgenceService;
            $conditionVide = $badm->getAgence() === null && $badm->getService() === null && $badm->getCasierDestinataire() === null && $badm->getDateMiseLocation() === null;
            $idMateriel = (int)$data[0]['num_matricule'];
            $idMateriels = $this->getEntityManager()->getRepository(Badm::class)->findIdMateriel();



            if (($idTypeMouvement === 1 || $idTypeMouvement === 2) && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            }
            // elseif ($idTypeMouvement === 1 && in_array($idMateriel, $idMateriels)) {
            //     $message = 'ce matériel est déjà en PARC';

            //     $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            // } 
            elseif ($idTypeMouvement === 2 && $coditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($idTypeMouvement === 2 && $conditionAgenceServices) {
                $message = 'le choix du type devrait être Changement de Casier';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } else {

                $this->ajoutDesDonnnerFormulaire($data, $this->getEntityManager(), $badm, $form, $idTypeMouvement);

                //RECUPERATION de la dernière NumeroDemandeIntervention 
                $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM']);
                $application->setDerniereId($badm->getNumBadm());
                // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
                $this->getEntityManager()->persist($application);
                $this->getEntityManager()->flush();

                //recuperation des ordres de réparation
                $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);
                $OR = $this->ouiNonOr($orDb);
                $orDb = $this->miseEnformeOrDb($orDb);


                $idAgenceEmetteur = $this->getEntityManager()->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($badm->getAgenceEmetteur(), 0, 2)]);
                $idServiceEmetteur = $this->getEntityManager()->getRepository(Service::class)->findOneBy(['codeService' => substr($badm->getServiceEmetteur(), 0, 3)]);

                $badm
                    ->setAgenceEmetteurId($idAgenceEmetteur)
                    ->setServiceEmetteurId($idServiceEmetteur)
                    ->setAgenceDebiteurId($badm->getAgence())
                    ->setServiceDebiteurId($badm->getService())
                ;

                //ENVOIE DANS LE BASE DE DONNEE
                $this->getEntityManager()->persist($badm);
                $this->getEntityManager()->flush();

                /** CREATION PDF */
                $createPdf = new GenererPdfBadm();
                $generPdfBadm = $this->genereteTabPdf($OR, $data, $badm, $form, $this->getEntityManager(), $idTypeMouvement);
                $createPdf->genererPdfBadm($generPdfBadm, $orDb);
                //envoie des pièce jointe dans une dossier et le fusionner
                $this->envoiePieceJoint($form, $badm, $this->fusionPdf);
                //copy du fichier fusionner dan sdocuware
                $createPdf->copyInterneToDOCUWARE($badm->getNumBadm(), substr($badm->getAgenceEmetteur(), 0, 2) . substr($badm->getServiceEmetteur(), 0, 3));

                $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrer', $badm->getNumBadm(), 'badmListe_AffichageListeBadm', true);
            }
        }

        $this->logUserVisit('badms_newForm2'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/secondForm.html.twig',
            [
                'items' => $data,
                'form1Data' => $form1Data,
                'form' => $form->createView()
            ]
        );
    }
}
