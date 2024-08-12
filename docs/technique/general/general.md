# duplique une tabel dans sqlServer

-- Étape 1 : Dupliquer la structure sans données
SELECT \*
INTO NouvelleTable
FROM AncienneTable
WHERE 1 = 0;

-- Étape 2 : Copier les données
INSERT INTO NouvelleTable
SELECT \*
FROM AncienneTable;
