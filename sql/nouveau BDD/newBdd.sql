
ALTER TABLE agences DROP COLUMN id;
ALTER TABLE agences ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE agences ADD CONSTRAINT PK_agences PRIMARY KEY (id);

ALTER TABLE applications DROP COLUMN id;
ALTER TABLE applications ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE applications ADD CONSTRAINT PK_applications PRIMARY KEY (id);

ALTER TABLE Casier_Materiels DROP COLUMN id;
ALTER TABLE Casier_Materiels ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Casier_Materiels ADD CONSTRAINT PK_Casier_Materiels PRIMARY KEY (id);

ALTER TABLE Casier_Materiels_Temporaire DROP COLUMN id;
ALTER TABLE Casier_Materiels_Temporaire ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Casier_Materiels_Temporaire ADD CONSTRAINT PK_Casier_Materiels_Temporaire PRIMARY KEY (id);

ALTER TABLE categorie_ate_app DROP COLUMN id;
ALTER TABLE categorie_ate_app ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE categorie_ate_app ADD CONSTRAINT PK_categorie_ate_app PRIMARY KEY (id);

ALTER TABLE catg DROP COLUMN id;
ALTER TABLE catg ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE catg ADD CONSTRAINT PK_catg PRIMARY KEY (id);

ALTER TABLE demande_intervention DROP COLUMN id;
ALTER TABLE demande_intervention ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE demande_intervention ADD CONSTRAINT PK_demande_intervention PRIMARY KEY (id);

ALTER TABLE demande_intervention_migration DROP COLUMN id;
ALTER TABLE demande_intervention_migration ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE demande_intervention_migration ADD CONSTRAINT PK_demande_intervention_migration PRIMARY KEY (id);

ALTER TABLE Demande_Mouvement_Materiel DROP COLUMN ID_Demande_Mouvement_Materiel;
ALTER TABLE Demande_Mouvement_Materiel ADD ID_Demande_Mouvement_Materiel INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Demande_Mouvement_Materiel ADD CONSTRAINT PK_Demande_Mouvement_Materiel PRIMARY KEY (ID_Demande_Mouvement_Materiel);

ALTER TABLE Demande_ordre_mission DROP COLUMN ID_Demande_Ordre_Mission;
ALTER TABLE Demande_ordre_mission ADD ID_Demande_Ordre_Mission INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Demande_ordre_mission ADD CONSTRAINT PK_Demande_ordre_mission PRIMARY KEY (ID_Demande_Ordre_Mission);

ALTER TABLE devis_soumis_a_validation DROP COLUMN id;
ALTER TABLE devis_soumis_a_validation ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE devis_soumis_a_validation ADD CONSTRAINT PK_devis_soumis_a_validation PRIMARY KEY (id);

ALTER TABLE facture_soumis_a_validation DROP COLUMN id;
ALTER TABLE facture_soumis_a_validation ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE facture_soumis_a_validation ADD CONSTRAINT PK_facture_soumis_a_validation PRIMARY KEY (id);

ALTER TABLE fonctions DROP COLUMN id;
ALTER TABLE fonctions ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE fonctions ADD CONSTRAINT PK_fonctions PRIMARY KEY (id);

ALTER TABLE Hff_pages DROP COLUMN id;
ALTER TABLE Hff_pages ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Hff_pages ADD CONSTRAINT PK_Hff_pages PRIMARY KEY (id);

ALTER TABLE historique_operation_document DROP COLUMN id;
ALTER TABLE historique_operation_document ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE historique_operation_document ADD CONSTRAINT PK_historique_operation_document PRIMARY KEY (id);

ALTER TABLE idemnite DROP COLUMN id;
ALTER TABLE idemnite ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE idemnite ADD CONSTRAINT PK_idemnite PRIMARY KEY (id);

ALTER TABLE Idemnity DROP COLUMN ID_Idemnity;
ALTER TABLE Idemnity ADD ID_Idemnity INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Idemnity ADD CONSTRAINT PK_Idemnity PRIMARY KEY (ID_Idemnity);

ALTER TABLE Log_utilisateur DROP COLUMN id;
ALTER TABLE Log_utilisateur ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Log_utilisateur ADD CONSTRAINT PK_Log_utilisateur PRIMARY KEY (id);

ALTER TABLE mise_a_jour DROP COLUMN id_update;
ALTER TABLE mise_a_jour ADD id_update INT IDENTITY(1,1) NOT NULL;
ALTER TABLE mise_a_jour ADD CONSTRAINT PK_mise_a_jour PRIMARY KEY (id_update);

ALTER TABLE ors_soumis_a_validation DROP COLUMN id;
ALTER TABLE ors_soumis_a_validation ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE ors_soumis_a_validation ADD CONSTRAINT PK_ors_soumis_a_validation PRIMARY KEY (id);

ALTER TABLE permissions DROP COLUMN id;
ALTER TABLE permissions ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE permissions ADD CONSTRAINT PK_permissions PRIMARY KEY (id);

ALTER TABLE Personnel DROP COLUMN id;
ALTER TABLE Personnel ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Personnel ADD CONSTRAINT PK_Personnel PRIMARY KEY (id);

ALTER TABLE Profil_User DROP COLUMN id;
ALTER TABLE Profil_User ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Profil_User ADD CONSTRAINT PK_Profil_User PRIMARY KEY (id);

ALTER TABLE ri_soumis_a_validation DROP COLUMN id;
ALTER TABLE ri_soumis_a_validation ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE ri_soumis_a_validation ADD CONSTRAINT PK_ri_soumis_a_validation PRIMARY KEY (id);

ALTER TABLE rmq DROP COLUMN id;
ALTER TABLE rmq ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE rmq ADD CONSTRAINT PK_rmq PRIMARY KEY (id);

ALTER TABLE roles DROP COLUMN id;
ALTER TABLE roles ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE roles ADD CONSTRAINT PK_roles PRIMARY KEY (id);

ALTER TABLE secteur DROP COLUMN id;
ALTER TABLE secteur ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE secteur ADD CONSTRAINT PK_secteur PRIMARY KEY (id);

ALTER TABLE services DROP COLUMN id;
ALTER TABLE services ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE services ADD CONSTRAINT PK_services PRIMARY KEY (id);

ALTER TABLE site DROP COLUMN id;
ALTER TABLE site ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE site ADD CONSTRAINT PK_site PRIMARY KEY (id);

ALTER TABLE societe DROP COLUMN id;
ALTER TABLE societe ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE societe ADD CONSTRAINT PK_societe PRIMARY KEY (id);

ALTER TABLE Sous_type_document DROP COLUMN ID_Sous_Type_Document;
ALTER TABLE Sous_type_document ADD ID_Sous_Type_Document INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Sous_type_document ADD CONSTRAINT PK_Sous_type_document PRIMARY KEY (ID_Sous_Type_Document);

ALTER TABLE Statut_demande DROP COLUMN ID_Statut_Demande;
ALTER TABLE Statut_demande ADD ID_Statut_Demande INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Statut_demande ADD CONSTRAINT PK_Statut_demande PRIMARY KEY (ID_Statut_Demande);

ALTER TABLE type_document DROP COLUMN id;
ALTER TABLE type_document ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE type_document ADD CONSTRAINT PK_type_document PRIMARY KEY (id);

ALTER TABLE Type_Mouvement DROP COLUMN ID_Type_Mouvement;
ALTER TABLE Type_Mouvement ADD ID_Type_Mouvement INT IDENTITY(1,1) NOT NULL;
ALTER TABLE Type_Mouvement ADD CONSTRAINT PK_Type_Mouvement PRIMARY KEY (ID_Type_Mouvement);

ALTER TABLE type_operation DROP COLUMN id;
ALTER TABLE type_operation ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE type_operation ADD CONSTRAINT PK_type_operation PRIMARY KEY (id);

ALTER TABLE users DROP COLUMN id;
ALTER TABLE users ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE users ADD CONSTRAINT PK_users PRIMARY KEY (id);

ALTER TABLE wor_niveau_urgence DROP COLUMN id;
ALTER TABLE wor_niveau_urgence ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE wor_niveau_urgence ADD CONSTRAINT PK_wor_niveau_urgence PRIMARY KEY (id);

ALTER TABLE wor_type_document DROP COLUMN id;
ALTER TABLE wor_type_document ADD id INT IDENTITY(1,1) NOT NULL;
ALTER TABLE wor_type_document ADD CONSTRAINT PK_wor_type_document PRIMARY KEY (id);



select * from TKI_CATEGORIE

select * from TKI_SOUS_CATEGORIE

select * from TKI_Autres_Categorie

select * from categorie_souscategorie

select * from souscategorie_autrescategories

SELECT * INTO TKI_CATEGORIE FROM HFF_INTRANET.dbo.TKI_CATEGORIE
SELECT * INTO TKI_SOUS_CATEGORIE FROM HFF_INTRANET.dbo.TKI_SOUS_CATEGORIE
SELECT * INTO TKI_Autres_Categorie FROM HFF_INTRANET.dbo.TKI_Autres_Categorie
SELECT * INTO categorie_souscategorie FROM HFF_INTRANET.dbo.categorie_souscategorie
SELECT * INTO souscategorie_autrescategories FROM HFF_INTRANET.dbo.souscategorie_autrescategories
