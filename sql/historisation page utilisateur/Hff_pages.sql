CREATE TABLE Hff_pages (
	id INT IDENTITY (1, 1),
    nom VARCHAR(255) NOT NULL,
    nom_route varchar(255) NOT NULL,
    lien VARCHAR(255) NOT NULL
    CONSTRAINT PK_hff_pages PRIMARY KEY (id)
);

INSERT INTO TABLE Hff_pages (nom, nom_route, lien)
VALUES 
    ('Accueil', 'profil_acceuil', '/Acceuil'),
    ('Authentification (identifiants incorrects)', 'security_signin', '/'),
    ('Nouvelle DIT', 'dit_new', '/dit/new'),
    ('Liste DIT', 'dit_index', '/dit'),
    ('Duplication d''une DIT', 'dit_duplication', '/ditDuplication/{id<\d+>}/{numDit<\w+>}'),
    ('Consultation intervention atelier avec DIT', 'dw_interv_ate_avec_dit', '/dw-intervention-atelier-avec-dit/{numDit}'),
    ('Consultation dossier DIT', 'dit_dossier_intervention_atelier', '/dit-dossier-intervention-atelier'),
    ('Nouvelle ordre de mission premier formulaire', 'dom_first_form', '/dom-first-form'),
    ('Nouvelle ordre de mission deuxième formulaire', 'dom_second_form', '/dom-second-form'),
    ('Liste de tous les ordres de mission', 'doms_liste', '/dom-liste'),
    ('Liste des ordres de mission annulées', 'dom_list_annuler', '/dom-list-annuler'),
    ('Fiche détail d''un DOM', 'Dom_detail', '/detailDom/{id}'),
    ('Nouvelle BADM premier formulaire', 'badms_newForm1', '/badm-form1'),
    ('Nouvelle BADM deuxième formulaire', 'badms_newForm2', '/badm-form2'),
    ('Liste de tous les BADM', 'badmListe_AffichageListeBadm', '/listBadm'),
    ('Fiche détail d''un BADM', 'BadmDetail_detailBadm', '/detailBadm/{id}'), 
    ('Duplication d''un BADM', 'BadmDupli_dupliBadm', '/dupliBADM/{numBadm}/{id}'), 
    ('Liste des BADM annulées', 'badm_list_annuler', '/badm-list-annuler'),
    ('Nouveau casier premier formulaire', 'casier_nouveau', '/nouveauCasier'),
    ('Nouveau casier deuxième formulaire', 'casiser_formulaireCasier', '/createCasier'),
    ('Liste temporaire de tous les casiers', 'listeTemporaire_affichageListeCasier', '/listTemporaireCasier'),
    ('Liste définitive de tous les casiers', 'liste_affichageListeCasier', '/listCasier'),
    ('Liste des OR à traiter', 'magasinListe_index', '/liste-magasin'),
    ('Liste des OR à livrer', 'magasinListe_or_Livrer', '/liste-or-livrer'),
    ('Liste des CIS à traiter', 'cis_liste_a_traiter', '/cis-liste-a-traiter'),
    ('Liste des CIS à livrer', 'cis_liste_a_livrer', '/cis-liste-a-livrer'),
    ('Nouvelle demande de support informatique (ticket)', 'demande_support_informatique', '/demande-support-informatique'),
    ('Liste de tous les tickets', 'liste_tik_index', '/tik-liste'),
    ('Fiche détail d''un ticket', 'detail_tik', '/tik-detail/{id<\d+>}'),
    ('Fiche de modification d''un ticket', 'tik_modification_edit', '/tik-modification-edit/{id}'),
    ('Calendrier planning', 'tik_calendar_planning', '/tik-calendar-planning'),
    ('Liste de tous les planning', 'planning_vue', '/planning'),
    ('Procédure d''utilisation', 'badm_index', '/doc/badm'),
    ('Soumission de la facture d''un DIT', 'dit_insertion_facture', '/soumission-facture/{numDit}'),
    ('Soumission de l''OR d''un DIT', 'dit_insertion_or', '/soumission-or/{numDit}'),
    ('Soumission de la RI d''un DIT', 'dit_insertion_ri', '/soumission-ri/{numDit}'),
    ('Fiche détail d''un DIT', 'dit_validationDit', '/ditValidation/{id<\d+>}/{numDit<\w+>}'),
    ('Soumission d''une commande', 'dit_insertion_cde', '/soumission-cde')
;
