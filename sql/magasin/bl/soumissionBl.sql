CREATE TABLE bl_soumission
(
    id int IDENTITY(1,1) NOT NULL,
    agence_user varchar(100) NOT NULL,
    service_user varchar(100) NOT NULL,
    utilisateur varchar(100) not null,
    path_fichier_soumis varchar(255) NOT NULL,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_bl_soumission PRIMARY KEY (id)
);


ALTER TABLE bl_soumission ADD type_bl VARCHAR(50)