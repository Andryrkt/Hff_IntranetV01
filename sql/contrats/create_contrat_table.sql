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