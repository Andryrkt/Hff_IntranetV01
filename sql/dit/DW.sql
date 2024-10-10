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
    statut_or NVARCHAR(50),
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