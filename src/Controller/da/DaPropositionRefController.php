<?php

namespace App\Controller\da;

use App\Model\da\DaModel;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefController extends Controller
{
    private DaModel $daModel;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DemandeApproLRepository $demandeApproLRepository;


    public function __construct()
    {
        parent::__construct();

        $this->daModel = new DaModel();
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
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
            'id' => $id,
            'form' => $form->createView()
        ]);
    }

    private function traitementFormulaire($form, $data, $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($request->request->has('enregistrer')) {
                // ✅ Récupérer les valeurs des champs caché
                $dalrList = $form->getData()->getDALR();

                dd($dalrList);

                $refsString = $request->request->get('refs');
                $selectedRefs = $refsString ? explode(',', $refsString) : [];
                $refs = $this->separationNbrPageLigne($selectedRefs);

                if ($dalrList->isEmpty() && empty($refs)) {
                    $notification = $this->notification('info', "Aucune modification n'a été effectuée");
                } else {
                    $this->enregistrementDb($data, $dalrList);
                    $notification = $this->notification('success', "Votre demande a été enregistré avec succès");
                }

                if (!empty($refs)) {
                    // reset les ligne de la page courante
                    $this->resetChoix($refs, $data);

                    //modifier la colonne choix
                    $this->modifChoix($refs, $data);

                    //modification de la table demande_appro_L
                    // $this->modificationTableDaL($refs, $data);
                }

                $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
                $this->redirectToRoute("da_list");
            } elseif ($request->request->has('bonAchat')) {

                // ✅ Récupérer les valeurs des champs caché
                $dalrList = $form->getData()->getDALR();

                dd($dalrList);
            }
        }
    }

    private function modificationTableDaL(array $refs,  $data): void
    {
        for ($i = 0; $i < count($refs); $i++) {
            $dals = $this->demandeApproLRepository->findBy(['numeroLigne' => $refs[$i][0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigne' => 'ASC']);
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $refs[$i][0], 'numLigneTableau' => $refs[$i][1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigneDem' => 'ASC']);

            for ($i = 0; $i < count($dalrs); $i++) {

                $dals[$i]
                    ->setQteDispo($dalrs[$i]->getQteDispo())
                    ->setArtRefp($dalrs[$i]->getArtRefp() == '' ? NULL : $dalrs[$i]->getArtRefp())
                    ->setArtFams1($dalrs[$i]->getArtFams1() == '' ? NULL : $dalrs[$i]->getArtFams1())
                    ->setArtFams2($dalrs[$i]->getArtFams2() == '' ? NULL : $dalrs[$i]->getArtFams2())
                    ->setArtDesi($dalrs[$i]->getArtDesi() == '' ? NULL : $dalrs[$i]->getArtDesi())
                    // ->setCodeFams1($dalrs[$i]->getCodeFams1() == '' ? NULL : $dalrs[$i]->getCodeFams1())
                    // ->setCodeFams2($dalrs[$i]->getCodeFams2() == '' ? NULL : $dalrs[$i]->getCodeFams2())
                    ->setEstValidee($dalrs[$i]->getEstValidee())
                    ->setEstModifier($dalrs[$i]->getChoix())
                    // ->setCatalogue($dalrs[$i]->getArtFams1() == NULL && $dalrs[$i]->getArtFams2() == NULL ? FALSE : TRUE)
                ;

                self::$em->persist($dals[$i]);
            }
        }
        die('fin');
        self::$em->flush();
    }

    private function resetChoix(array $refs, $data): void
    {
        $dalrsAll = $this->recupEntitePageCourante($refs, $data);
        $dalrsAll = $this->resetEntite($dalrsAll);
        $this->resetBd($dalrsAll);
    }

    private function modifChoix(array $refs, $data): void
    {
        $dalrs = $this->recupEntiteAModifier($refs, $data);
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

    private function recupEntitePageCourante(array $refs, $data): array
    {
        foreach ($refs as $ref) {
            $dalrsAll = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
        }

        return $dalrsAll;
    }

    private function resetEntite(array $dalrsAll): array
    {
        foreach ($dalrsAll as  $dalr) {
            $dalr->setChoix(false);
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

    private function recupEntiteAModifier(array $refs, $data): array
    {
        foreach ($refs as $ref) {
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0], 'numLigneTableau' => $ref[1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
        }

        return $dalrs;
    }

    private function modifEntite(array $dalrs): array
    {
        foreach ($dalrs as  $dalr) {
            $dalr->setChoix(true);
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
            ->setArtFams1($libelleFamille == '' ? NULL : $libelleFamille)
            ->setArtFams2($libelleSousFamille == '' ? NULL : $libelleSousFamille)
            // ->setCodeFams1($demandeApproLR->getArtFams1() == '' ? NULL : $demandeApproLR->getArtFams1())
            // ->setCodeFams2($demandeApproLR->getArtFams2() == '' ? NULL : $demandeApproLR->getArtFams2())
        ;

        return $demandeApproLR;
    }
}
