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
use App\Service\historiqueOperation\HistoriqueOperationBADMService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsForm2Controller extends Controller
{
    use FormatageTrait;
    use BadmsForm2Trait;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationBADMService;
    }

    /**
     * @Route("/badm-form2", name="badms_newForm2")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $badm = new Badm();

        //recupération des donnée qui vient du formulaire 1
        $form1Data = $this->sessionService->get('badmform1Data', []);
        // recuperation des information du matériel entrer par l'utilisateur dans le formulaire 1
        $data = $this->badm->findAll($form1Data['idMateriel'],  $form1Data['numParc'], $form1Data['numSerie']);

        /** INITIALISATION du formulaire 2*/
        $badm = $this->initialisation($badm, $form1Data, $data, self::$em);

        //création du formulaire
        $form = self::$validator->createBuilder(BadmForm2Type::class, $badm)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $badm->setTypeMouvement(self::$em->getRepository(TypeMouvement::class)->find($badm->getTypeMouvement()));
            //recuperatin de l'id du type de mouvemnet choisi par l'utilisateur dans le formulaire 1
            $idTypeMouvement = $badm->getTypeMouvement()->getId();


            //condition
            $coditionAgenceService = $badm->getAgenceEmetteur() === $badm->getAgence() && $badm->getServiceEmetteur() === $badm->getService();
            $conditionAgenceServices = $badm->getAgence() === null && $badm->getService() === null || $coditionAgenceService;
            $conditionVide = $badm->getAgence() === null && $badm->getService() === null && $badm->getCasierDestinataire() === null && $badm->getDateMiseLocation() === null;
            $idMateriel = (int)$data[0]['num_matricule'];
            $idMateriels = self::$em->getRepository(Badm::class)->findIdMateriel();


            if (($idTypeMouvement === 1 || $idTypeMouvement === 2) && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($idTypeMouvement === 1 && in_array($idMateriel, $idMateriels)) {
                $message = 'ce matériel est déjà en PARC';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($idTypeMouvement === 2 && $coditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } elseif ($idTypeMouvement === 2 && $conditionAgenceServices) {
                $message = 'le choix du type devrait être Changement de Casier';

                $this->historiqueOperation->sendNotificationCreation($message, '-', 'badms_newForm1');
            } else {

                $this->ajoutDesDonnnerFormulaire($data, self::$em, $badm, $form, $idTypeMouvement);

                //RECUPERATION de la dernière NumeroDemandeIntervention 
                $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM']);
                $application->setDerniereId($badm->getNumBadm());
                // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
                self::$em->persist($application);
                self::$em->flush();

                //recuperation des ordres de réparation
                $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);
                $OR = $this->ouiNonOr($orDb);
                $orDb = $this->miseEnformeOrDb($orDb);


                $idAgenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($badm->getAgenceEmetteur(), 0, 2)]);
                $idServiceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => substr($badm->getServiceEmetteur(), 0, 3)]);

                $badm
                    ->setAgenceEmetteurId($idAgenceEmetteur)
                    ->setServiceEmetteurId($idServiceEmetteur)
                    ->setAgenceDebiteurId($badm->getAgence())
                    ->setServiceDebiteurId($badm->getService())
                ;

                //ENVOIE DANS LE BASE DE DONNEE
                self::$em->persist($badm);
                self::$em->flush();

                /** CREATION PDF */
                $createPdf = new GenererPdfBadm();
                $generPdfBadm = $this->genereteTabPdf($OR, $data, $badm, $form, self::$em, $idTypeMouvement);
                $createPdf->genererPdfBadm($generPdfBadm, $orDb);
                //envoie des pièce jointe dans une dossier et le fusionner
                $this->envoiePieceJoint($form, $badm, $this->fusionPdf);
                //copy du fichier fusionner dan sdocuware
                $createPdf->copyInterneToDOXCUWARE($badm->getNumBadm(), substr($badm->getAgenceEmetteur(), 0, 2) . substr($badm->getServiceEmetteur(), 0, 3));

                $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrer', $badm->getNumBadm(), 'badmListe_AffichageListeBadm', true);
            }
        }

        $this->logUserVisit('badms_newForm2'); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'badm/secondForm.html.twig',
            [
                'items' => $data,
                'form1Data' => $form1Data,
                'form' => $form->createView()
            ]
        );
    }
}
