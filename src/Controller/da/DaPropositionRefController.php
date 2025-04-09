<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use App\Model\da\DaModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefController extends Controller
{
    private DaModel $daModel;
    public function __construct()
    {
        parent::__construct();

        $this->daModel = new DaModel();
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

        $this->traitementFormulaire($form, $data, $request);

        self::$twig->display('da/proposition.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire($form, $data, $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dalrList = $form->getData()->getDALR();
            // dd($dalrList);
            if ($dalrList->isEmpty()) {
                $notification = $this->notification('info', "Aucune modification n'a été effectuée");
            } else {
                $this->enregistrementDb($data, $dalrList);
                $notification = $this->notification('success', "Votre demande a été enregistré avec succès");
            }

            $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
            $this->redirectToRoute("da_list");
        }
    }

    private function notification(string $type, string $message): array
    {
        return [
            'type'    => $type,
            'message' => $message,
        ];
    }

    private function enregistrementDb($data, $dalrList)
    {
        foreach ($dalrList as $demandeApproLR) {
            $DAL = $this->filtreDal($data, $demandeApproLR);
            $demandeApproLR = $this->ajoutDonnerDaLR($DAL, $demandeApproLR);
            self::$em->persist($demandeApproLR);
        }
        self::$em->flush();
    }

    private function filtreDal($data, $demandeApproLR): ?object
    {
        return  $data->filter(function ($entite) use ($demandeApproLR) {
            return $entite->getNumeroLigne() === $demandeApproLR->getNumeroLigneDem();
        })->first();
    }

    private function ajoutDonnerDaLR($DAL, $demandeApproLR)
    {
        $libelleSousFamille = $this->daModel->getLibelleSousFamille($demandeApproLR->getArtFams2(), $demandeApproLR->getArtFams1()); // changement de code sous famille en libelle sous famille
        $libelleFamille = $this->daModel->getLibelleFamille($demandeApproLR->getArtFams1()); // changement de code famille en libelle famille

        $demandeApproLR
            ->setDemandeApproL($DAL)
            ->setNumeroDemandeAppro($DAL->getNumeroDemandeAppro())
            ->setQteDem($DAL->getQteDem())
            ->setArtConstp($DAL->getArtConstp())
            ->setArtFams1($libelleFamille)
            ->setArtFams2($libelleSousFamille)
        ;

        return $demandeApproLR;
    }
}
