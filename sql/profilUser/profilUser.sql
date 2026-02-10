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
    ref_profil VARCHAR(10) NOT NULL,
    designation_profil VARCHAR(100) NOT NULL,
    date_creation DATETIME2(0) NOT NULL,
    date_modification DATETIME2(0) NULL,
    societe_id INT NULL,
    CONSTRAINT PK_profil PRIMARY KEY (id)
); 

-- Relation entre les utilisateurs et profil
ALTER TABLE users ADD profil_id INT NULL, CONSTRAINT FK_users_profil FOREIGN KEY (profil_id) REFERENCES profil (id);

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

