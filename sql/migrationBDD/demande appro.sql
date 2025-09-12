
-- da_afficher
delete from HFF_INTRANET_TEST_TEST.dbo.da_afficher;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.da_afficher', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.da_afficher
(numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut_dal, statut_or, statut_cde, objet_dal, detail_dal, num_ligne, num_ligne_tableau, qte_dem, qte_dispo, qte_en_attent, qte_livrer, art_constp, art_refp, art_desi, code_fams1, art_fams1, code_fams2, art_fams2, numero_fournisseur, nom_fournisseur, date_fin_souhaitee_l, commentaire, prix_unitaire, total, est_fiche_technique, nom_fiche_technique, pj_fiche_technique, pj_new_ate, pj_proposition_appro, pj_bc, catalogue, date_livraison_prevue, valide_par, numero_version, niveau_urgence, jours_dispo, demandeur, achat_direct, position_bc, date_planning_or, or_a_resoumettre, numero_ligne_ips, date_demande, est_dalr, date_creation, date_modification, bc_envoyer_fournisseur, agence_emmetteur_id, Service_emmetteur_id, agence_debiteur_id, service_debiteur_id, deleted_by, deleted, numero_version_or_maj_statut, date_derniere_bav, date_maj_statut_or)
select numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut_dal, statut_or, statut_cde, objet_dal, detail_dal, num_ligne, num_ligne_tableau, qte_dem, qte_dispo, qte_en_attent, qte_livrer, art_constp, art_refp, art_desi, code_fams1, art_fams1, code_fams2, art_fams2, numero_fournisseur, nom_fournisseur, date_fin_souhaitee_l, commentaire, prix_unitaire, total, est_fiche_technique, nom_fiche_technique, pj_fiche_technique, pj_new_ate, pj_proposition_appro, pj_bc, catalogue, date_livraison_prevue, valide_par, numero_version, niveau_urgence, jours_dispo, demandeur, achat_direct, position_bc, date_planning_or, or_a_resoumettre, numero_ligne_ips, date_demande, est_dalr, date_creation, date_modification, bc_envoyer_fournisseur, agence_emmetteur_id, Service_emmetteur_id, agence_debiteur_id, service_debiteur_id, deleted_by, deleted, numero_version_or_maj_statut, date_derniere_bav, date_maj_statut_or
FROM HFF_INTRANET.dbo.da_afficher;


-- demande_appro
delete from HFF_INTRANET_TEST_TEST.dbo.Demande_Appro;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.Demande_Appro', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.Demande_Appro
(numero_demande_appro, demandeur, achat_direct, numero_demande_dit, objet_dal, detail_dal, agence_emmetteur_id, Service_emmetteur_id, agence_service_emmeteur, agence_debiteur_id, service_debiteur_id, agence_service_debiteur, date_creation, date_heure_fin_souhaitee, statut_dal, date_modification, id_Materiel, statut_email, est_validee, valide_par, nom_fichier_bav, user_id, validateur_id, niveau_urgence)
select numero_demande_appro, demandeur, achat_direct, numero_demande_dit, objet_dal, detail_dal, agence_emmetteur_id, Service_emmetteur_id, agence_service_emmeteur, agence_debiteur_id, service_debiteur_id, agence_service_debiteur, date_creation, date_heure_fin_souhaitee, statut_dal, date_modification, id_Materiel, statut_email, est_validee, valide_par, nom_fichier_bav, user_id, validateur_id, niveau_urgence
FROM HFF_INTRANET.dbo.Demande_Appro;


-- demande_appro_l
delete from HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L
(numero_demande_appro, num_ligne, qte_dem, qte_dispo, art_constp, art_refp, art_desi, art_fams1, art_fams2, numero_fournisseur, nom_fournisseur, date_fin_souhaitee_l, commentaire, statut_dal, catalogue, demande_appro_id, code_fams1, code_fams2, est_validee, date_creation, date_modification, est_modifier, valide_par, numero_version, edit, prix_unitaire, deleted, numero_dit, est_fiche_technique, nom_fiche_technique, jours_dispo, file_names, choix, demandeur, date_livraison_prevue)
select numero_demande_appro, num_ligne, qte_dem, qte_dispo, art_constp, art_refp, art_desi, art_fams1, art_fams2, numero_fournisseur, nom_fournisseur, date_fin_souhaitee_l, commentaire, statut_dal, catalogue, demande_appro_id, code_fams1, code_fams2, est_validee, date_creation, date_modification, est_modifier, valide_par, numero_version, edit, prix_unitaire, deleted, numero_dit, est_fiche_technique, nom_fiche_technique, jours_dispo, file_names, choix, demandeur, date_livraison_prevue
FROM HFF_INTRANET.dbo.Demande_Appro_L;


-- demande_appro_l_r
delete from HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L_R;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L_R', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.Demande_Appro_L_R
(numero_demande_appro, num_ligne_dem, qte_dem, qte_dispo, art_constp, art_refp, art_desi, art_fams1, art_fams2, numero_fournisseur, nom_fournisseur, demande_appro_l_id, PU, total, conditionnement, motif, est_validee, date_creation, date_modification, num_ligne_tableau, choix, code_fams1, code_fams2, valide_par, deleted, est_fiche_technique, nom_fiche_technique, date_fin_souhaitee_l, file_names, statut_dal, jours_dispo, numero_demande_dit, date_livraison_prevue)
select numero_demande_appro, num_ligne_dem, qte_dem, qte_dispo, art_constp, art_refp, art_desi, art_fams1, art_fams2, numero_fournisseur, nom_fournisseur, demande_appro_l_id, PU, total, conditionnement, motif, est_validee, date_creation, date_modification, num_ligne_tableau, choix, code_fams1, code_fams2, valide_par, deleted, est_fiche_technique, nom_fiche_technique, date_fin_souhaitee_l, file_names, statut_dal, jours_dispo, numero_demande_dit, date_livraison_prevue
FROM HFF_INTRANET.dbo.Demande_Appro_L_R;


-- da_observation
delete from HFF_INTRANET_TEST_TEST.dbo.da_observation;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.da_observation', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.da_observation
(observation, numero_da, utilisateur, date_creation, date_modification)
select observation, numero_da, utilisateur, date_creation, date_modification
FROM HFF_INTRANET.dbo.da_observation;


-- da_soumission_bc
delete from HFF_INTRANET_TEST_TEST.dbo.da_soumission_bc;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.da_soumission_bc', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.da_soumission_bc
(numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut, piece_joint1, utilisateur, date_creation, date_modification, numero_version, bc_envoyer_fournisseur)
select numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut, piece_joint1, utilisateur, date_creation, date_modification, numero_version, bc_envoyer_fournisseur
FROM HFF_INTRANET.dbo.da_soumission_bc;


-- da_soumis_a_validation
delete from HFF_INTRANET_TEST_TEST.dbo.da_soumis_a_validation;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.da_soumis_a_validation', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.da_soumis_a_validation
(numero_demande_appro, numero_version, statut, date_soumission, date_validation, date_creation, date_modification, utilisateur)
select numero_demande_appro, numero_version, statut, date_soumission, date_validation, date_creation, date_modification, utilisateur
FROM HFF_INTRANET.dbo.da_soumis_a_validation;


-- da_soumission_facture_bl
delete from HFF_INTRANET_TEST_TEST.dbo.da_soumission_facture_bl;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.da_soumission_facture_bl', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.da_soumission_facture_bl
(numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut, piece_joint1, utilisateur, numero_version, date_creation, date_modification)
select numero_demande_appro, numero_demande_dit, numero_or, numero_cde, statut, piece_joint1, utilisateur, numero_version, date_creation, date_modification
FROM HFF_INTRANET.dbo.da_soumission_facture_bl;


-- ors_soumis_a_validation
delete from HFF_INTRANET_TEST_TEST.dbo.ors_soumis_a_validation o ;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.ors_soumis_a_validation', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.ors_soumis_a_validation
(numeroOR, numeroItv, nombreLigneItv, montantItv, numeroVersion, montantPiece, montantMo, montantAchatLocaux, montantFraisDivers, montantLubrifiants, libellelItv, dateSoumission, heureSoumission, statut, migration, numeroDIT)
select numeroOR, numeroItv, nombreLigneItv, montantItv, numeroVersion, montantPiece, montantMo, montantAchatLocaux, montantFraisDivers, montantLubrifiants, libellelItv, dateSoumission, heureSoumission, statut, migration, numeroDIT
FROM HFF_INTRANET.dbo.ors_soumis_a_validation;


-- demande_intervention
delete from HFF_INTRANET_TEST_TEST.dbo.demande_intervention;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.demande_intervention', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.demande_intervention
(numero_demande_dit, type_document, code_societe, type_reparation, reparation_realise, categorie_demande, internet_externe, agence_service_debiteur, agence_service_emmeteur, nom_client, numero_telephone, date_or, heure_or, date_prevue_travaux, demande_devis, id_niveau_urgence, avis_recouvrement, client_sous_contrat, objet_demande, detail_demande, livraison_partiel, ID_Materiel, mail_demandeur, date_demande, heure_demande, date_cloture, heure_cloture, piece_joint1, piece_joint2, piece_joint, utilisateur_demandeur, observations, id_statut_demande, date_validation, heure_validation, numero_client, libelle_client, date_fin_souhaite, numero_or, observation_direction_technique, observation_devis, numero_devis_rattache, date_soumission_devis, statut_devis, date_validation_devis, id_service_intervenant, date_devis_fin_probable, date_fin_estimation_travaux, code_section, mas_ate, code_ate, secteur, utilisateur_intervenant, KM_machine, Heure_machine, date_devis_rattache, section_affectee, statut_or, statut_commande, date_validation_or, agence_emetteur_id, service_emetteur_id, agence_debiteur_id, service_debiteur_id, section_support_1, section_support_2, section_support_3, migration, etat_facturation, ri, mail_client, num_migr, a_annuler, date_annulation, numero_demande_dit_avoir, numero_demande_dit_refacturation, dit_avoir, dit_refacturation)
select numero_demande_dit, type_document, code_societe, type_reparation, reparation_realise, categorie_demande, internet_externe, agence_service_debiteur, agence_service_emmeteur, nom_client, numero_telephone, date_or, heure_or, date_prevue_travaux, demande_devis, id_niveau_urgence, avis_recouvrement, client_sous_contrat, objet_demande, detail_demande, livraison_partiel, ID_Materiel, mail_demandeur, date_demande, heure_demande, date_cloture, heure_cloture, piece_joint1, piece_joint2, piece_joint, utilisateur_demandeur, observations, id_statut_demande, date_validation, heure_validation, numero_client, libelle_client, date_fin_souhaite, numero_or, observation_direction_technique, observation_devis, numero_devis_rattache, date_soumission_devis, statut_devis, date_validation_devis, id_service_intervenant, date_devis_fin_probable, date_fin_estimation_travaux, code_section, mas_ate, code_ate, secteur, utilisateur_intervenant, KM_machine, Heure_machine, date_devis_rattache, section_affectee, statut_or, statut_commande, date_validation_or, agence_emetteur_id, service_emetteur_id, agence_debiteur_id, service_debiteur_id, section_support_1, section_support_2, section_support_3, migration, etat_facturation, ri, mail_client, num_migr, a_annuler, date_annulation, numero_demande_dit_avoir, numero_demande_dit_refacturation, dit_avoir, dit_refacturation
FROM HFF_INTRANET.dbo.demande_intervention;


-- mise à jour ID des DA et id des DIT dans la table da_afficher
update da
SET da.demande_appro_id = d.id, da.dit_id = di.id
FROM HFF_INTRANET_TEST_TEST.dbo.da_afficher da
JOIN HFF_INTRANET_TEST_TEST.dbo.Demande_Appro d ON da.numero_demande_appro = d.numero_demande_appro 
JOIN HFF_INTRANET_TEST_TEST.dbo.demande_intervention di ON da.numero_demande_dit = di.numero_demande_dit;
