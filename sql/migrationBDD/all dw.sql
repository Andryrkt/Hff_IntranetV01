delete from HFF_INTRANET_TEST_TEST.dbo.DW_BC_Appro;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_BC_Appro', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_BC_Appro
(id_bca, numero_bca, numero_or, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_da, numero_version)
select id_bca, numero_bca, numero_or, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_da, numero_version
from HFF_INTRANET.dbo.DW_BC_Appro;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_BC_Client;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_BC_Client', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_BC_Client
(id_bc, numero_bc, nom_document, id_tiroir, numero_dit, numero_devis, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_bc, extension_fichier, total_page, taille_fichier, [path])
select id_bc, numero_bc, nom_document, id_tiroir, numero_dit, numero_devis, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_bc, extension_fichier, total_page, taille_fichier, [path]
from HFF_INTRANET.dbo.DW_BC_Client;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Commande;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Commande', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Commande
(id_cde, numero_cde, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, total_page, taille_fichier, [path])
select id_cde, numero_cde, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, total_page, taille_fichier, [path]
from HFF_INTRANET.dbo.DW_Commande;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Demande_Intervention;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Demande_Intervention', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Demande_Intervention
(id_dit, numero_dit, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, type_reparation, id_materiel, numero_parc, numero_serie, designation_materiel, total_page, taille_fichier, [path], numero_cli, numero_dit_avoir, numero_dit_refacturation, dit_avoir, dit_refacturation)
select id_dit, numero_dit, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, type_reparation, id_materiel, numero_parc, numero_serie, designation_materiel, total_page, taille_fichier, [path], numero_cli, numero_dit_avoir, numero_dit_refacturation, dit_avoir, dit_refacturation
from HFF_INTRANET.dbo.DW_Demande_Intervention;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Devis;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Devis', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Devis
(id_devis, numero_devis, nom_document, id_tiroir, numero_dit, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_devis, extension_fichier, total_page, taille_fichier, [path])
select id_devis, numero_devis, nom_document, id_tiroir, numero_dit, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_devis, extension_fichier, total_page, taille_fichier, [path]
from HFF_INTRANET.dbo.DW_Devis;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_FAC_BL;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_FAC_BL', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_FAC_BL
(id_fac_bl, numero_fac_bl, numero_da, numero_or, id_tiroir, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path])
select id_fac_bl, numero_fac_bl, numero_da, numero_or, id_tiroir, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path]
from HFF_INTRANET.dbo.DW_FAC_BL;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Facture;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Facture', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Facture
(id_fac, numero_fac, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, total_page, taille_fichier, [path])
select id_fac, numero_fac, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, total_page, taille_fichier, [path]
from HFF_INTRANET.dbo.DW_Facture;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Ordre_De_Reparation;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Ordre_De_Reparation', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Ordre_De_Reparation
(id_or, numero_or, id_tiroir, numero_dit, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_or, extension_fichier, total_page, taille_fichier, [path])
select id_or, numero_or, id_tiroir, numero_dit, numero_version, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, statut_or, extension_fichier, total_page, taille_fichier, [path]
from HFF_INTRANET.dbo.DW_Ordre_De_Reparation;


delete from HFF_INTRANET_TEST_TEST.dbo.DW_Rapport_Intervention;

DBCC CHECKIDENT ('HFF_INTRANET_TEST_TEST.dbo.DW_Rapport_Intervention', RESEED, 0);

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_Rapport_Intervention
(id_ri, numero_ri, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, taille_fichier, [path], total_page)
select id_ri, numero_ri, id_tiroir, numero_or, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, extension_fichier, taille_fichier, [path], total_page
from HFF_INTRANET.dbo.DW_Rapport_Intervention;

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_DA_Direct
(id_da_dir, numero_da_dir, statut_da_dir, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_version)
select id_da_dir, numero_da_dir, statut_da_dir, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_version from HFF_INTRANET.dbo.DW_DA_Direct;

INSERT INTO HFF_INTRANET_TEST_TEST.dbo.DW_DA_Reappro
(id_da_reap, numero_da_reap, statut_da_reap, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_version)
SELECT id_da_reap, numero_da_reap, statut_da_reap, id_tiroir, date_creation, heure_creation, date_derniere_modification, heure_derniere_modification, total_page, taille_fichier, extension_fichier, [path], numero_version from HFF_INTRANET.dbo.DW_DA_Reappro;

