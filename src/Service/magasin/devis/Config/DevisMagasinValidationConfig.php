<?php

namespace App\Service\magasin\devis\Config;

use App\Entity\magasin\devis\DevisMagasin;

/**
 * Configuration centralisée pour la validation des devis magasin
 * 
 * Cette classe contient toutes les constantes et configurations
 * utilisées par les services de validation des devis magasin.
 */
class DevisMagasinValidationConfig
{
    // Configuration des fichiers
    public const FILE_FIELD_NAME = 'pieceJoint01';
    public const FILENAME_PATTERN = '/^(DEVIS MAGASIN|CONTROLE DEVIS)_(\d+)_(\d+)_(\d+)\\.pdf$/';


    // Statuts bloquants pour la validation de prix (VP)
    public const VP_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_A_CONFIRMER,
    ];

    // Statuts bloquants pour la validation de devis (VD) - Prix validé
    public const VD_PRICE_VALIDATED_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_VALIDER_TANA,
    ];

    // Statuts bloquants pour la validation de devis (VD) - Prix modifié
    public const VD_PRICE_MODIFIED_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_MODIFIER_TANA,
    ];

    // Statuts bloquants pour les changements de lignes sans montant
    public const VD_LINES_CHANGE_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_VALIDER_TANA,
        DevisMagasin::STATUT_PRIX_VALIDER_AGENCE,
    ];

    public const VD_STATUT_MODIFIER = [
        DevisMagasin::STATUT_PRIX_MODIFIER_TANA,
        DevisMagasin::STATUT_PRIX_MODIFIER_AGENCE,
    ];

    public const VD_DEMANDE_REFUSE_PAR_PM = [
        DevisMagasin::STATUT_DEMANDE_REFUSE_PAR_PM,
    ];

    // Statuts bloquants pour la validation de devis (VD)
    public const BLOCKING_STATUSES = [
        DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE,
    ];

    public const VD_AMOUNT_MODIFIED_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_VALIDE_AGENCE,
    ];

    public const VD_LINES_AMOUNT_MODIFIED_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_VALIDE_AGENCE,
    ];

    public const VD_LINES_TOTAL_MODIFIED_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_CLOTURER_A_MODIFIER,
        DevisMagasin::STATUT_ENVOYER_CLIENT,
    ];

    public const VP_PRIX_VALIDER_AGENCE_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_VALIDER_AGENCE,
    ];

    public const VP_PRIX_MODIFIER_AGENCE_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_MODIFIER_AGENCE,
    ];

    public const VP_VALIDE_A_ENVOYER_AU_CLIENT_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_VALIDE_AGENCE,
    ];

    public const VP_BLOCKING_STATUTS_VALIDE_CHEF_AGENCE = [
        DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE
    ];

    public const VP_BLOCKING_STATUTS_VALIDE_A_ENVOYER_AU_CLIENT_ET_SOMME_LINES_INCHANGE = [
        DevisMagasin::STATUT_VALIDE_AGENCE,
    ];

    public const VP_BLOCKING_STATUTS_CLOTURE_A_MODIFIER_ET_SOMME_MONTANT_IPS_INFERIEUR_SOMME_MONTANT_DEVIS = [
        DevisMagasin::STATUT_CLOTURER_A_MODIFIER,
        DevisMagasin::STATUT_ENVOYER_CLIENT
    ];


    public const POINTAGE_PRIX_A_CONFIRMER_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_A_CONFIRMER,
    ];
    public const POINTAGE_PRIX_MODIFIER_BLOCKING_STATUSES = [
        DevisMagasin::STATUT_PRIX_MODIFIER_TANA,
    ];

    // Messages d'erreur
    public const ERROR_MESSAGES = [
        // blocage avant soumission
        'missing_identifier' => 'Le numero de devis est obligatoire pour la soumission.',
        'devis_not_exists' => 'Veuillez demander une confirmation des prix du devis',
        'status_blocking_vp' => 'Une confirmation de prix pour ce devis est déjà en cours au magasin.',
        'price_already_validated' => 'Les prix ont déjà été validés par le parts manager. Veuillez envoyer le devis au client',
        'lines_changed' => 'Une ligne a été ajoutée dans votre devis. Veuillez demander une confirmation des prix.',
        'statut_modified' => "Une ligne a été ajoutée dans votre devis ou les prix n'ont pas été modifiés dans IPS. Veuillez demander une confirmation des prix.",
        'demande_refuse_par_pm' => 'Veuillez demander une confirmation des prix du devis.',
        'status_blocking_general' => "Un devis est en cours de validation chez le chef d'agence",
        'amount_modified' => "Le devis a déjà été validé. Veuillez l'envoyer au client",
        'lines_amount_modified' => "Une ligne a été ajoutée au devis dans IPS. Veuillez demander une confirmation des prix",
        'lines_modified' => "Une ligne a été ajoutée au devis dans IPS. Veuillez demander une confirmation des prix.",
        'vp_prix_valide_agence_et_somme_de_lignes_et_amount_inchangé' => "Les prix ont déjà été validés par le parts manager,. Veuillez faire valider le devis au chef d'agence",
        'vp_prix_modifier_agence_et_somme_de_lignes_et_amount_inchangé' => "Les prix ont déjà été validés par le parts manager,. Veuillez faire valider le devis au chef d'agence",
        'vp_valide_a_envoyer_au_client_et_somme_de_lignes_changeet_amount_inchange' => "Le montant du devis validé ne correspond pas au montant du devis dans IPS. Veuillez refaire valider le devis.",
        'vp_valide_a_envoyer_au_client_et_somme_lignes_inchange' => "Le devis a déjà été validé. Veuillez l'envoyer au client",
        'vp_cloture_a_modifier_et_somme_montant_ips_inferieur_somme_montant_devis' => "Le devis a été modifié dans IPS. Veuillez le refaire valider par le chef d'agence.",
        'pointage_prix_modifier_montant_inchanger' => "Le montant du devis n'a pas été modifié dans IPS.",
        //TODO: ----------------------------
        'no_file_submitted' => "Aucun fichier n'a été soumis.",
        'invalid_filename_format' => 'Le nom du fichier soumis n\'est pas conforme au format attendu. Reçu: %s',
        'filename_number_mismatch' => 'Le numéro de devis dans le nom du fichier (%s) ne correspond pas au devis du formulaire (%s)',
        'lines_modified' => 'Soumission bloquée : Le devis soumis est différent de celui envoyé pour vérification de prix car des lignes ont été ajoutées ou supprimées. Veuillez resoumettre à vérification de prix.',
        'amount_modified' => 'Soumission bloquée : Le devis soumis est différent de celui envoyé pour vérification de prix car le montant total a été modifié. Veuillez resoumettre à vérification de prix.',
        'price_not_modified_in_ips' => 'Le magasin a oublié de modifier le prix dans IPS. Veuillez informer le Parts Manager',
    ];

    // Routes de redirection
    public const REDIRECT_ROUTE = 'devis_magasin_liste';


}
