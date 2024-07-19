-- pour voir le detail de la table
SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_NAME = '<table>';

-- rendre une colonne en contraint UNIQUE
ALTER TABLE <
table >
ADD CONSTRAINT UQ_numero_demande_dit UNIQUE (< colonne >);

-- Ajouter une colonne à une table
ALTER TABLE < table > ADD < nouveau_colonne > < type >