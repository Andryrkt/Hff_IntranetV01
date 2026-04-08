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
date_creation DATETIME2(0) NULL,
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

alter table users drop constraint FK_users_role_id;
alter table users drop column role_id;

alter table users drop constraint FK_users_agence_id;
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

-- Supprimer les relations avec users
alter table agence_user drop constraint FK__agence_us__user___2057CCD0;
alter table Demande_Appro drop constraint FK_User_Id;
alter table Demande_Appro drop constraint FK_Validateur_id;
alter table Log_utilisateur drop constraint FK_Log_users;
alter table user_roles drop constraint FK__user_role__user___062DE679;
alter table user_roles drop constraint FK__user_role__user___4924D839;
alter table user_roles drop constraint FK__user_role__user___55F4C372;
alter table user_superieurs drop constraint FK_users_superieurs_user_id;
alter table user_superieurs drop constraint FK_users_superieurs_superieur_id;
alter table users_agence_autoriser drop constraint FK_users_agence_autoriser_user_id;
alter table users_applications drop constraint FK_users_application_user_id;
alter table users_permission drop constraint FK_users_permission_user_id;
alter table users_service drop constraint FK_users_service_user_id;

-- remplir la table applications
INSERT INTO applications
(nom, code_app, date_creation, date_modification, derniere_id, vignette_id)
VALUES
(N'DOCUMENTATION INTERNE', N'DOC', '2025-12-29', '2026-03-17', NULL, NULL),
(N'APPLICATION ADMIN', N'ADMIN', '2026-02-18', '2026-02-18', NULL, NULL),
(N'PNEU OUTIL LUB', N'POL', '2026-02-26', '2026-02-26', NULL, NULL),
(N'LOGISTIQUE (MATERIEL)', N'LOG', '2026-03-17', '2026-03-17', NULL, NULL),
(N'CONTRAT (DOCUMENTATION)', N'CONTRAT', '2026-03-24', NULL, NULL, NULL);

-- Ajout de Accueil et authentification
INSERT INTO Hff_pages
(nom, nom_route, lien, application_id, date_creation, date_modification)
VALUES(N'Accueil', N'profil_acceuil', N'/', NULL, NULL, NULL),
(N'Authentification (identifiants incorrects)', N'security_signin', N'/login', NULL, NULL, NULL);

-- Mise à jour de log_utilisateur pour les anciens Accueil et authentification
UPDATE Log_utilisateur
SET id_page = hp.id
FROM Log_utilisateur lu
JOIN Hff_Pages hp
    ON (
        (lu.id_page = 1 AND hp.nom_route = 'profil_acceuil' AND hp.id > 50) OR (lu.id_page = 2 AND hp.nom_route = 'security_signin' AND hp.id > 50)
    )
WHERE lu.id_page IN (1, 2);

-- Suppression des anciens Accueil et authentification
delete from Hff_pages where id in (1,2);

-- Ajout de nouveaux pages depuis la BDD "HFF_INTRANET_TEST_TEST" ===> "HFF_INTRANET_TEST_2026"
INSERT INTO HFF_INTRANET_TEST_2026.dbo.Hff_pages
(nom, nom_route, lien, application_id, date_creation, date_modification)
select hp.nom, hp.nom_route, hp.lien, a.id, hp.date_creation, hp.date_modification
from HFF_INTRANET_TEST_TEST.dbo.Hff_pages hp
inner join HFF_INTRANET_TEST_TEST.dbo.applications a2 on a2.id=hp.application_id
inner join HFF_INTRANET_TEST_2026.dbo.applications a on a.code_app=a2.code_app 
where hp.id > 58;

-- Mise à jour de application_id pour les anciens pages depuis la BDD "HFF_INTRANET_TEST_TEST" ===> "HFF_INTRANET_TEST_2026"
update HFF_INTRANET_TEST_2026.dbo.Hff_pages
set application_id=a.id
from HFF_INTRANET_TEST_2026.dbo.Hff_pages hptarget
inner JOIN HFF_INTRANET_TEST_TEST.dbo.Hff_pages hpsource on hptarget.nom_route=hpsource.nom_route
inner join HFF_INTRANET_TEST_TEST.dbo.applications a2 on a2.id=hpsource.application_id
inner join HFF_INTRANET_TEST_2026.dbo.applications a on a.code_app=a2.code_app 
where hptarget.id < 57;

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

-- Agence et service + Société --
alter table agences add code_societe varchar(2) null;
alter table agences add societe_id int null;

INSERT INTO societe
(nom, code_societe, date_creation, date_modification)
VALUES
(N'TRAVEL SERVICE', N'TS', '2026-04-01', '2026-04-01'),
(N'SMR ET HR', N'SM', '2026-04-01', '2026-04-01'),
(N'SAMA', N'SA', '2026-04-01', '2026-04-01'),
(N'SOMAVA', N'SV', '2026-04-01', '2026-04-01'),
(N'SOMECA', N'SO', '2026-04-01', '2026-04-01');

INSERT INTO services
(code_service, libelle_service, date_creation, date_modification)
VALUES
(N'C2', N'TRAVEL SERVICE', '2026-04-01', '2026-04-01'),
(N'C3', N'SAMA', '2026-04-01', '2026-04-01'),
(N'C4', N'SMR ET HR', '2026-04-01', '2026-04-01'),
(N'C5', N'SOMAVA', '2026-04-01', '2026-04-01'),
(N'C6', N'NATEMA', '2026-04-01', '2026-04-01');

INSERT INTO agences
(code_agence, libelle_agence, date_creation, date_modification)
VALUES
(N'C2', N'TRAVEL SERVICE', '2026-04-01', '2026-04-01'),
(N'C3', N'SAMA', '2026-04-01', '2026-04-01'),
(N'C4', N'SMR ET HR', '2026-04-01', '2026-04-01'),
(N'C5', N'SOMAVA', '2026-04-01', '2026-04-01'),
(N'C6', N'NATEMA', '2026-04-01', '2026-04-01'),
(N'C7', N'SOMECA', '2026-04-01', '2026-04-01');

create table agence_service_defaut_societe (
    id int identity(1,1) not null,
    id_user int not null,
    code_sage varchar(50) null,
    id_societe int not null,
    code_societe varchar(2) not null,
    code_agence varchar(50) not null,
    code_service varchar(50) not null,
    id_agence int not null,
    id_service int not null,
    constraint fk_agence_service_defaut_societe_user foreign key (id_user) references users(id),
    constraint fk_agence_service_defaut_societe_agence foreign key (id_agence) references agences(id),
    constraint fk_agence_service_defaut_societe_service foreign key (id_service) references services(id),
    constraint fk_agence_service_defaut_societe_societe foreign key (id_societe) references societe(id),
    constraint pk_agence_service_defaut_societe primary key (id)
);

INSERT INTO agence_service_defaut_societe
(id_user, code_sage, id_societe, code_societe, code_agence, code_service, id_agence, id_service)
SELECT
    u.id                    AS id_user,
    asi.service_sage_paie   AS code_sage,
    soc.id                  AS id_societe,
    soc.code_societe        AS code_societe,
    asi.agence_ips          AS code_agence,   
    asi.service_ips         AS code_service,
    a.id                    AS id_agence,
    s.id                    AS id_service
FROM users u
LEFT JOIN Agence_Service_Irium asi ON asi.id = u.agence_utilisateur
JOIN agences a on a.code_agence=asi.agence_ips 
JOIN services s on s.code_service=asi.service_ips
JOIN societe soc on soc.code_societe=asi.societe_ios
;