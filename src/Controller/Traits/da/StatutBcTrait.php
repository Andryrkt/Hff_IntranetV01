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
            DemandeAppro::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
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
            //statut pour DA Reappro
            DaSoumissionBc::STATUT_CESSION_A_GENERER        => 'bg-bc-cession-a-generer',
            DaSoumissionBc::STATUT_EN_COURS_DE_PREPARATION  => 'bg-bc-en-cours-de-preparation',
            //statut pour DA Reappro, DA direct, DA via OR
            DaSoumissionBc::STATUT_TOUS_LIVRES              => 'tout-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE      => 'partiellement-livre',
            DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO      => 'partiellement-dispo',
            DaSoumissionBc::STATUT_COMPLET_NON_LIVRE        => 'complet-non-livre',

        ];
        //----------------------------------------------------------------------------------------------------
    }

    private function statutBc(DaAfficher $DaAfficher): ?string
    {
        // 0. recupération de l'entity manager
        $em = self::getEntity();

        // 1. recupération des données necessaire dans DaAfficher
        [$ref, $numDit, $numDa, $designation, $numeroOr, $statutOr, $statutBc, $statutDa] = $this->getVariableNecessaire($DaAfficher);

        // 2. on met vide la statut bc selon le condition en survolon la fonction
        if ($this->doitRetournerVide($statutDa)) return '';

        /** 3. recuperation type DA @var bool $daDirect @var bool $daViaOR @var bool $daReappro  */
        [$daDirect, $daViaOR, $daReappro] = $this->getTypeDa($DaAfficher);

        // 4. modification de l'information de l'or
        if (!$daDirect) $this->updateInfoOR($DaAfficher, $daViaOR, $daReappro);

        // 5. modification du statut de la DA
        if ($statutOr === DemandeAppro::STATUT_DW_A_MODIFIER && $statutDa !== DemandeAppro::STATUT_EN_COURS_CREATION) $DaAfficher->setStatutDal(DemandeAppro::STATUT_EN_COURS_CREATION);

        /** 6.recuperation des informations necessaire dans IPS  @var array $infoDaDirect @var array $situationCde*/
        [$infoDaDirect, $situationCde] = $this->getInfoNecessaireIps($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc);

        /** 7. Non dispo || DA avec DIT et numéro OR null || numéro OR non vide et statut OR non vide || infoDaDirect ou situationCde est vide */
        if ($DaAfficher->getNonDispo() || ($numeroOr == null && $daViaOR) || ($numeroOr != null && empty($statutOr)) || !$this->aSituationCde($situationCde, $infoDaDirect, $daViaOR, $daDirect)) {
            return $statutBc;
        }

        /** 8. recupération de numero commande dans IPS et  statut commande dans da_bc_soumission */
        [$numCde, $statutSoumissionBc] = $this->getInfoCde($infoDaDirect, $situationCde, $daDirect, $daViaOR, $daReappro, $numeroOr, $em);

        /** 9. recupération des qte necessaire dans IPS @var array $qte */
        $qte = $this->getQte($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc, $daDirect, $daViaOR, $daReappro, $numCde);

        /** 10.  @var bool $partiellementDispo @var bool $completNonLivrer @var bool $toutLivres @var bool $partiellementLivrer */
        [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre] = $this->evaluerQuantites($qte,  $infoDaDirect, $daDirect, $DaAfficher);

        // 11. modification de situation commande dans DaAfficher
        $this->updateSituationCdeDansDaAfficher($situationCde, $DaAfficher, $numCde, $infoDaDirect, $daDirect, $daViaOR, $daReappro, $qte);

        // 12. modification du Qte de commande dans DaAfficher
        $this->updateQteCdeDansDaAfficher($qte, $DaAfficher, $infoDaDirect, $daDirect, $daViaOR);

        if (empty($situationCde) && ($daViaOR || $daReappro) && $statutOr === DitOrsSoumisAValidation::STATUT_VALIDE) {
            return 'PAS DANS OR';
        }
        // DA Direct , DA Via OR
        elseif ($this->doitGenererBc($situationCde, $statutDa, $statutOr, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A générer';
        } elseif ($this->doitEditerBc($situationCde, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A éditer';
        } elseif ($this->doitSoumettreBc($situationCde, $numCde, $statutBc, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A soumettre à validation';
        } elseif ($this->doitEnvoyerBc($situationCde, $statutBc, $DaAfficher, $statutSoumissionBc, $infoDaDirect, $daDirect, $daViaOR)) {
            return 'A envoyer au fournisseur';
        } elseif ($DaAfficher->getBcEnvoyerFournisseur() && !$DaAfficher->getEstFactureBlSoumis()) {
            return 'BC envoyé au fournisseur';
        }
        // DA Reappro
        elseif ($daReappro && $numeroOr == null && $statutOr == DemandeAppro::STATUT_DW_VALIDEE) {
            return DaSoumissionBc::STATUT_CESSION_A_GENERER;
        } elseif ($daReappro && $numeroOr != null && $statutOr == DemandeAppro::STATUT_DW_VALIDEE && $DaAfficher->getEstBlReapproSoumis() == false) {
            return DaSoumissionBc::STATUT_EN_COURS_DE_PREPARATION;
        }
        // DA Reappro, DA Direct , DA Via OR
        elseif ($partiellementDispo) {
            return 'Partiellement dispo';
        } elseif ($completNonLivrer) {
            return 'Complet non livré';
        } elseif ($tousLivres) {
            return 'Tous livrés';
        } elseif ($partiellementLivre) {
            return 'Partiellement livré';
        } elseif ($DaAfficher->getEstFactureBlSoumis()) {
            return 'BC envoyé au fournisseur';
        }
        // DA Direct , DA Via OR
        elseif ($daDirect || $daViaOR) {
            return $statutSoumissionBc;
        }

        return '';
    }

    private function getInfoCde($infoDaDirect, $situationCde, $daDirect, $daViaOR, $daReappro, $numeroOr, $em): array
    {
        $numCde = $this->numeroCde($infoDaDirect, $situationCde, $daDirect, $daViaOR, $daReappro, $numeroOr);

        $statutSoumissionBc = $em->getRepository(DaSoumissionBc::class)->getStatut($numCde);

        return [$numCde, $statutSoumissionBc];
    }

    private function getInfoNecessaireIps($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc): array
    {
        /**  pour le DaDirect @var array $infoDaDirect */
        $infoDaDirect = $this->daModel->getInfoDaDirect($numDa, $ref, $designation);

        /** IPS pour le DaViaOR @var array $situationCde */
        $situationCde = $this->daModel->getSituationCde($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc);

        return [$infoDaDirect, $situationCde];
    }

    /**
     * On met vide le statut BC si 
     * statut_dal n'est validé || statut_dal est parmis (soumis à l'ate, soumis à l'appro, autoriser à modifier l'ate)
     *
     * @param string|null $statutDa
     * @return boolean
     */
    private function doitRetournerVide(?string $statutDa): bool
    {
        // si statut Da n'est pas validé
        if ($statutDa !== DemandeAppro::STATUT_VALIDE) return true;

        $statutDaInternet = [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
        ];
        // si le statut DA est par mis ci dessus
        return in_array($statutDa, $statutDaInternet, true);
    }



    private function getQte($ref, $numDit, $numDa, $designation, $numeroOr, $statutBc, $daDirect, $daViaOR, $daReappro, $numCde): array
    {
        if ($daDirect) $qte = $this->daModel->getEvolutionQteDaDirect($numCde, $ref, $designation);
        // pour da via OR et DA reappro
        if ($daViaOR || $daReappro) $qte = $this->daModel->getEvolutionQteDaAvecDit($numDit, $ref, $designation, $numeroOr, $statutBc, $numDa, $daReappro);

        return $qte;
    }

    private function getTypeDa(DaAfficher $DaAfficher): array
    {
        //Recuperation de type de demande
        $daTypeId = $DaAfficher->getDaTypeId();
        $daDirect = $daTypeId == DemandeAppro::TYPE_DA_DIRECT; // condition pour daDirect
        $daViaOR = $daTypeId == DemandeAppro::TYPE_DA_AVEC_DIT; // condition pour Da via OR
        $daReappro = $daTypeId == DemandeAppro::TYPE_DA_REAPPRO;

        return [$daDirect, $daViaOR, $daReappro];
    }

    private function getVariableNecessaire(DaAfficher $DaAfficher): array
    {
        $ref         = $DaAfficher->getArtRefp();
        $numDit      = $DaAfficher->getNumeroDemandeDit();
        $numDa       = $DaAfficher->getNumeroDemandeAppro();
        $designation = $DaAfficher->getArtDesi();
        $numeroOr    = $DaAfficher->getNumeroOr();
        $statutOr    = $DaAfficher->getStatutOr();
        $statutBc    = $DaAfficher->getStatutCde();
        $statutDa    = $DaAfficher->getStatutDal();

        return [$ref, $numDit, $numDa, $designation, $numeroOr, $statutOr, $statutBc, $statutDa];
    }

    private function getTypeDemande() {}

    private function numeroCde(array $infoDaDirect, array $situationCde, bool $daDirect, bool $daViaOR, bool $daReappro, ?string $numOr): ?string
    {
        $numCde = '';
        if ($daDirect) $numCde = array_key_exists(0, $infoDaDirect) ? $infoDaDirect[0]['num_cde'] : '';
        if ($daViaOR) $numCde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        if ($daReappro) $numCde = $numOr; // numero OR = numero Cde pour DaReappro
        return $numCde;
    }

    private function aSituationCde(array $situationCde, array $infoDaDirect, bool $daViaOR, bool $daDirect): bool
    {
        if ($daViaOR) return !array_key_exists(0, $situationCde);
        elseif ($daDirect) return !array_key_exists(0, $infoDaDirect);
        else return true;
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
        } elseif ($daViaOR) {

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
            return false; //  DA réappro
        }
    }


    private function doitEditerBc(array $situationCde, array $infoDaDirect, bool $daDirect, bool $daViaOR): bool
    {
        if ($daDirect) {
            return (int)$infoDaDirect[0]['num_cde'] > 0
                &&  ($infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        } elseif ($daViaOR) {
            // numero de commande existe && ... && position terminer
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                &&
                ($situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_TERMINER || $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_ENCOUR);
        } else {
            return false; // DA réappro
        }
    }

    private function doitSoumettreBc(array $situationCde, ?string $numCde, ?string $statutBc, array $infoDaDirect, bool $daDirect, $daViaOR): bool
    {
        if ($numCde == null) $numCde = '';
        $statutBcDw = [
            DaSoumissionBc::STATUT_SOUMISSION,
            DaSoumissionBc::STATUT_A_VALIDER_DA,
            DaSoumissionBc::STATUT_VALIDE,
            DaSoumissionBc::STATUT_CLOTURE,
            DaSoumissionBc::STATUT_REFUSE
        ];

        $bcExiste = $this->daSoumissionBcRepository->bcExists($numCde);

        if ($daDirect) {
            return (int)$infoDaDirect[0]['num_cde'] > 0
                && $infoDaDirect[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        } elseif ($daViaOR) {
            // numero de commande existe && ... && position editer && BC n'est pas encore soumis
            return (int)$situationCde[0]['num_cde'] > 0
                && $situationCde[0]['slor_natcm'] === 'C'
                && $situationCde[0]['position_bc'] === DaSoumissionBc::POSITION_EDITER
                && !in_array($statutBc, $statutBcDw)
                && !$bcExiste;
        } else {
            return false; // DA réappro
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
            return false; // DA réappro
        }
    }

    private function evaluerQuantites(array $qte, array $infoDaDirect, bool $daDirect, DaAfficher $DaAfficher): array
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
        } else { // pour via or et reappro
            $q = $qte[0];
            $qteDem = (int)$q['qte_dem'];
            $qteALivrer = (int)$q['qte_dispo'];
            $qteLivee = (int)$q['qte_livree'];
        }


        $soumissionFait = ($DaAfficher->getEstFactureBlSoumis() || $DaAfficher->getEstBlReapproSoumis());
        $partiellementDispo = ($qteDem != $qteALivrer && $qteLivee == 0 && $qteALivrer > 0) && $soumissionFait;
        $completNonLivrer = (($qteDem == $qteALivrer && $qteLivee < $qteDem) || ($qteALivrer > 0 && $qteDem == ($qteALivrer + $qteLivee))) && $soumissionFait;
        $tousLivres = ($qteDem == $qteLivee && $qteDem != 0) && $soumissionFait;
        $partiellementLivre = ($qteLivee > 0 && $qteLivee != $qteDem && $qteDem > ($qteLivee + $qteALivrer)) && $soumissionFait;

        return [$partiellementDispo, $completNonLivrer, $tousLivres, $partiellementLivre];
    }


    private function updateQteCdeDansDaAfficher(array $qte, DaAfficher $DaAfficher, array $infoDaDirect, bool $daDirect, bool $daViaOR): void
    {
        if (!empty($qte) || !empty($infoDaDirect)) {

            if ($daDirect) {
                $q = $infoDaDirect[0];
                $qteDem = (int)$q['qte_dem'];
                $qteLivee = 0; //TODO: en attend du decision du client
                $qteReliquat = (int)$q['qte_en_attente']; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
            } else { // pour via or et reappro
                $q = $qte[0];
                $qteDem = (int)$q['qte_dem'];
                $qteLivee = (int)$q['qte_livree'];
                $qteReliquat = $qteDem - $qteLivee; // quantiter en attente
                $qteDispo = (int)$q['qte_dispo'];
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


    private function updateSituationCdeDansDaAfficher(array $situationCde, DaAfficher $DaAfficher, ?string $numcde, array $infoDaDirect, bool $daDirect, bool $daViaOR, bool $daReappro, array $qte): void
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

        if ($daReappro) {
            if (empty($qte)) return;
            $q = $qte[0];
            $qteDem = (int)$q['qte_dem'];
            $qteLivee = (int)$q['qte_livree'];
            $qteReliquat = $qteDem - $qteLivee; // quantiter en attente
            $qteDispo = (int)$q['qte_dispo'];

            if ($qteDem >= $qteLivee) {
                $DaAfficher->setNumeroCde($numcde);
            }
        };
    }

    /**
     * mise à jour du numéro OR et date planning OR dans la table DaAfficher
     *
     * @param DaAfficher $DaAfficher
     * @return void
     */
    private function updateInfoOR(DaAfficher $DaAfficher, bool $daViaOr, bool $daReappro): void
    {
        if ($daViaOr) {
            /** recupération numero OR et Statut OR dans la table ors_soumis_a_validation @var ?string $numOr @var ?string $statutOr  */
            [$numOr, $statutOr] = $this->ditOrsSoumisAValidationRepository->getNumeroEtStatutOr($DaAfficher->getNumeroDemandeDit());
            $datePlanningOr = $this->getDatePlannigOr($numOr);
        }
        //recupération de numero OR dans IPS
        elseif ($daReappro) {
            $numOr = $this->daModel->getNumeroOrReappro($DaAfficher->getNumeroDemandeAppro());
            $datePlanningOr = null;
        }

        // recupération date planning or si da via OR

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
