<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\badm\BadmForm2Type;
use App\Entity\admin\Application;
use Illuminate\Support\Facades\Request;
use App\Entity\admin\badm\TypeMouvement;
use App\Controller\Traits\FormatageTrait;
use App\Service\genererPdf\GenererPdfBadm;
use App\Controller\Traits\BadmDuplicationTrait;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\badm\BadmModel;

/**
 * @Route("/materiel/mouvement-materiel")
 */
class BadmDupliController extends Controller
{
    use FormatageTrait;
    use BadmDuplicationTrait;

    private $badm;

    public function __construct()
    {
        parent::__construct();
        $this->badm = new BadmModel();
    }

    /**
     * @Route("/duplication/{numBadm}/{id}", name="BadmDupli_dupliBadm")
     */
    public function dupliBadm($numBadm, $id, Request $request)
    {
        $badm = new Badm();

        $dataDb = $this->getEntityManager()->getRepository(Badm::class)->find($id);


        $data = $this->badm->findAll($dataDb->getIdMateriel(), '', '');

        /** INITIALISATION du formulaire 2*/
        $badm = $this->initialisation($badm, $dataDb->getTypeMouvement(), $data, $this->getEntityManager());

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
                $this->notification($message);
            } elseif ($idTypeMouvement === 1 && in_array($idMateriel, $idMateriels)) {
                $message = 'ce matériel est déjà en PARC';
                $this->notification($message);
            } elseif ($idTypeMouvement === 2 && $coditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->notification($message);
            } elseif ($idTypeMouvement === 2 && $conditionAgenceServices) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->notification($message);
            } else {

                $this->ajoutDesDonnnerFormulaire($data, $this->getEntityManager(), $badm, $form, $idTypeMouvement);


                //recuperation des ordres de réparation
                $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);
                $OR = $this->ouiNonOr($orDb);
                $orDb = $this->miseEnformeOrDb($orDb);

                //envoie des pièce jointe dans une dossier et le fusionner
                $this->envoiePieceJoint($form, $badm);

                $generPdfBadm = $this->genereteTabPdf($OR, $data, $badm, $form, $this->getEntityManager(), $idTypeMouvement);

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
                $createPdf->genererPdfBadm($generPdfBadm, $orDb);
                $createPdf->copyInterneToDOCUWARE($badm->getNumBadm(), substr($badm->getAgenceEmetteur(), 0, 2) . substr($badm->getServiceEmetteur(), 0, 3));

                //RECUPERATION de la dernière NumeroDemandeIntervention 
                $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM']);
                $application->setDerniereId($badm->getNumBadm());
                // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
                $this->getEntityManager()->persist($application);
                $this->getEntityManager()->flush();

                $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrer']);
                $this->redirectToRoute("badmListe_AffichageListeBadm");
            }
        }

        $this->logUserVisit('BadmDupli_dupliBadmnumBadm', [
            'id'      => $id,
            'numBadm' => $numBadm,
        ]); // historisation du page visité par l'utilisateur 

        return $this->render(
            'badm/duplication.html.twig',
            [
                'items' => $data,
                'form1Data' => $dataDb->getTypeMouvement(),
                'form' => $form->createView()
            ]
        );
    }
}
