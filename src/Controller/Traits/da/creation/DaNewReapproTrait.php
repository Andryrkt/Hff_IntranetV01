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
     * @param array $agenceServiceIps
     * 
     * @return DemandeAppro la demande appro initialisée
     */
    private function initialisationDemandeApproReappro(array $agenceServiceIps): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

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
        ;

        return $demandeAppro;
    }

    private function generateDemandApproLinesFromReappros(DemandeAppro $demandeAppro, array $agenceServiceIps)
    {
        $existingDals = [];
        $newDals = [];
        $lineNumber = 0;

        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $articlesReappro = $this->daArticleReapproRepository->findBy([
            'codeAgence' => $agence->getCodeAgence(),
            'codeService' => $service->getCodeService(),
        ]);

        // Indexation des DAL existantes
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
                ->setNumeroDemandeAppro($numDa)
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
