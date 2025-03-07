-- ce 07/03/2025
SELECT DB_NAME() AS CurrentDatabase;
USE HFF_INTRANET;
GO

select * from demande_intervention;



----------------------------------------------------------------------------
-- Language: sql
-- pour la table TEST
SELECT DB_NAME() AS CurrentDatabase;
USE HFF_INTRANET_TEST_TEST;
GO

select * from demande_intervention where numero_demande_dit = 'DIT25020132'; 

