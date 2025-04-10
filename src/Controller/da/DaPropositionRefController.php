<?php

namespace App\Controller\da;

use App\Model\da\DaModel;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproLR;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefController extends Controller
{
    private DaModel $daModel;
    private DemandeApproLRRepository $demandeApproLRRepository;
    public function __construct()
    {
        parent::__construct();

        $this->daModel = new DaModel();
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/proposition/{id}", name="da_proposition", methods={"GET","POST"})
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
            'id' => $id,
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire($form, $data, $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();


            $refsString = $request->request->get('refs');
            $selectedRefs = $refsString ? explode(',', $refsString) : [];
            $refs = $this->separationNbrPageLigne($selectedRefs);

            if (!empty($refs)) {
                // reset les ligne de la page courante
                $this->resetEstValide($refs);

                //modifier la colonne estvalidee 
                $this->modifEstValide($refs);
            }


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

    private function resetEstValide(array $refs): void
    {
        $dalrsAll = $this->recupEntitePageCourante($refs);
        $dalrsAll = $this->resetEntite($dalrsAll);
        $this->resetBd($dalrsAll);
    }

    private function modifEstValide(array $refs): void
    {
        $dalrs = $this->recupEntiteAModifier($refs);
        $dalrs = $this->modifEntite($dalrs);
        $this->modificationBd($dalrs);
    }
    private function separationNbrPageLigne(array $selectedRefs): array
    {
        $refs = [];
        foreach ($selectedRefs as  $value) {
            $refs[] = explode('-', $value);
        }
        return $refs;
    }

    private function recupEntitePageCourante(array $refs): array
    {
        foreach ($refs as $ref) {
            $dalrsAll = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0]]);
        }

        return $dalrsAll;
    }
    private function resetEntite(array $dalrsAll): array
    {
        foreach ($dalrsAll as  $dalr) {
            $dalr->setEstValidee(false);
        }
        return $dalrsAll;
    }

    private function resetBd(array $dalrsAll): void
    {
        foreach ($dalrsAll as $dalr) {
            self::$em->persist($dalr);
        }
        self::$em->flush();
    }

    private function recupEntiteAModifier(array $refs): array
    {
        foreach ($refs as $ref) {
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0], 'numLigneTableau' => $ref[1]]);
        }

        return $dalrs;
    }

    private function modifEntite(array $dalrs): array
    {
        foreach ($dalrs as  $dalr) {
            $dalr->setEstValidee(true);
        }
        return $dalrs;
    }

    private function modificationBd(array $dalrs): void
    {
        foreach ($dalrs as $dalr) {
            self::$em->persist($dalr);
        }
        self::$em->flush();
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
