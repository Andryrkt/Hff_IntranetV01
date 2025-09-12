CREATE TABLE DW_Rapport_Intervention (
    id_ri INT,
    numero_ri VARCHAR(50),
    id_tiroir VARCHAR(100),
    numero_or VARCHAR(8),
    date_creation DATE,
    heure_creation TIME,
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    total_page INT,
    extension_fichier VARCHAR(50),
    taille_fichier INT,
    path VARCHAR(255)
);

select * from DW_Rapport_Intervention

CREATE TABLE DW_Facture (
    id_fac INT,
    numero_fac VARCHAR(8),
    id_tiroir VARCHAR(100),
    numero_or VARCHAR(8),
    date_creation DATE,
    heure_creation TIME,
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    extension_fichier VARCHAR(50),
    total_page INT,
    taille_fichier INT,
    path VARCHAR(255)
);

CREATE TABLE DW_Commande (
    id_cde INT,
    numero_cde VARCHAR(8),
    id_tiroir VARCHAR(100),
    numero_or VARCHAR(8),
    date_creation DATE,
    heure_creation TIME,
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    extension_fichier VARCHAR(50),
    total_page INT,
    taille_fichier INT,
    path VARCHAR(255)
);

CREATE TABLE DW_Ordre_De_Reparation (
    id_or INT,
    numero_or VARCHAR(8),
    id_tiroir VARCHAR(100),
    numero_dit VARCHAR(11),
    numero_version INT,
    date_creation DATE,
    heure_creation TIME,
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    statut_or NVARCHAR (50),
    extension_fichier VARCHAR(50),
    total_page INT,
    taille_fichier INT,
    path VARCHAR(255)
);

CREATE TABLE DW_Tiroir (
    id_tiroir VARCHAR(100) PRIMARY KEY,
    designation_tiroir VARCHAR(255)
);

select * from DW_Tiroir

select * from DW_Demande_Intervention

select * from DW_Commande

drop table DW_Demande_Intervention

CREATE TABLE DW_Demande_Intervention (
    id_dit INT,
    numero_dit VARCHAR(11),
    id_tiroir VARCHAR(100),
    date_creation DATE,
    heure_creation TIME,
    date_derniere_modification DATE,
    heure_derniere_modification TIME,
    extension_fichier VARCHAR(50),
    type_reparation VARCHAR(100),
    id_materiel VARCHAR(11),
    numero_parc VARCHAR(50),
    numero_serie VARCHAR(100),
    designation_materiel VARCHAR(255),
    total_page INT,
    taille_fichier INT,
    path VARCHAR(255)
);

select * from DW_Facture

select * from DW_Rapport_Intervention

select * from DW_Demande_Intervention

select * from DW_Tiroir

select * from DW_Ordre_De_Reparation

select * from DW_Rapport_intervention

select * from DW_Commande

select * from DW_Facture

EXEC sp_rename 'DW_Demande_Intervention.id', 'id_dit', 'COLUMN';

EXEC sp_rename 'DW_Tiroir.id', 'id_tiroir', 'COLUMN';

EXEC sp_rename 'DW_Ordre_De_Reparation.id', 'id_or', 'COLUMN';

EXEC sp_rename 'DW_Rapport_intervention.id', 'id_ri', 'COLUMN';

EXEC sp_rename 'DW_Commande.id', 'id_cde', 'COLUMN';

EXEC sp_rename 'DW_Facture.id', 'id_fac', 'COLUMN';

ALTER TABLE DW_Demande_Intervention
ADD id INT IDENTITY (1, 1)
ALTER TABLE DW_Tiroir
ADD id INT IDENTITY (1, 1)
ALTER TABLE DW_Ordre_De_Reparation
ADD id INT IDENTITY (1, 1)
ALTER TABLE DW_Rapport_intervention
ADD id INT IDENTITY (1, 1)
ALTER TABLE DW_Commande
ADD id INT IDENTITY (1, 1)
ALTER TABLE DW_Facture
ADD id INT IDENTITY (1, 1)

ALTER TABLE DW_Facture ADD numero_or VARCHAR(8)

ALTER TABLE DW_Demande_Intervention ADD numero_dit_avoir VARCHAR(11)
ALTER TABLE DW_Demande_Intervention ADD numero_dit_refacturation VARCHAR(11)
ALTER TABLE DW_Demande_Intervention ADD dit_avoir bit DEFAULT 0
ALTER TABLE DW_Demande_Intervention ADD dit_refacturation bit DEFAULT 0


UPDATE DW_Demande_Intervention SET dit_avoir = 0
UPDATE DW_Demande_Intervention SET dit_refacturation = 0