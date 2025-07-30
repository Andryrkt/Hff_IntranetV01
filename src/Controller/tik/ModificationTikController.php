<?php

namespace App\Controller\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\tik\DemandeSupportInformatiqueType;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class ModificationTikController extends Controller
{
    /**
     * @Route("/tik-modification-edit/{id}", name="tik_modification_edit")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        /** 
         * @var DemandeSupportInformatique $supportInfo entité correspondant à l'id 
         */
        $supportInfo = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);

        // Vérifier si l'utilisateur peut modifier le ticket
        if (!$this->canEdit($supportInfo->getNumeroTicket())) {
            $this->redirectToRoute('liste_tik_index');
        }

        //agence et service
        $agenceRepository = self::$em->getRepository(Agence::class);
        $serviceRepository = self::$em->getRepository(Service::class);
        $agenceEmetteur = $agenceRepository->find($supportInfo->getAgenceEmetteurId())->getCodeAgence() . ' ' . $agenceRepository->find($supportInfo->getAgenceEmetteurId())->getLibelleAgence();
        $serviceEmetteur = $serviceRepository->find($supportInfo->getServiceEmetteurId())->getCodeService() . ' ' . $serviceRepository->find($supportInfo->getServiceEmetteurId())->getLibelleService();
        $statutOuvert = self::$em->getRepository(StatutDemande::class)->find('58');
        $supportInfo
            ->setAgenceEmetteur($agenceEmetteur)
            ->setServiceEmetteur($serviceEmetteur)
            ->setAgence($agenceRepository->find($supportInfo->getAgenceDebiteurId()))
            ->setService($serviceRepository->find($supportInfo->getServiceDebiteurId()))
            ->setIdStatutDemande($statutOuvert)
        ;

        //fichier
        $fichiers = $supportInfo->getFileNames();

        //formulaire
        $form = self::$validator->createBuilder(DemandeSupportInformatiqueType::class, $supportInfo)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //envoi les donnée dans la base de donnée
            self::$em->persist($supportInfo);
            self::$em->flush();

            $this->historiqueStatut($supportInfo, $statutOuvert);

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("liste_tik_index");
        }

        $this->logUserVisit('tik_modification_edit', [
            'id' => $id
        ]); // historisation du page visité par l'utilisateur 

        self::$twig->display('tik/demandeSupportInformatique/edit.html.twig', [
            'fichiers' => $fichiers,
            'form' => $form->createView()
        ]);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut éditer le ticket
     */
    private function canEdit(string $numTik): bool
    {
        $this->verifierSessionUtilisateur();

        $idUtilisateur  = $this->sessionService->get('user_id');

        $utilisateur    = $idUtilisateur !== '-' ? self::$em->getRepository(User::class)->find($idUtilisateur) : null;

        if (is_null($utilisateur)) {
            $this->SessionDestroy();
            $this->redirectToRoute("security_signin");
        }

        $allTik = $utilisateur->getSupportInfoUser();

        foreach ($allTik as $tik) {
            // si le numéro du ticket appartient à l'utilisateur connecté et le statut du ticket est ouvert ou en attente
            if ($numTik === $tik->getNumeroTicket() && ($tik->getIdStatutDemande()->getId() === 58 || $tik->getIdStatutDemande()->getId() === 65)) {
                return true;
                break;
            }
        }

        return false;
    }

    private function historiqueStatut($supportInfo, $statut)
    {
        $tikStatut = new TkiStatutTicketInformatique();
        $tikStatut
            ->setNumeroTicket($supportInfo->getNumeroTicket())
            ->setCodeStatut($statut->getCodeStatut())
            ->setIdStatutDemande($statut)
        ;
        self::$em->persist($tikStatut);
        self::$em->flush();
    }
}
