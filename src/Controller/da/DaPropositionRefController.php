<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Route("/proposition/{id}", name="da_proposition")
     */
    public function propositionDeReference($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $data = self::$em->getRepository(DemandeAppro::class)->find($id)->getDAL();

        $DapLRCollection = new DemandeApproLRCollection();
        $form = self::$validator->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dalrList = $form->getData()->getDALR();

            if ($dalrList->isEmpty()) {
                $notification = $this->notification('info', "Aucune modification n'a été effectuée");
            } else {
                foreach ($dalrList as $demandeApproLR) {
                    $DAL = $data->filter(function ($entite) use ($demandeApproLR) {
                        return $entite->getNumeroLigne() === $demandeApproLR->getNumeroLigneDem();
                    })->first();
                    $demandeApproLR = $this->ajoutDonnerDaLR($DAL, $demandeApproLR);
                    self::$em->persist($demandeApproLR);
                }
                self::$em->flush();

                $notification = $this->notification('success', "Votre demande a été enregistré avec succès");
            }

            $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
            $this->redirectToRoute("da_list");
        }

        self::$twig->display('da/proposition.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    private function notification(string $type, string $message): array
    {
        return [
            'type'    => $type,
            'message' => $message,
        ];
    }

    private function ajoutDonnerDaLR($DAL, $demandeApproLR)
    {
        $demandeApproLR
            ->setDemandeApproL($DAL)
            ->setNumeroDemandeAppro($DAL->getNumeroDemandeAppro())
            ->setQteDem($DAL->getQteDem())
            ->setArtConstp($DAL->getArtConstp())
            ->setArtFams1($DAL->getArtFams1())
            ->setArtFams2($DAL->getArtFams2())
        ;

        return $demandeApproLR;
    }
}
