-- Table pour stocker les vignettes
CREATE TABLE vignette (
    id INT IDENTITY(1,1) NOT NULL,
    ref_vignette VARCHAR(10) NOT NULL,
    nom_vignette VARCHAR(100) NOT NULL,
    date_creation DATETIME2(0) NOT NULL,
    date_modification DATETIME2(0) NULL,
    CONSTRAINT PK_vignette PRIMARY KEY (id)
);

-- Relation entre les applications et les vignettes
ALTER TABLE applications ADD vignette_id INT NULL, CONSTRAINT FK_applications_vignette FOREIGN KEY (vignette_id) REFERENCES vignette (id);

-- Augmentation de la longueur de code_app
ALTER TABLE applications ALTER COLUMN code_app VARCHAR(10) NULL; 

-- Relation entre les pages et les applications
ALTER TABLE Hff_pages 
ADD application_id INT NULL,
date_creation DATETIME2(0) NOT NULL,
date_modification DATETIME2(0) NULL,
CONSTRAINT FK_pages_applications FOREIGN KEY (application_id) REFERENCES applications (id);

-- Table pour stocker les profils
CREATE TABLE profil (
    id INT IDENTITY(1,1) NOT NULL,
    ref_profil VARCHAR(255) NOT NULL,
    designation_profil VARCHAR(255) NOT NULL,
    date_creation DATETIME2(0) NOT NULL,
    date_modification DATETIME2(0) NULL,
    societe_id INT NULL,
    CONSTRAINT PK_profil PRIMARY KEY (id)
); 

-- Table de relation entre les applications, profil
CREATE TABLE application_profil (
    id INT IDENTITY(1,1) PRIMARY KEY,
    application_id INT NOT NULL,
    profil_id INT NOT NULL,
    UNIQUE (application_id, profil_id), 
    FOREIGN KEY (application_id) REFERENCES applications(id),
    FOREIGN KEY (profil_id) REFERENCES profil(id)
);

-- Supprimer la contrainte sur agence_service
ALTER TABLE agence_service DROP CONSTRAINT PK_agence_service; -- ! Pour PROD UNIQUEMENT

-- Ajout de la contrainte sur agence_service
ALTER TABLE agence_service 
ADD id INT IDENTITY(1,1) PRIMARY KEY, 
UNIQUE (agence_id, service_id), 
FOREIGN KEY (agence_id) REFERENCES agences(id), 
FOREIGN KEY (service_id) REFERENCES services(id);

-- Relation entre les applications - profil et agence - service
CREATE TABLE application_profil_agence_service (
    id INT IDENTITY(1,1) PRIMARY KEY,
    application_profil_id INT NOT NULL,
    agence_service_id INT NOT NULL,
    UNIQUE (application_profil_id, agence_service_id), 
    FOREIGN KEY (application_profil_id) REFERENCES application_profil(id),
    FOREIGN KEY (agence_service_id) REFERENCES agence_service(id)
);

/** TABLE RELATION ENTRE L'UTILISATEUR ET LE PROFIL */
CREATE TABLE users_profils (
    user_id INT,
    profil_id INT,
    CONSTRAINT PK_users_profils PRIMARY KEY (user_id, profil_id),
    CONSTRAINT FK_users_profils_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_profils_profil_id FOREIGN KEY (profil_id) REFERENCES profil (id)
);

alter table users drop column role_id;
alter table users drop column agence_id;
alter table users drop column superieurs;
alter table users drop column fonction;

alter table users add code_agence_user varchar(50) NULL;
alter table users add code_service_user varchar(50) NULL;
alter table users add id_agence_user int NULL;
alter table users add id_service_user int NULL;
alter table users add code_sage varchar(50) NULL;

CREATE TABLE application_profil_page (
    id                                     INT IDENTITY(1,1) NOT NULL,
    application_profil_id                  INT               NOT NULL,
    page_id                                INT               NOT NULL,
    peut_voir                              bit               NOT NULL DEFAULT 1,
    peut_voir_liste_avec_debiteur          bit               NOT NULL DEFAULT 0,
    peut_multi_succursale                  bit               NOT NULL DEFAULT 0,
    peut_supprimer                         bit               NOT NULL DEFAULT 0,
    peut_exporter                          bit               NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE (application_profil_id, page_id),
    CONSTRAINT fk_app_profil_page_ap FOREIGN KEY (application_profil_id) REFERENCES application_profil (id),
    CONSTRAINT fk_app_profil_page_page FOREIGN KEY (page_id) REFERENCES Hff_pages (id)
);

UPDATE users set 
    code_agence_user=asi.agence_ips, 
    code_service_user=asi.service_ips,
    code_sage=asi.service_sage_paie,
    id_agence_user=a.id,
    id_service_user=s.id
from users u
INNER JOIN Agence_Service_Irium asi on asi.id=u.agence_utilisateur
inner join agences a on a.code_agence=asi.agence_ips
inner join services s on s.code_service=asi.service_ips;

alter table devis_soumis_a_validation_neg add code_societe varchar(2) null;

update devis_soumis_a_validation_neg set code_societe='HF';

alter table pointage_relance drop column societe; /** à executer en TEST et PROD */
alter table pointage_relance add code_societe varchar(2) null;

update pointage_relance set code_societe='HF';

alter table demande_intervention drop column societe; /** à executer en TEST et PROD */

update demande_intervention set code_societe='HF';

alter table devis_soumis_a_validation add code_societe varchar(2) null;

update devis_soumis_a_validation set code_societe='HF';

alter table facture_soumis_a_validation add code_societe varchar(2) null;

update facture_soumis_a_validation set code_societe='HF';

alter table ors_soumis_a_validation drop column societe; /** à executer en TEST et PROD */

alter table ors_soumis_a_validation add code_societe varchar(2) null;

update ors_soumis_a_validation set code_societe='HF';

alter table ri_soumis_a_validation add code_societe varchar(2) null;

update ri_soumis_a_validation set code_societe='HF';

alter table Demande_Appro add code_societe varchar(2) null;

update Demande_Appro set code_societe='HF';

alter table da_afficher add code_societe varchar(2) null;

update da_afficher set code_societe='HF';

alter table bc_soumis add code_societe varchar(2) null;

update bc_soumis set code_societe='HF';

alter table Demande_Appro_P add code_societe varchar(2) null;

update Demande_Appro_P set code_societe='HF';

alter table da_soumission_bc add code_societe varchar(2) null;

update da_soumission_bc set code_societe='HF';

alter table da_soumission_facture_bl add code_societe varchar(2) null;

update da_soumission_facture_bl set code_societe='HF';

alter table Demande_ordre_mission add code_societe varchar(2) null;

update Demande_ordre_mission set code_societe='HF';

alter table Demande_ordre_mission_tp add code_societe varchar(2) null;

update Demande_ordre_mission_tp set code_societe='HF';

alter table Demande_Mouvement_Materiel add code_societe varchar(2) null;

update Demande_Mouvement_Materiel set code_societe='HF';

alter table Casier_Materiels_Temporaire add code_societe varchar(2) null;

update Casier_Materiels_Temporaire set code_societe='HF';

alter table Casier_Materiels add code_societe varchar(2) null;

update Casier_Materiels set code_societe='HF';
