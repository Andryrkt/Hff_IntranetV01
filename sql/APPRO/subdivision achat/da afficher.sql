ALTER table da_afficher
ADD numero_demande_appro_mere varchar(11);

ALTER table da_afficher
ADD demande_appro_parent_id int null;

ALTER table da_afficher
alter column demande_appro_id int null;