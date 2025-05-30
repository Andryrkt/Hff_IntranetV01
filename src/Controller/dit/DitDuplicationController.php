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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitDuplicationController extends Controller
{
    use DitTrait;
    use FormatageTrait;

    /**
     * @Route("/ditDuplication/{id<\d+>}/{numDit<\w+>}", name="dit_duplication")
     *
     * @return void
     */
    public function Duplication($numDit, $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        //INITIALISATION DU FORMULAIRE
        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);
        $codeEmetteur = explode('-', $dit->getAgenceServiceEmetteur());
        $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeEmetteur[0]]);
        $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => $codeEmetteur[1]]);
        $codeDebiteur = explode('-', $dit->getAgenceServiceDebiteur());
        $ditModel = new DitModel();
        $data = $ditModel->findAll($dit->getIdMateriel(), $dit->getNumParc(), $dit->getNumSerie());
        $dit
            ->setNumParc($data[0]['num_parc'])
            ->setNumSerie($data[0]['num_serie'])
            ->setIdMateriel($data[0]['num_matricule'])
            ->setConstructeur($data[0]['constructeur'])
            ->setModele($data[0]['modele'])
            ->setDesignation($data[0]['designation'])
            ->setCasier($data[0]['casier_emetteur'])
            ->setKm($data[0]['km'])
            ->setHeure($data[0]['heure'])
        ;

        $demandeInterventions = new DemandeIntervention();

        $demandeInterventions
            ->setAgenceEmetteur($agenceEmetteur->getCodeAgence() . ' ' . $agenceEmetteur->getLibelleAgence())
            ->setServiceEmetteur($serviceEmetteur->getCodeService() . ' ' . $serviceEmetteur->getLibelleService())
            ->setAgence(self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeDebiteur[0]]))
            ->setService(self::$em->getRepository(Service::class)->findOneBy(['codeService' => $codeDebiteur[1]]))
            ->setTypeDocument($dit->getTypeDocument())
            ->setCodeSociete($dit->getCodeSociete())
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




        $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeInterventions)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $dit = $form->getData();
            $dits = $this->infoEntrerManuel($dit, self::$em, $user);

            //envoie des pièce jointe dans une dossier
            $this->envoiePieceJoint($form, $dits, $this->fusionPdf);

            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);

            $application->setDerniereId($dits->getNumeroDemandeIntervention());

            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);


            //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
            $insertDemandeInterventions = $this->insertDemandeIntervention($dits, $demandeInterventions, self::$em);

            self::$em->persist($insertDemandeInterventions);
            self::$em->flush();

            /**CREATION DU PDF*/
            //recupération des donners dans le formulaire
            $pdfDemandeInterventions = $this->pdfDemandeIntervention($dits, $demandeInterventions);
            //récupération des historique de materiel (informix)
            $historiqueMateriel = $this->historiqueInterventionMateriel($dits);
            //genere le PDF
            $genererPdfDit = new GenererPdfDit();
            $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);


            //ENVOYER le PDF DANS DOXCUWARE
            if ($dits->getAgence()->getCodeAgence() === "91" || $dits->getAgence()->getCodeAgence() === "92" || $dits->getAgence()->getCodeAgence() === "50") {
                $genererPdfDit->copyInterneToDOCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(), str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));
            }

            $this->redirectToRoute("dit_index");
        }

        $this->logUserVisit('dit_duplication', [
            'id'     => $id,
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        self::$twig->display('dit/duplication.html.twig', [
            'form' => $form->createView(),
            'dit' => $dit,
        ]);
    }
}
