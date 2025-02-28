ALTER TABLE users ADD fonction VARCHAR(255)

ALTER TABLE users
ADD agence_utilisateur VARCHAR(255)
ALTER TABLE users
ADD service_utilisateur VARCHAR(255)

CREATE TABLE users_agence_autoriser (
    user_id INT,
    agence_autoriser_id INT,
    CONSTRAINT PK_users_agence_autoriser PRIMARY KEY (user_id, agence_autoriser_id),
    CONSTRAINT FK_users_agence_autoriser_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_agence_autoriser_agence_autoriser_id FOREIGN KEY (agence_autoriser_id) REFERENCES agences (id)
);

CREATE TABLE agence_user (
    agence_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (agence_id, user_id),
    FOREIGN KEY (agence_id) REFERENCES agences (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

/** TABLE RELATION ENTRE L4UTILISATEUR ET LE PERMISSION */
CREATE TABLE users_permission (
    user_id INT,
    permission_id INT,
    CONSTRAINT PK_users_permission PRIMARY KEY (user_id, permission_id),
    CONSTRAINT FK_users_permission_user_id FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT FK_users_permission_permission_id FOREIGN KEY (permission_id) REFERENCES permissions (id)
);

UPDATE users
SET
    users.personnel_id = (
        select id
        from Personnel
        where
            matricule = users.matricule
    )
where
    users.matricule = (
        select matricule
        from Personnel
        where
            matricule = users.matricule
    )

ALTER TABLE users ADD societe_id INT

UPDATE users
set
    societe_id = 1


    
    /** permet de changer le personnel_id dans la table user à partir de l'id de la table personnel */
UPDATE users
SET
    personnel_id = (
        SELECT Personnel.id
        FROM Personnel
        WHERE
            Personnel.Matricule = users.matricule
    )
WHERE
    EXISTS (
        SELECT 1
        FROM Personnel
        WHERE
            Personnel.Matricule = users.matricule
    );


UPDATE demande_intervention
SET mail_demandeur = CASE 
    WHEN utilisateur_demandeur = 'aina' THEN  'aina.rajaonarivelo@hff.mg'
    WHEN utilisateur_demandeur = 'ambroise' THEN  'ambroise.rakotoarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'cathy' THEN  'cathy.andrianarimanana@hff.mg'
    WHEN utilisateur_demandeur = 'davida' THEN  'davida.ramahatantsoa@hff.mg'
    WHEN utilisateur_demandeur = 'domoina' THEN  'domoina.rakotohasy@hff.mg'
    WHEN utilisateur_demandeur = 'Donat' THEN  'donat.rabarone@hff.mg'
    WHEN utilisateur_demandeur = 'estella' THEN  'estella.razafiarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'faneva' THEN  'faneva.rabarijaona@hff.mg'
    WHEN utilisateur_demandeur = 'fetra' THEN  'fetra.rakotomalalatiana@hff.mg'
    WHEN utilisateur_demandeur = 'fidinirina' THEN  'fidinirina.rasamimanana@hff.mg'
    WHEN utilisateur_demandeur = 'finaritra' THEN  'finaritra.rakotoarimanana@hff.mg'
    WHEN utilisateur_demandeur = 'gaelle.lecohu' THEN  'gaelle.lecohu@hff.mg'
    WHEN utilisateur_demandeur = 'gillot' THEN  'gillot.emile@hff.mg'
    WHEN utilisateur_demandeur = 'Hasina' THEN  'hasina.raharinasinavalona@hff.mg'
    WHEN utilisateur_demandeur = 'helimino' THEN  'helimino.andriamihaja@hff.mg'
    WHEN utilisateur_demandeur = 'hoby' THEN  'hoby.rasoazanamiarana@hff.mg'
    WHEN utilisateur_demandeur = 'HOLY' THEN  'holy.ranaivoson@hff.mg'
    WHEN utilisateur_demandeur = 'maharo' THEN  'maharo.ratsimandresy@hff.mg'
    WHEN utilisateur_demandeur = 'malala' THEN  'malala.rajonson@hff.mg'
    WHEN utilisateur_demandeur = 'mamitiana' THEN  'mamitiana.ravaoarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'mampionona' THEN  'prisca.soloniaina@hff.mg'
    WHEN utilisateur_demandeur = 'marie' THEN  'marie.muhimana@hff.mg'
    WHEN utilisateur_demandeur = 'martin' THEN  'martin.randrianantoanina@hff.mg'
    WHEN utilisateur_demandeur = 'mendrika.f' THEN  'mendrika.randriarimalala@hff.mg'
    WHEN utilisateur_demandeur = 'Mihanta' THEN  'mihanta.rasoloarison@hff.mg'
    WHEN utilisateur_demandeur = 'Miora.energie' THEN  'miora.razafimahazo@hff.mg'
    WHEN utilisateur_demandeur = 'naina' THEN  'niaina.randrianaivoravelona@hff.mg'
    WHEN utilisateur_demandeur = 'nancy' THEN  'nancy.ratovoarisolo@hff.mg'
    WHEN utilisateur_demandeur = 'nomentsoa' THEN  'diamondra.razafiniary@hff.mg'
    WHEN utilisateur_demandeur = 'Norolalao' THEN  'norolalao.harimanana@hff.mg'
    WHEN utilisateur_demandeur = 'oliva' THEN  'oliva.ramaroson@hff.mg'
    WHEN utilisateur_demandeur = 'omega' THEN  'omega.razanadrasoa@hff.mg'
    WHEN utilisateur_demandeur = 'onitiana' THEN  'onitiana.ranaivoarison@hff.mg'
    WHEN utilisateur_demandeur = 'ony.rafalimanana' THEN  'ony.rafalimanana@hff.mg'
    WHEN utilisateur_demandeur = 'paul.marcusse' THEN  'paul.marcusse@hff.mg'
    WHEN utilisateur_demandeur = 'Prisca' THEN  'prisca.michea@hff.mg'
    WHEN utilisateur_demandeur = 'r.alisoa' THEN  'alisoa.rakotoarivony@hff.mg'
    WHEN utilisateur_demandeur = 'rachel' THEN  'rachel.ralalarinarivo@hff.mg'
    WHEN utilisateur_demandeur = 'radonirina' THEN  'radonirina.andriantsimba@hff.mg'
    WHEN utilisateur_demandeur = 'rajohnson' THEN  'fenohery.rajohnson@hff.mg'
    WHEN utilisateur_demandeur = 'rojo' THEN  'rojo.ramamonjy@hff.mg'
    WHEN utilisateur_demandeur = 'roussel' THEN  'antoine.roussel@hff.mg'
    WHEN utilisateur_demandeur = 'setra' THEN  'setra.razanamparany@hff.mg'
    WHEN utilisateur_demandeur = 'Tahiantsoa' THEN  'tahiantsoa.rafaliarivony@hff.mg'
    WHEN utilisateur_demandeur = 'tiana' THEN  'tiana.andrianarivelo@hff.mg'
    WHEN utilisateur_demandeur = 'tsiry' THEN  'tsirivao.radison@hff.mg'
    WHEN utilisateur_demandeur = 'vania' THEN  'vania.rakotomanga@hff.mg'
    WHEN utilisateur_demandeur = 'Voahangy' THEN  'seheno.raholiarimanga@hff.mg'
    WHEN utilisateur_demandeur = 'zoary' THEN  'zoary.andriamanantena@hff.mg'
    ELSE '-'
END
WHERE num_migr=4

UPDATE users SET personnel_id = NULL WHERE personnel_id NOT IN (SELECT id FROM personnel);