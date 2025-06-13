CREATE TABLE da_demande_modification
(
    id int IDENTITY(1,1) NOT NULL,
    numero_da varchar(11),
    utilisateur varchar(100),
    est_deverouille bit,
    motif text,
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),

    CONSTRAINT PK_da_demande_modification PRIMARY KEY (id)
)