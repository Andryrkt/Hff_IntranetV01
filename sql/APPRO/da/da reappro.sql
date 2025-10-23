CREATE TABLE da_article_reappro 
(
    id int IDENTITY(1,1) NOT NULL,
    art_constp varchar(3) NOT NULL,
    art_refp varchar(50) NOT NULL,
    art_desi varchar(100) NOT NULL,
    qte_validee_appro VARCHAR(100) NOT NULL,
    art_pu VARCHAR(100) NOT NULL,
    code_agence varchar(2) NULL,
    code_service varchar(3) NULL,
    date_creation DATETIME2(0) NULL,
    date_modification DATETIME2(0) NULL,
    CONSTRAINT PK_da_article_reappro PRIMARY KEY (id)
);

ALTER TABLE Demande_Appro ADD da_type_id INT;
ALTER TABLE da_afficher ADD da_type_id INT;

update Demande_Appro set da_type_id = achat_direct;
update da_afficher set da_type_id = achat_direct;

alter table Demande_Appro DROP COLUMN achat_direct;