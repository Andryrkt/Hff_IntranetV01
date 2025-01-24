CREATE TABLE site (
    id INT IDENTITY (1, 1),
    nom_zone VARCHAR(255),
    date_creation DATE NOT NULL,
    date_modification DATE NOT NULL,
    CONSTRAINT PK_site PRIMARY KEY (id),
);

INSERT INTO
    site (
        nom_zone,
        date_creation,
        date_modification
    )
VALUES (
        'AUTRES VILLES',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'HORS TANA MOINS DE 24H',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'ZONE ENCLAVEES',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'ZONE TOURISTIQUES',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'FORT-DAUPHIN',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'AUTRES SITE ENCLAVES',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'HORS TANA',
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'TANA',
        '2024-07-16',
        '2024-07-16'
    );