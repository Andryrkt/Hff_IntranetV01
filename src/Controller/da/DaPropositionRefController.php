<?php

namespace App\Controller\da;

use DateTime;
use App\Model\da\DaModel;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DemandeApproLRCollectionType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaObservationRepository;
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
    private DemandeApproRepository $demandeApproRepository;
    private DaObservation $daObservation;
    private DaObservationRepository $daObservationRepository;


    public function __construct()
    {
        parent::__construct();

        $this->daModel = new DaModel();

        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daObservation = new DaObservation();
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
    }

    /**
     * @Route("/proposition/{id}", name="da_proposition")
     */
    public function propositionDeReference($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $numDa = $this->demandeApproRepository->find($id)->getNumeroDemandeAppro();
        $data = $this->demandeApproRepository->find($id)->getDAL();

        $DapLRCollection = new DemandeApproLRCollection();
        $form = self::$validator->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();

        $this->traitementFormulaire($form, $data, $request, $numDa);

        $observations = $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'DESC']);

        self::$twig->display('da/proposition.html.twig', [
            'data' => $data,
            'id' => $id,
            'form' => $form->createView(),
            'observations' => $observations,
            'numDa' => $numDa,
        ]);
    }

    private function traitementFormulaire($form, $data, Request $request, string $numDa)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();

            if ($request->request->has('enregistrer')) {
                $this->taitementPourBtnEnregistrer($dalrList, $request, $data, $observation, $numDa);
            } elseif ($request->request->has('bonAchat')) {

                $dalrs = [];
                foreach ($dalrList as $demandeApproLR) {
                    $DAL = $this->filtreDal($data, $demandeApproLR);
                    $dalrs[] = $this->ajoutDonnerDaLR($DAL, $demandeApproLR);
                }

                // Convertir les entités en tableau de données
                $dataExel = $this->transformationEnTableauAvecEntet($dalrs);

                //creation du fichier excel
                $date = new DateTime();
                $formattedDate = $date->format('Ymd_His');
                $fileName = $dalrs[0]->getNumeroDemandeAppro() . '_' . $formattedDate . '.xlsx';
                $filePath = $_ENV['BASE_PATH_FICHIER'] . '/da/ba/' . $fileName;
                $this->excelService->createSpreadsheetEnregistrer($dataExel, $filePath);
            }
        }
    }

    private function taitementPourBtnEnregistrer($dalrList, Request $request, $data, string $observation, string $numDa): void
    {
        $refsString = $request->request->get('refs');
        $selectedRefs = $refsString ? explode(',', $refsString) : [];
        $refs = $this->separationNbrPageLigne($selectedRefs);

        if ($dalrList->isEmpty() && empty($refs)) {
            $notification = $this->notification('info', "Aucune modification n'a été effectuée");
        } else {
            $this->enregistrementDb($data, $dalrList);
            $this->insertionObservation($observation, $numDa);
            $notification = $this->notification('success', "Votre demande a été enregistré avec succès");
        }

        if (!empty($refs)) {
            // reset les ligne de la page courante
            $this->resetChoix($refs, $data);

            //modifier la colonne choix
            $this->modifChoix($refs, $data);

            //modification de la table demande_appro_L
            $this->modificationTableDaL($refs, $data);
        }

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }

    private function insertionObservation(string $observation, string $numDa): void
    {
        $daObservation = $this->recupDonnerDaObservation($observation, $numDa);

        self::$em->persist($daObservation);

        self::$em->flush();
    }

    private function recupDonnerDaObservation(string $observation, string $numDa): DaObservation
    {
        return $this->daObservation
            ->setNumDa($numDa)
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
            ->setObservation($observation)
        ;
    }

    private function transformationEnTableauAvecEntet($entities): array
    {
        $data = [];
        $data[] = ['constructeur', 'reference', 'quantité'];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
            ];
        }

        return $data;
    }

    private function modificationTableDaL(array $refs,  $data): void
    {
        $dals = $this->recupDataDaL($refs,  $data);
        $dalrs = $this->recupDataDaLR($refs,  $data);

        for ($i = 0; $i < count($dals); $i++) {
            $dals[$i][0]
                ->setQteDispo($dalrs[$i][0]->getQteDispo())
                ->setArtRefp($dalrs[$i][0]->getArtRefp() == '' ? NULL : $dalrs[$i][0]->getArtRefp())
                ->setArtFams1($dalrs[$i][0]->getArtFams1() == '' ? NULL : $dalrs[$i][0]->getArtFams1())
                ->setArtFams2($dalrs[$i][0]->getArtFams2() == '' ? NULL : $dalrs[$i][0]->getArtFams2())
                ->setArtDesi($dalrs[$i][0]->getArtDesi() == '' ? NULL : $dalrs[$i][0]->getArtDesi())
                ->setCodeFams1($dalrs[$i][0]->getCodeFams1() == '' ? NULL : $dalrs[$i][0]->getCodeFams1())
                ->setCodeFams2($dalrs[$i][0]->getCodeFams2() == '' ? NULL : $dalrs[$i][0]->getCodeFams2())
                ->setEstValidee($dalrs[$i][0]->getEstValidee())
                ->setEstModifier($dalrs[$i][0]->getChoix())
                ->setCatalogue($dalrs[$i][0]->getArtFams1() == NULL && $dalrs[$i][0]->getArtFams2() == NULL ? FALSE : TRUE)
            ;
            self::$em->persist($dals[$i][0]);
        }

        self::$em->flush();
    }


    private function recupDataDaL(array $refs,  $data)
    {
        $dals = [];
        for ($i = 0; $i < count($refs); $i++) {
            $dals[] = $this->demandeApproLRepository->findBy(['numeroLigne' => $refs[$i][0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigne' => 'ASC']);
        }

        return $dals;
    }
    private function recupDataDaLR(array $refs,  $data)
    {
        $dalrs = [];
        for ($i = 0; $i < count($refs); $i++) {
            $dalrs[] = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $refs[$i][0], 'numLigneTableau' => $refs[$i][1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigneDem' => 'ASC']);
        }

        return $dalrs;
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
            ->setCodeFams1($demandeApproLR->getArtFams1() == '' ? NULL : $demandeApproLR->getArtFams1()) // ceci doit toujour avant le setArtFams1
            ->setCodeFams2($demandeApproLR->getArtFams2() == '' ? NULL : $demandeApproLR->getArtFams2()) // ceci doit toujour avant le setArtFams2
            ->setArtFams1($libelleFamille == '' ? NULL : $libelleFamille) // ceci doit toujour après le codeFams1
            ->setArtFams2($libelleSousFamille == '' ? NULL : $libelleSousFamille) // ceci doit toujour après le codeFams2
        ;

        return $demandeApproLR;
    }
}
