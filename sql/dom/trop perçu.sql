
DELETE FROM Sous_type_document where ID_Sous_Type_Document='4';

INSERT INTO Sous_Type_Document (Code_Document, Code_Sous_Type, Date_creation)
    VALUES ('ORM', 'TROP PERCU', GETDATE())

INSERT INTO Idemnity (Catg, Destination, Rmq, Montant_idemnite, Type)
SELECT Catg, Destination, Rmq, Montant_idemnite, 'TROP PERCU' FROM Idemnity
WHERE Type = 'MISSION';

INSERT INTO idemnite(montant, site_id, catg_id, rmq_id, sousTypeDoc_id, date_creation, date_modification)
SELECT montant, site_id, catg_id, rmq_id, '11', date_creation, date_modification FROM idemnite
WHERE sousTypeDoc_id = '2';

CREATE TABLE Demande_ordre_mission_tp (
    id int IDENTITY(1,1) NOT NULL,
    Numero_Ordre_Mission varchar(11) not null,
    Numero_Ordre_Mission_Tp  varchar(11) not null,
    Nombre_Jour_Tp int not null
);
