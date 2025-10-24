<?php

namespace App\Controller\Traits\da;

use DateTime;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Model\da\DaModel;
use Symfony\Component\Validator\Constraints\Length;

trait StatutBcTrait
{
    // Styles des DA, OR, BC dans le css
    private $styleStatutDA = [];
    private $styleStatutOR = [];
    private $styleStatutBC = [];

    // Repository / Model
    private DaModel $daModel;

    /**
     * Initialise le trait StatutBcTrait
     */
    public function initStatutBcTrait()
    {
        $this->daModel = new DaModel();

        //----------------------------------------------------------------------------------------------------
        $this->styleStatutDA = [
            DemandeAppro::STATUT_VALIDE               => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_TERMINER             => 'bg-primary text-white',
            DemandeAppro::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
            DemandeAppro::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
            DemandeAppro::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
            DemandeAppro::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
            DemandeAppro::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE  => 'bg-creation-demande-initiale',
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        ];
        $this->styleStatutOR = [
            DitOrsSoumisAValidation::STATUT_VALIDE                     => 'bg-or-valide',
            DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION => 'bg-a-resoumettre-a-validation',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_CA               => 'bg-or-valider-ca',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_DT               => 'bg-or-valider-dt',
            DitOrsSoumisAValidation::STATUT_A_VALIDER_CLIENT           => 'bg-or-valider-client',
            DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CA       => 'bg-modif-demande-ca',
            DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CLIENT   => 'bg-modif-demande-client',
            DitOrsSoumisAValidation::STATUT_REFUSE_CA                  => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_REFUSE_CLIENT              => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_REFUSE_DT                  => 'bg-or-non-valide',
            DitOrsSoumisAValidation::STATUT_SOUMIS_A_VALIDATION        => 'bg-or-soumis-validation',
        ];
        $this->styleStatutBC = [
            DaSoumissionBc::STATUT_A_GENERER                => 'bg-bc-a-generer',
            DaSoumissionBc::STATUT_A_EDITER                 => 'bg-bc-a-editer',
            DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION => 'bg-bc-a-soumettre-a-validation',
            DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR => 'bg-bc-a-envoyer-au-fournisseur',
            DaSoumissionBc::STATUT_SOUMISSION               => 'bg-bc-soumission',
            DaSoumissionBc::STATUT_A_VALIDER_DA             => 'bg-bc-a-valider-da',
            DaSoumissionBc::STATUT_NON_DISPO                => 'bg-bc-non-dispo',
            DaSoumissionBc::STATUT_VALIDE                   => 'bg-bc-valide',
            DaSoumissionBc::STATUT_CLOTURE                  => 'bg-bc-cloture',
            DaSoumissionBc::STATUT_REFUSE                   => 'bg-bc-refuse',
            DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR => 'bg-bc-envoye-au-fournisseur',
            'Non validé'                                    => 'bg-bc-non-valide',
            DaSoumissionBc::STATUT_TOUS_LIVRES              => 'tout-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE      => 'partiellement-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO      => 'partiellement-dispo',
            DaSoumissionBc::STATUT_COMPLET_NON_LIVRE        => 'complet-non-livre',
        ];
        //----------------------------------------------------------------------------------------------------
    }

    private function statutBc(DaAfficher $DaAfficher): ?string
    {
        $em = self::getEntity();
        $qte = [];

        $ref         = $DaAfficher->getArtRefp();
        $numDit      = $DaAfficher->getNumeroDemandeDit();
        $numDa       = $DaAfficher->getNumeroDemandeAppro();
        $designation = $DaAfficher->getArtDesi();
        $numeroOr    = $DaAfficher->getNumeroOr();

        if ($DaAfficher == null || $DaAfficher->getStatutDal() !== DemandeAppro::STATUT_VALIDE) {
            return '';
        };

        $statutBc = $DaAfficher->getStatutCde();
        $daTypeId = $DaAfficher->getDaTypeId();
        $daDirect = $daTypeId == DemandeAppro::TYPE_DA_DIRECT;
        $daViaOR = $daTypeId == DemandeAppro::TYPE_DA_AVEC_DIT;

        if ($daViaOR) $this->updateInfoOR($numDit, $DaAfficher);

        /** Non dispo || DA avec DIT et numéro OR null || numéro OR non vide et statut OR non vide */
        if ($DaAfficher->getNonDispo() || ($numeroOr == null && $daViaOR) || ($numeroOr != null && empty($DaAfficher->getStatutOr()))) {
            return $statutBc;
        }
        $infoDaDirect = $this->daModel->getInfoDaDirect($numDa, $ref, $designation);
        $situationCde = $this->daModel->getSituationCde($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc);

        $statutDaIntanert = [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE
        ];
        $statutDa = $this->demandeApproRepository->getStatutDa($numDa);
        if (in_array($statutDa, $statutDaIntanert)) {
            return '';
        }

        $numcde = $this->numeroCde($infoDaDirect, $situationCde, $daDirect, $daViaOR);
        $bcExiste = $this->daSoumissionBcRepository->bcExists($numcde);
        $statutSoumissionBc = $em->getRepository(DaSoumissionBc::class)->getStatut($numcde);

        if ($daDirect) $qte = $this->daModel->getEvolutionQteDaDirect($numcde, $ref, $designation);
        if ($daViaOR) $qte = $this->daModel->getEvolutionQteDaAvecDit($numDit, $ref, $designation, $numeroOr, $statutBc, $numDa, $DaAfficher->getQteDem());
        [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre] = $this->evaluerQuantites($qte,  $infoDaDirect, $daDirect, $daViaOR, $DaAfficher);


        $this->updateSituationCdeDansDaAfficher($situationCde, $DaAfficher, $numcde, $infoDaDirect, $daDirect, $daViaOR);
        $this->updateQteCdeDansDaAfficher($qte, $DaAfficher, $infoDaDirect, $daDirect, $daViaOR);


        $statutBcDw = [
            DaSoumissionBc::STATUT_SOUMISSION,
            DaSoumissionBc::STATUT_A_VALIDER_DA,
            DaSoumissionBc::STATUT_VALIDE,
            DaSoumissionBc::STATUT_CLOTURE,
            DaSoumissionBc::STATUT_REFUSE
        ];

        if (empty($situationCde) && $daViaOR && $DaAfficher->getStatutOr() === DitOrsSoumisAValidation::STATUT_VALIDE) {
            return 'PAS DANS OR';
        }

        if ($this->doitGenererBc($situationCde, $statutDa, $DaAfficher->getStatutOr(), $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A générer';
        }

        if (!$this->aSituationCde($situationCde, $infoDaDirect)) {
            return $statutBc;
        }

        if ($this->doitEditerBc($situationCde, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A éditer';
        }

        if ($this->doitSoumettreBc($situationCde, $bcExiste, $statutBc, $statutBcDw, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A soumettre à validation';
        }


        if ($this->doitEnvoyerBc($situationCde, $statutBc, $DaAfficher, $statutSoumissionBc, $infoDaDirect, $daDirect, $daViaOR)) {
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

    private function numeroCde(array $infoDaDirect, array $situationCde, bool $daDirect, bool $daViaOR): ?string
    {
        $numCde = '';
        if ($daDirect) $numCde = array_key_exists(0, $infoDaDirect) ? $infoDaDirect[0]['num_cde'] : '';
        if ($daViaOR) $numCde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        return $numCde;
    }

    private function aSituationCde(array $situationCde, array $infoDaDirect): bool
    {
        return array_key_exists(0, $situationCde) || array_key_exists(0, $infoDaDirect);
    }

    private function doitGenererBc(array $situationCde, string $statutDa, ?string $statutOr, array $infoDaDirect, bool $daDirect, bool $daViaOR): bool
    {
        if ($daDirect) {
            if ($statutOr === DemandeAppro::STATUT_DW_VALIDEE) {
                if (empty($infoDaDirect)) {
                    return true;
                }

                // Si le numéro de commande est vide
                $numCdeVide = empty($infoDaDirect[0]['num_cde'] ?? null);

                return $numCdeVide;
            }
            return false;
        } else if ($daViaOR) {

            $daValide = $statutDa === DemandeAppro::STATUT_VALIDE;
            $orValide = $statutOr === DitOrsSoumisAValidation::STATUT_VALIDE;

            // Si aucune situation de commande n'est présente
            if (empty($situationCde)) {
                return $daValide && $orValide;
            }
            // Si une situation existe mais sans numéro de commande
            $numCdeVide = empty($situationCde[0]['num_cde'] ?? null);

            return $numCdeVide && $daValide && $orValide;
        } else {
            return false; // TODO: concernant les DA réappro
        }
    }


    private function doitEditerBc(array $situationCde, array $infoDaDirect, bool $daDirect, bool $daViaOR): bool
    {
        if ($daDirect) {
            return (int)$infoDaDirect[0]['num_cde'] > 0
                &&  ($infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        } else if ($daViaOR) {
            // numero de commande existe && ... && position terminer
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                &&
                ($situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        } else {
            return false; // TODO: concernant les DA réappro
        }
    }

    private function doitSoumettreBc(array $situationCde, bool $bcExiste, ?string $statutBc, array $statutBcDw, array $infoDaDirect, bool $daDirect, $daViaOR): bool
    {
        if ($daDirect) {
            return (int)$infoDaDirect[0]['num_cde'] > 0
                && $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        } else if ($daViaOR) {
            // numero de commande existe && ... && position editer && BC n'est pas encore soumis
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                && $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        } else {
            return false; // TODO: concernant les DA réappro
        }
    }

    private function doitEnvoyerBc(array $situationCde, ?string $statutBc, DaAfficher $DaAfficher, string $statutSoumissionBc, array $infoDaDirect, bool $daDirect, bool $daViaOR): bool
    {
        if ($daDirect) {
            return $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && in_array($statutSoumissionBc, [DaSoumissionBc::STATUT_VALIDE, DaSoumissionBc::STATUT_CLOTURE])
                && !$DaAfficher->getBcEnvoyerFournisseur();
        } else if ($daViaOR) {
            // numero de commande existe && ... && position editer && BC n'est pas encore soumis
            return $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && in_array($statutSoumissionBc, [DaSoumissionBc::STATUT_VALIDE, DaSoumissionBc::STATUT_CLOTURE])
                && !$DaAfficher->getBcEnvoyerFournisseur();
        } else {
            return false; // TODO: concernant les DA réappro
        }
    }

    private function evaluerQuantites(array $qte, array $infoDaDirect, bool $daDirect, bool $daViaOR, DaAfficher $DaAfficher): array
    {
        if (empty($qte)) {
            return [false, false, false, false];
        }

        if ($daDirect) {
            if (empty($infoDaDirect)) {
                return [false, false, false, false];
            }
            $q = $infoDaDirect[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_dispo'];
            $qteLivee = 0; //TODO: en attend du decision du client
        } else if ($daViaOR) {
            $q = $qte[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_dispo'];
            $qteLivee = (int)$q['qte_livree'];
        }

        $partiellementDispo = ($qteDem != $qteALivrer && $qteLivee == 0 && $qteALivrer > 0) && $DaAfficher->getEstFactureBlSoumis();
        $completNonLivrer = (($qteDem == $qteALivrer && $qteLivee < $qteDem) || ($qteALivrer > 0 && $qteDem == ($qteALivrer + $qteLivee))) && $DaAfficher->getEstFactureBlSoumis();
        $tousLivres = ($qteDem == $qteLivee && $qteDem != 0) && $DaAfficher->getEstFactureBlSoumis();
        $partiellementLivre = ($qteLivee > 0 && $qteLivee != $qteDem && $qteDem > ($qteLivee + $qteALivrer)) && $DaAfficher->getEstFactureBlSoumis();

        return [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre];
    }


    private function updateQteCdeDansDaAfficher(array $qte, DaAfficher $DaAfficher, array $infoDaDirect, bool $daDirect, bool $daViaOR): void
    {
        if (!empty($qte) || !empty($infoDaDirect)) {

            if ($daDirect) {
                $q = $infoDaDirect[0];
                $qteLivee = 0; //TODO: en attend du decision du client
                $qteReliquat = (int)$q['qte_en_attente']; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
                $qteDem = (int)$q['qte_dem'];
            } else if ($daViaOR) {
                $q = $qte[0];
                $qteLivee = (int)$q['qte_livree'];
                $qteReliquat = (int)$q['qte_reliquat']; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
                $qteDem = (int)$q['qte_dem'];
            }

            if ($DaAfficher->getNumeroCde() != '26246458' && $DaAfficher->getArtDesi() != 'ECROU HEX. AC.GALVA A CHAUD CL.8 DI') {
                $DaAfficher
                    ->setQteEnAttent($qteReliquat)
                    ->setQteLivrer($qteLivee)
                    ->setQteDispo($qteDispo)
                    ->setQteDemIps($qteDem)
                ;
            }
        }
    }


    private function updateSituationCdeDansDaAfficher(array $situationCde, DaAfficher $DaAfficher, ?string $numcde, array $infoDaDirect, bool $daDirect, bool $daViaOR): void
    {
        if (!empty($situationCde) || !empty($infoDaDirect)) {
            if ($daDirect) {
                $positionBc = array_key_exists(0, $infoDaDirect) ? $infoDaDirect[0]['position_bc'] : '';
            } else if ($daViaOR) {
                $positionBc = array_key_exists(0, $situationCde) ? $situationCde[0]['position_bc'] : '';
            }
            $DaAfficher->setPositionBc($positionBc ?? '')
                ->setNumeroCde($numcde);
        }
    }

    private function updateInfoOR(string $numDit, DaAfficher $DaAfficher)
    {
        [$numOr,] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($numDit);
        $datePlanningOr = $this->getDatePlannigOr($numOr);

        $DaAfficher
            ->setNumeroOr($numOr)
            ->setDatePlannigOr($datePlanningOr)
        ;
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
            //'numeroDemandeDit' => $numDit,
            'artRefp' => $ref,
            'artDesi' => $designation,
            'numeroVersion' => $numeroVersionMax
        ];
        return $this->daAfficherRepository->findOneBy($conditionDeRecuperation);
    }


    private function modificationNumLigneEtItv(string $ref, string $desi, string $numOr, DaAfficher $DaAfficher)
    {
        $numLigneEtItv = $this->daModel->getNumLigneAntItvIps($ref, $desi, $numOr);

        if (!empty($numLigneEtItv)) {
            if (count($numLigneEtItv) > 1) {
                foreach ($numLigneEtItv as $value) {
                    if ($DaAfficher->getNumeroLigneIps() == null && $DaAfficher->getNumeroInterventionIps() == null && $DaAfficher->getQteDemIps() == $value['qte_dem']) {

                        $DaAfficher
                            ->setNumeroLigneIps($value['num_ligne'])
                            ->setNumeroInterventionIps($value['numero_intervention_ips']);
                        break;
                    }
                }
            } else {
                $numLigne = $numLigneEtItv[0]['num_ligne'];
                $numIntervention = $numLigneEtItv[0]['numero_intervention_ips'];
                $DaAfficher
                    ->setNumeroLigneIps($numLigne)
                    ->setNumeroInterventionIps($numIntervention);
            }
        }
    }
}
