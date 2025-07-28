<?php

namespace App\Model\dit\migration;

use App\Model\Model;

class MigrationDataModel extends Model
{
    // public function __construct()
    // {
    //     parent::__construct();
    // }

    public function getDitMigrer()
    {
        $sql = " SELECT 
                NumeroDemandeIntervention,
                '7' as type_document,
                null as code_societe,
                'A REALISER' as type_reparation,
                CASE    
                        when MASATE = 'MAS' then 'ATE MAS'
                        when MASATE = 'ATE' then 'ATE TANA'
                END as reparation_realise,
                '2' as categorie_demande, 
                'INTERNE' as internet_externe,
                IDAgenceDebiteur+'-'+IDServiceDebiteur as agence_service_debiteur,
                IDAgence+'-'+IDService as agence_service_emmeteur,
                NULL as nom_client,
                NULL as numero_telephone,
                NULL as date_or,
                NULL as heure_or,
                '1900-01-01' as date_prevue_travaux,
                DemandeDevis as demande_devis,
                '1' as id_niveau_urgence,
                'NON' as avis_recouvrement,
                NULL as client_sous_contrat,
                'Migration | '+ObjetDemande as objet_demande,
                ObjetDemande+' | '+DetailDemande as detail_demande,
                'OUI' as livraison_partiel,
                NumeroMateriel as ID_Materiel,
                NULL as mail_demandeur,
                DateDemande as date_demande,
                FORMAT(HeureDemande, 'HH:mm') as heure_demande,
                null as date_cloture,
                null as heure_cloture,
                FichierJoint1 as piece_joint1,
                FichierJoint2 as piece_joint2,
                FichierJoint3 as piece_joint,
                UtilisateurDemandeur as utilisateur_demandeur,
                Observations as observations,
                NULL as id_statut_demande,
                null as date_validation,
                null as heure_validation,
                null as numero_client,
                null as libelle_client,
                null as date_fin_souhaite,
                null as numero_or,
                null as observation_direction_technique,
                null as observation_devis,
                null as numero_devis_rattache,
                null as date_soumission_devis,
                null as devis_valide,
                null as date_validation_devis,
                null as id_service_intervenant,
                null as date_devis_fin_probable,
                null as date_fin_estimation_travaux,
                null as code_section,
                null as mas_ate,
                null as code_ate,
                null as secteur,
                null as utilisateur_intervenant,
                KilometrageMachine as KM_machine,
                HeureMachine as heure_machine,
                null as date_devis_rattache,
                null as section_affectee, 
                null as statut_or,
                null as statut_commande,
                null as date_validation_or,
                null as agence_emetteur_id,
                null as service_emetteur_id,
                null as agence_debiteur_id,
                null as service_debiteur_id,
                null as section_support_1,
                null as section_support_2,
                null as section_support_3,
                '1' as migration,
                null as etat_facturation,
                '0/0' as ri,
                null as mail_client,
                2 as num_migr
                FROM DemandeIntervention
                inner join StatutInfo on StatutInfo.IDStatutInfo = DemandeIntervention.IDStatutInfo
                where CodeSection not in ('ASS','MAG') and InterneExterne='I' and LibelleStatutInfo in ('encours','attente devis','incomplet','devis à approuver')
                and IDAgence in ('01','02','10','20','30','40','50','60','80','90','91','92')
        ";
        $result = [];
        $query = odbc_exec($this->connexion04->getConnexion(), $sql);
        if (! $query) {
            die("Erreur lors de l'exécution de la requête : " . odbc_errormsg($this->connexion));
        }
        while ($tab = odbc_fetch_array($query)) {
            $result[] = $tab;
        }

        return $result;
    }

    public function insertDit($row)
    {
        $sql = " INSERT INTO demande_intervention (
                    NumeroDemandeIntervention, type_document, code_societe, type_reparation, 
                    reparation_realise, categorie_demande, internet_externe, agence_service_debiteur, 
                    agence_service_emmeteur, nom_client, numero_telephone, date_or, heure_or, 
                    date_prevue_travaux, demande_devis, id_niveau_urgence, avis_recouvrement, 
                    client_sous_contrat, objet_demande, detail_demande, livraison_partiel, 
                    ID_Materiel, mail_demandeur, date_demande, heure_demande, date_cloture, 
                    heure_cloture, piece_joint1, piece_joint2, piece_joint, utilisateur_demandeur, 
                    observations, id_statut_demande, date_validation, heure_validation, 
                    numero_client, libelle_client, date_fin_souhaite, numero_or, 
                    observation_direction_technique, observation_devis, numero_devis_rattache, 
                    date_soumission_devis, devis_valide, date_validation_devis, id_service_intervenant, 
                    date_devis_fin_probable, date_fin_estimation_travaux, code_section, mas_ate, 
                    code_ate, secteur, utilisateur_intervenant, KM_machine, heure_machine, 
                    date_devis_rattache, section_affectee, statut_or, statut_commande, 
                    date_validation_or, agence_emetteur_id, service_emetteur_id, 
                    agence_debiteur_id, service_debiteur_id, section_support_1, 
                    section_support_2, section_support_3, migration, etat_facturation, 
                    ri, mail_client, num_migr
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";

        $stmt = odbc_prepare($this->connexion, $sql);
        $params = array_values($row);

        if (! odbc_execute($stmt, $params)) {
            die("Erreur lors de l'insertion : " . odbc_errormsg($this->connexion));
        }
    }
}
