CREATE TABLE [dbo].[contrat] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [reference] NVARCHAR(255) NOT NULL,
        [objet] NVARCHAR(255) NULL,
        [date_enregistrement] DATE NULL,
        [statut] NVARCHAR(50) NULL,
        [agence] NVARCHAR(100) NOT NULL,
        [service] NVARCHAR(100) NOT NULL,
        [nom_partenaire] NVARCHAR(150) NULL,
        [type_tiers] NVARCHAR(50) NULL,
        [date_debut_contrat] DATE NULL,
        [date_fin_contrat] DATE NULL,
        [piece_jointe] NVARCHAR(255) NULL
);

-- exemple de donnée pour test
INSERT INTO contrat (reference, objet, date_enregistrement, statut, agence, service, nom_partenaire, type_tiers, date_debut_contrat, date_fin_contrat, piece_jointe) 
VALUES 
('CONTRAT260320260001', 'exemple d''objet de contrat 1', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_1.pdf'),
('CONTRAT260320260002', 'exemple d''objet de contrat 2', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_2.pdf'),
('CONTRAT260320260003', 'exemple d''objet de contrat 3', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_3.pdf'),
('CONTRAT260320260004', 'exemple d''objet de contrat 4', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_4.pdf'),
('CONTRAT260320260005', 'exemple d''objet de contrat 5', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_5.pdf'),
('CONTRAT260320260006', 'exemple d''objet de contrat 6', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_6.pdf'),
('CONTRAT260320260007', 'exemple d''objet de contrat 7', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_7.pdf'),
('CONTRAT260320260008', 'exemple d''objet de contrat 8', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_8.pdf'),
('CONTRAT260320260009', 'exemple d''objet de contrat 9', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_9.pdf'),
('CONTRAT260320260010', 'exemple d''objet de contrat 10', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_10.pdf'),
('CONTRAT260320260011', 'exemple d''objet de contrat 11', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_11.pdf'),
('CONTRAT260320260012', 'exemple d''objet de contrat 12', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_12.pdf'),
('CONTRAT260320260013', 'exemple d''objet de contrat 13', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_13.pdf'),
('CONTRAT260320260014', 'exemple d''objet de contrat 14', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_14.pdf'),
('CONTRAT260320260015', 'exemple d''objet de contrat 15', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_15.pdf'),
('CONTRAT260320260016', 'exemple d''objet de contrat 16', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_16.pdf'),
('CONTRAT260320260017', 'exemple d''objet de contrat 17', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_17.pdf'),
('CONTRAT260320260018', 'exemple d''objet de contrat 18', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_18.pdf'),
('CONTRAT260320260019', 'exemple d''objet de contrat 19', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_19.pdf'),
('CONTRAT260320260020', 'exemple d''objet de contrat 20', '2026-03-13', 'EN ATTENTE APPROBATION', '80', 'NEG', 'NON DFINI', 'FOURNISSEUR', '2026-03-13', '2026-03-13', 'piece_joint_20.pdf');