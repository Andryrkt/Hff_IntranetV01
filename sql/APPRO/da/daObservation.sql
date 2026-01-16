-- table observaton
CREATE TABLE da_observation
(
    id int IDENTITY(1,1) NOT NULL,
    observation text,
    numero_da varchar(11),
    utilisateur varchar(100),
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),

    CONSTRAINT PK_da_observation PRIMARY KEY (id)
);

ALTER TABLE da_observation ADD file_names text;