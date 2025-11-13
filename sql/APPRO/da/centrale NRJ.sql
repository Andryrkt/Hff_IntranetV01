CREATE TABLE centrale_nrj 
(
    id int IDENTITY(1,1) NOT NULL,
    code_centrale varchar(4) NOT NULL,
    designation_central varchar(50) NOT NULL,
    CONSTRAINT PK_centrale_nrj PRIMARY KEY (id)
);

ALTER TABLE Demande_Appro ADD code_centrale varchar(4) NULL;

ALTER TABLE da_afficher ADD code_centrale varchar(4) NULL;

insert into centrale_nrj
values 
('ABL', 'AMBILOBE'),
('ABS', 'AMBOSITRA'),
('ABT', 'AMBODIATAFANA'),
('ADP', 'ANDAPA'),
('AJB', 'ANJOZOROBE'),
('AMB', 'AMBOHIMANAMBOLA'),
('BSL', 'BESALAMPY'),
('FGN', 'FARAFANGANA'),
('FLP', 'FOULPOINTE'),
('FNR', 'FIANARANTSOA'),
('FST', 'FENERIVE EST'),
('MHR', 'MAHANORO'),
('MJN', 'MAHAJANGA'),
('MNK', 'MANAKARA'),
('MNN', 'MANANARA NORD'),
('MRV', 'MAROVOAY'),
('NSB', 'NOSY BE'),
('SIV', 'SOANIERANA IVONGO'),
('STM', 'SAINTE MARIE'),
('TSD', 'TSIROANOMANDIDY'),
('TUL', 'TOLIARA'),
('VGN', 'VANGAINDRANO'),
('VPN', 'VOHIPENO'),
('VTM', 'VATOMANDRY'),
('VVT', 'VAVATENINA')