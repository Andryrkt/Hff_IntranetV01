<?php

namespace App\Controller\Traits\da;

use DateTime;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Model\da\DaModel;

trait StatutBcTrait
{
    private DaModel $daModel;

    /**
     * Initialise le trait StatutBcTrait
     */
    public function initStatutBcTrait()
    {
        $this->daModel = new DaModel();
    }

    private function statutBc(?string $ref, string $numDit, string $numDa, ?string $designation, ?string $numeroOr): ?string
    {
        $em = self::getEntity();

        $DaAfficher = $this->getDaAfficher($numDa, $numDit, $ref, $designation);

        if ($DaAfficher == null) {
            return '';
        };
        $statutBc = $DaAfficher->getStatutCde();
        $achatDirect = $DaAfficher->getAchatDirect();

        if ($numeroOr == null && !$achatDirect) {
            return $statutBc;
        }

        $infoDaDirect = $this->daModel->getInfoDaDirect($numDa, $ref, $designation);
        $situationCde = $this->daModel->getSituationCde($ref, $numDit, $numDa, $designation, $numeroOr);

        $statutDaIntanert = [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE
        ];
        $statutDa = $this->demandeApproRepository->getStatutDa($numDa);
        if (in_array($statutDa, $statutDaIntanert)) {
            return '';
        }

        $numcde = $this->numeroCde($infoDaDirect, $situationCde, $achatDirect);
        $bcExiste = $this->daSoumissionBcRepository->bcExists($numcde);
        $statutSoumissionBc = $em->getRepository(DaSoumissionBc::class)->getStatut($numcde);

        $qte = $this->daModel->getEvolutionQte($numDit, $numDa, $ref, $designation, $numeroOr);
        [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre] = $this->evaluerQuantites($qte,  $infoDaDirect, $achatDirect);

        if (! $achatDirect) {
            $this->updateInfoOR($numDit, $DaAfficher);
        }
        $this->updateSituationCdeDansDaAfficher($situationCde, $DaAfficher, $numcde, $infoDaDirect, $achatDirect);
        $this->updateQteCdeDansDaAfficher($qte, $DaAfficher, $infoDaDirect, $achatDirect);

        $statutBcDw = [
            DaSoumissionBc::STATUT_SOUMISSION,
            DaSoumissionBc::STATUT_A_VALIDER_DA,
            DaSoumissionBc::STATUT_VALIDE,
            DaSoumissionBc::STATUT_CLOTURE,
            DaSoumissionBc::STATUT_REFUSE
        ];

        if ($this->doitGenererBc($situationCde, $statutDa, $DaAfficher->getStatutOr(), $infoDaDirect, $achatDirect)) {
            return 'A générer';
        }

        if (!$this->aSituationCde($situationCde, $infoDaDirect)) {
            return $statutBc;
        }

        if ($this->doitEditerBc($situationCde, $infoDaDirect, $achatDirect)) {
            return 'A éditer';
        }

        if ($this->doitSoumettreBc($situationCde, $bcExiste, $statutBc, $statutBcDw, $infoDaDirect, $achatDirect)) {
            return 'A soumettre à validation';
        }

        if ($this->doitEnvoyerBc($situationCde, $statutBc, $DaAfficher, $statutSoumissionBc, $infoDaDirect, $achatDirect)) {
            return 'A envoyer au fournisseur';
        }

        if ($partiellementDispo) {
            return 'Partiellement dispo';
        }

        if ($completNonLivrer) {
            return 'Complet non livré';
        }

        if ($tousLivres) {
            return 'Tous livrés';
        }

        if ($partiellementLivre) {
            return 'Partiellement livré';
        }

        if ($DaAfficher->getBcEnvoyerFournisseur()) {
            return 'BC envoyé au fournisseur';
        }

        return $statutSoumissionBc;
    }

    private function numeroCde(array $infoDaDirect, array $situationCde, bool $achatDirect): ?string
    {
        if ($achatDirect) {
            $numCde = array_key_exists(0, $infoDaDirect) ? $infoDaDirect[0]['num_cde'] : '';
        } else {
            $numCde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        }
        return $numCde;
    }

    private function aSituationCde(array $situationCde, array $infoDaDirect): bool
    {
        return array_key_exists(0, $situationCde) || array_key_exists(0, $infoDaDirect);
    }

    private function doitGenererBc(array $situationCde, string $statutDa, ?string $statutOr, array $infoDaDirect, bool $achatDirect): bool
    {
        if ($achatDirect) {
            if (empty($infoDaDirect)) {
                return true;
            }

            // Si le numéro de commande est vide
            $numCdeVide = empty($situationCde[0]['num_cde'] ?? null);

            return $numCdeVide;
        } else {

            $daValide = $statutDa === DemandeAppro::STATUT_VALIDE;
            $orValide = $statutOr === DitOrsSoumisAValidation::STATUT_VALIDE;

            // Si aucune situation de commande n'est présente
            if (empty($situationCde)) {
                return $daValide && $orValide;
            }
            // Si une situation existe mais sans numéro de commande
            $numCdeVide = empty($situationCde[0]['num_cde'] ?? null);


            return $numCdeVide && $daValide && $orValide;
        }
    }


    private function doitEditerBc(array $situationCde, array $infoDaDirect, bool $achatDirect): bool
    {
        if ($achatDirect) {
            return (int)$infoDaDirect[0]['num_cde'] > 0
                &&  ($infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        } else {
            // numero de commande existe && ... && position terminer
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                &&
                ($situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        }
    }

    private function doitSoumettreBc(array $situationCde, bool $bcExiste, ?string $statutBc, array $statutBcDw, array $infoDaDirect, bool $achatDirect): bool
    {
        if ($achatDirect) {
            return (int)$situationCde[0]['num_cde'] > 0
                && $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        } else {
            // numero de commande existe && ... && position editer && BC n'est pas encore soumis
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                && $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        }
    }

    private function doitEnvoyerBc(array $situationCde, ?string $statutBc, DaAfficher $DaAfficher, string $statutSoumissionBc, array $infoDaDirect, bool $achatDirect): bool
    {
        if ($achatDirect) {
            return $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && in_array($statutSoumissionBc, [DaSoumissionBc::STATUT_VALIDE, DaSoumissionBc::STATUT_CLOTURE])
                && !$DaAfficher->getBcEnvoyerFournisseur();
        } else {
            // numero de commande existe && ... && position editer && BC n'est pas encore soumis
            return $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && in_array($statutSoumissionBc, [DaSoumissionBc::STATUT_VALIDE, DaSoumissionBc::STATUT_CLOTURE])
                && !$DaAfficher->getBcEnvoyerFournisseur();
        }
    }

    private function evaluerQuantites(array $qte, array $infoDaDirect, bool $achatDirect): array
    {
        if (empty($qte)) {
            return [false, false, false, false];
        }

        if ($achatDirect) {
            if (empty($infoDaDirect)) {
                return [false, false, false, false];
            }
            $q = $infoDaDirect[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_dispo'];
            $qteLivee = 0; //TODO: en attend du decision du client
        } else {
            $q = $qte[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_dispo'];
            $qteLivee = (int)$q['qte_livree'];
        }


        $partiellementDispo = $qteDem != $qteALivrer && $qteLivee == 0 && $qteALivrer > 0;
        $completNonLivrer = ($qteDem == $qteALivrer && $qteLivee < $qteDem) ||
            ($qteALivrer > 0 && $qteDem == ($qteALivrer + $qteLivee));
        $tousLivres = $qteDem == $qteLivee && $qteDem != 0;
        $partiellementLivre = $qteLivee > 0 && $qteLivee != $qteDem && $qteDem > ($qteLivee + $qteALivrer);

        return [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre];
    }


    private function updateQteCdeDansDaAfficher(array $qte, DaAfficher $DaAfficher, array $infoDaDirect, bool $achatDirect): void
    {
        if (!empty($qte) || !empty($infoDaDirect)) {

            if ($achatDirect) {
                $q = $infoDaDirect[0];
                $qteLivee = 0; //TODO: en attend du decision du client
                $qteReliquat = (int)$q['qte_en_attente']; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
            } else {
                $q = $qte[0];
                $qteLivee = (int)$q['qte_livree'];
                $qteReliquat = (int)$q['qte_reliquat']; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
            }
            $DaAfficher
                ->setQteEnAttent($qteReliquat)
                ->setQteLivrer($qteLivee)
                ->setQteDispo($qteDispo)
            ;
        }
    }


    private function updateSituationCdeDansDaAfficher(array $situationCde, DaAfficher $DaAfficher, ?string $numcde, array $infoDaDirect, bool $achatDirect): void
    {
        if (!empty($situationCde) || !empty($infoDaDirect)) {
            if ($achatDirect) {
                $positionBc = array_key_exists(0, $infoDaDirect) ? $infoDaDirect[0]['position_bc'] : '';
            } else {
                $positionBc = array_key_exists(0, $situationCde) ? $situationCde[0]['position_bc'] : '';
            }
            $DaAfficher->setPositionBc($positionBc)
                ->setNumeroCde($numcde);
        }
    }

    private function updateInfoOR(string $numDit, DaAfficher $DaAfficher)
    {
        [$numOr, $statutOr] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($numDit);
        $datePlanningOr = $this->getDatePlannigOr($numOr);

        $DaAfficher
            ->setNumeroOr($numOr)
            ->setDatePlannigOr($datePlanningOr)
        ;

        if ($DaAfficher->getStatutOr() != DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION) {
            $DaAfficher->setStatutOr($statutOr);
        }
    }

    private function getDatePlannigOr(?string $numOr)
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

    private function getDaAfficher(string $numDa, string $numDit,  string $ref, string $designation): ?DaAfficher
    {
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMax($numDa);
        $conditionDeRecuperation = [
            'numeroDemandeAppro' => $numDa,
            'numeroDemandeDit' => $numDit,
            'artRefp' => $ref,
            'artDesi' => $designation,
            'numeroVersion' => $numeroVersionMax
        ];
        return $this->daAfficherRepository->findOneBy($conditionDeRecuperation);
    }
}
