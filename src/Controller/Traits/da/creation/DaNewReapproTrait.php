<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DaArticleReappro;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Repository\da\DaArticleReapproRepository;
use App\Traits\JoursOuvrablesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

trait DaNewReapproTrait
{
    use DaNewTrait, JoursOuvrablesTrait;

    //=====================================================================================
    private DaArticleReapproRepository $daArticleReapproRepository;

    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewReapproTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->daArticleReapproRepository = $em->getRepository(DaArticleReappro::class);
    }
    //=====================================================================================

    /** 
     * Fonction pour initialiser une demande appro réappro
     * 
     * @return DemandeAppro la demande appro initialisée
     */
    private function initialisationDemandeApproReappro(): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];
        $codeAgence = $agence->getCodeAgence();
        $codeService = $service->getCodeService();
        $numDa = $this->autoDecrement('DAP');

        $demandeAppro
            ->setDaTypeId(DemandeAppro::TYPE_DA_REAPPRO)
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur("$codeAgence-$codeService")
            ->setAgenceServiceEmetteur("$codeAgence-$codeService")
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($numDa)
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
            ->setDAL($this->buildDalFromReappros($numDa, $codeAgence, $codeService))
        ;

        return $demandeAppro;
    }

    private function buildDalFromReappros(string $numDa, string $codeAgence, string $codeService): ArrayCollection
    {
        $dals = [];
        $line = 0;
        /** @var DaArticleReappro[] $articleReappros */
        $articleReappros = $this->daArticleReapproRepository->findBy(['codeAgence' => $codeAgence, 'codeService' => $codeService,]);
        foreach ($articleReappros as $articleReappro) {
            $dals[] = (new DemandeApproL)
                ->setNumeroDemandeAppro($numDa)
                ->setNumeroLigne(++$line)
                ->setArtConstp($articleReappro->getArtConstp())
                ->setArtRefp($articleReappro->getArtRefp())
                ->setArtDesi($articleReappro->getArtDesi())
                ->setPrixUnitaire($articleReappro->getArtPU())
                ->setQteValAppro($articleReappro->getQteValide());
        }
        return new ArrayCollection($dals);
    }

    /** 
     * Fonction pour retourner le nom du bouton cliqué
     *  - enregistrerBrouillon
     *  - soumissionAppro
     */
    private function getButtonName(Request $request): string
    {
        if ($request->request->has('enregistrerBrouillon')) {
            return 'enregistrerBrouillon';
        } elseif ($request->request->has('soumissionAppro')) {
            return 'soumissionAppro';
        } else {
            return '';
        }
    }
}
