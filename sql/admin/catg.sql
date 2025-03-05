CREATE TABLE catg (
    id INT IDENTITY (1, 1),
    description VARCHAR(255),
    sous_type_document_id INT,
    date_creation DATE NOT NULL,
    date_modification DATE NOT NULL,
    CONSTRAINT PK_catg PRIMARY KEY (id),
    CONSTRAINT FK_catg_sousTypeDoc_id FOREIGN KEY (sous_type_document_id) REFERENCES Sous_type_document (ID_Sous_Type_Document)
);

INSERT INTO
    catg (
        description,
        sous_type_document_id,
        date_creation,
        date_modification
    )
VALUES (
        'AGENTS DE MAITRISE, EMPLOYES SPECIALISES',
        2,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'CADRE HC',
        2,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'CHEF DE SERVICE',
        2,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'OUVRIERS ET CHAUFFEURS',
        2,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'TOUTE CATEGORIE',
        5,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'CHAUFFEURS PORTE CHAR',
        NULL,
        '2024-07-16',
        '2024-07-16'
    ),
    (
        'AIDE CHAUFFEUR',
        NULL,
        '2024-07-16',
        '2024-07-16'
    );