CREATE TABLE fonctions (
    id INT IDENTITY (1, 1),
    description VARCHAR(255),
    date_creation DATETIME,
    date_modification DATETIME,
    CONSTRAINT PK_fonction_id PRIMARY KEY (id)
)

INSERT INTO
    fonctions (
        description,
        date_creation,
        date_modification
    )
VALUES (
        'SECRETAIRE FACTURIERE IRIUM',
        '2024-08-02',
        '2024-08-02'
    ),

( 'ASSISTANTE IPS', '2024-08-02', '2024-08-02' ),

(
    'SECRETAIRE',
    '2024-08-02',
    '2024-08-02'
),
(
    'ASSISTANTE IRIUM IPS',
    '2024-08-02',
    '2024-08-02'
),
(
    'ASSISTANT IRIUM',
    '2024-08-02',
    '2024-08-02'
),
(
    'CHEF DE SECTION M M',
    '2024-08-02',
    '2024-08-02'
),
(
    'CHEF MOTORISTE GROUPES ELECTRO',
    '2024-08-02',
    '2024-08-02'
),
(
    'CHEF DE SECTION ELECTRICITE',
    '2024-08-02',
    '2024-08-02'
),
(
    'CHEF DE SECTION FB',
    '2024-08-02',
    '2024-08-02'
),
(
    'RESP.PARC VEH/POIDS LOURD',
    '2024-08-02',
    '2024-08-02'
),
(
    'RESPONSABLE SECTION MASS',
    '2024-08-02',
    '2024-08-02'
),
(
    'CHEF D''EQUIPE',
    '2024-08-02',
    '2024-08-02'
),
(
    'WARRANTY ENGINEER',
    '2024-08-02',
    '2024-08-02'
),
(
    'E.M ANALYST',
    '2024-08-02',
    '2024-08-02'
),
(
    'RESPONSABLE SECTIN EMS ET CM',
    '2024-08-02',
    '2024-08-02'
),
(
    'PROD SUPPORT & AFT MARKET MNGR',
    '2024-08-02',
    '2024-08-02'
),
(
    'WORKSHOP MANAGER',
    '2024-08-02',
    '2024-08-02'
),
(
    'PARTS AND LOGISTICS MANAGER',
    '2024-08-02',
    '2024-08-02'
)