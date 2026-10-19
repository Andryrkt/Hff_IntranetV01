<?php

namespace App\Service\da;

use DateTime;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\atelierRealise\AtelierRealise;
use App\Entity\da\DaArticleReappro;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproParent;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Service\UserData\UserDataService;
use App\Traits\JoursOuvrablesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class DaCreationService
{
    use JoursOuvrablesTrait;

    private EntityManagerInterface $em;
    private UserDataService $userDataService;

    public function __construct(EntityManagerInterface $em, UserDataService $userDataService)
    {
        $this->em = $em;
        $this->userDataService = $userDataService;
    }

    /**
     * Agence et service IPS par défaut de l'utilisateur connecté (null, null en cas d'échec).
     *
     * @return array{agenceIps:?Agence,serviceIps:?Service}
     */
    private function agenceServiceIpsObjet(): array
    {
        try {
            if (!$this->userDataService->getUserInfo()) throw new \Exception("User info not found in session");

            $codeAgence = $this->userDataService->getCodeAgenceUser();
            $agenceIps = $this->em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgence]);
            if (!$agenceIps) throw new \Exception("Agence not found with code $codeAgence");

            $codeService = $this->userDataService->getCodeServiceUser();
            $serviceIps = $this->em->getRepository(Service::class)->findOneBy(['codeService' => $codeService]);
            if (!$serviceIps) throw new \Exception("Service not found with code $codeService");

            return ['agenceIps' => $agenceIps, 'serviceIps' => $serviceIps];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['agenceIps' => null, 'serviceIps' => null];
        }
    }

    /** Initialise une demande appro achat (parent) */
    public function initialisationDemandeApproAchat(string $codeSociete): DemandeApproParent
    {
        $demandeApproParent = new DemandeApproParent();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $demandeApproParent
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setAgenceServiceEmetteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setCodeSociete($codeSociete)
            ->setUser($this->userDataService->getUser())
            ->setDemandeur($this->userDataService->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // 5 jours ouvrables après aujourd'hui
        ;

        return $demandeApproParent;
    }

    /** Initialise une demande appro avec DIT */
    public function initialisationDemandeApproAvecDit(DemandeIntervention $dit): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $agenceServiceEmetteur = $this->agenceServiceIpsObjet();
        $agenceEmetteur = $agenceServiceEmetteur['agenceIps'];
        $serviceEmetteur = $agenceServiceEmetteur['serviceIps'];

        $agenceServiceDebiteur = $this->handleAgenceEtServiceDebiteur($dit);
        $agenceDebiteur = $agenceServiceDebiteur['agence'];
        $serviceDebiteur = $agenceServiceDebiteur['service'];

        $demandeAppro
            ->setDaTypeId(DemandeAppro::TYPE_DA_AVEC_DIT)
            ->setNiveauUrgence($dit->getIdNiveauUrgence()->getDescription())
            ->setObjetDal($dit->getObjetDemande())
            ->setDetailDal($dit->getDetailDemande())
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceEmetteur($agenceEmetteur)
            ->setServiceEmetteur($serviceEmetteur)
            ->setAgenceServiceEmetteur("{$agenceEmetteur->getCodeAgence()}-{$serviceEmetteur->getCodeService()}")
            ->setAgenceDebiteur($agenceDebiteur)
            ->setServiceDebiteur($serviceDebiteur)
            ->setAgenceServiceDebiteur("{$agenceDebiteur->getCodeAgence()}-{$serviceDebiteur->getCodeService()}")
            ->setUser($this->userDataService->getUser())
            ->setDemandeur($this->userDataService->getUser()->getNomUtilisateur())
        ;

        return $demandeAppro;
    }

    /** Initialise une demande appro réappro mensuel */
    public function initialisationDemandeApproReapproMensuel(string $codeSociete): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $codeAgence = $agence->getCodeAgence();
        $codeService = $service->getCodeService();

        $demandeAppro
            ->setDaTypeId(DemandeAppro::TYPE_DA_REAPPRO_MENSUEL)
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur("$codeAgence-$codeService")
            ->setAgenceServiceEmetteur("$codeAgence-$codeService")
            ->setCodeSociete($codeSociete)
            ->setUser($this->userDataService->getUser())
            ->setDemandeur($this->userDataService->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // 5 jours ouvrables après aujourd'hui
        ;

        return $demandeAppro;
    }

    /** Reconstruit les DAL d'une DA réappro à partir des articles réappro de son agence/service */
    public function generateDemandApproLinesFromReappros(DemandeAppro $demandeAppro): void
    {
        $existingDals = [];
        $newDals      = [];
        $lineNumber   = 0;

        $agence       = $demandeAppro->getAgenceEmetteur();
        $service      = $demandeAppro->getServiceEmetteur();

        $articlesReappro = $this->em->getRepository(DaArticleReappro::class)->findBy([
            'codeAgence'  => $agence->getCodeAgence(),
            'codeService' => $service->getCodeService(),
        ]);

        // Indexation des DAL existantes
        /** @var DemandeApproL $dal */
        foreach ($demandeAppro->getDAL() as $dal) {
            $key = md5("{$dal->getArtConstp()}|{$dal->getArtRefp()}|{$dal->getArtDesi()}");
            $existingDals[$key] = $dal;
        }

        // Construction ou réutilisation des DAL
        foreach ($articlesReappro as $article) {
            $key = md5("{$article->getArtConstp()}|{$article->getArtRefp()}|{$article->getArtDesi()}");

            if (isset($existingDals[$key])) {
                $newDals[] = $existingDals[$key]->setQteValAppro($article->getQteValide());
                continue;
            }

            $newDals[] = (new DemandeApproL())
                ->setNumeroFournisseur('-')
                ->setNomFournisseur('-')
                ->setCommentaire('-')
                ->setNumeroLigne(++$lineNumber)
                ->setArtConstp($article->getArtConstp())
                ->setArtRefp($article->getArtRefp())
                ->setArtDesi($article->getArtDesi())
                ->setPrixUnitaire($article->getArtPU())
                ->setQteValAppro($article->getQteValide());
        }

        $demandeAppro->setDAL(new ArrayCollection($newDals));
    }

    /**
     * Date de fin souhaitée d'une DA avec DIT (jours ouvrables selon l'urgence ou la date planning de l'OR).
     */
    public function dateLivraisonPrevueDA(string $numDit, string $niveauUrgence): DateTime
    {
        $jours = ['P0' => 5, 'P1' => 7, 'P2' => 10, 'P3' => 15, 'P4' => 15];
        [$numOr,] = $this->em->getRepository(DitOrsSoumisAValidation::class)->getNumeroEtStatutOr($numDit);
        $datePlanningOR = $this->getDatePlannigOr($numOr);
        if ($datePlanningOR) { // DIT avec OR plannifiée
            $dateDans12JoursOuvrables = $this->ajouterJoursOuvrables(12);
            if ($datePlanningOR < $dateDans12JoursOuvrables) {
                return $this->ajouterJoursOuvrables(5);
            } else {
                return $this->retirerJoursOuvrables(7, $datePlanningOR); // on retire 7 jours ouvrables à la date planning or
            }
        } else { // DIT sans OR ou avec OR non plannifiée
            return $this->ajouterJoursOuvrables($jours[$niveauUrgence] ?? $jours['P4']);
        }
    }

    private function getDatePlannigOr(?string $numOr): ?DateTime
    {
        if (!is_null($numOr)) {
            $magasinListeOrLivrerModel = new MagasinListeOrLivrerModel();
            $data = $magasinListeOrLivrerModel->getDatePlanningPourDa($numOr);

            if (!empty($data) && !empty($data[0]['dateplanning'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d', $data[0]['dateplanning']);
            }
        }

        return $dateObj ?? null;
    }

    /**
     * Agence et service débiteur de la DA d'après la DIT
     *
     * @return array{agence:Agence,service:Service}
     * @throws \Exception
     */
    private function handleAgenceEtServiceDebiteur(DemandeIntervention $dit): array
    {
        $agence = $service = null;
        if ($dit->getInternetExterne() === "INTERNE") {
            $agence  = $dit->getAgenceDebiteurId();
            $service = $dit->getServiceDebiteurId();
        } elseif ($dit->getInternetExterne() === "EXTERNE") {
            $atelierRealise = $this->em->getRepository(AtelierRealise::class)->findWithAgenceAndServiceByCode($dit->getReparationRealise());

            if ($atelierRealise) {
                $agence  = $atelierRealise->getAgence();
                $service = $atelierRealise->getService();
            } else {
                throw new \Exception("Atelier non trouvé pour le code: {$dit->getReparationRealise()}");
            }
        } else {
            throw new \Exception("Type de DIT non reconnu: elle n'est ni 'INTERNE' ni 'EXTERNE'");
        }

        return ['agence' => $agence, 'service' => $service];
    }
}
