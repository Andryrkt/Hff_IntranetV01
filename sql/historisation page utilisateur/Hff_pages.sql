CREATE TABLE Hff_pages (
	id INT IDENTITY (1, 1) ,
    nom VARCHAR(255) NOT NULL,
    nom_route varchar(255) NOT NULL,
    lien VARCHAR(255) NOT NULL
    CONSTRAINT PK_hff_pages PRIMARY KEY (id)
);

INSERT INTO Hff_pages (nom, nom_route, lien)
VALUES 
    ('Accueil', 'profil_acceuil', '/Acceuil'),
    ('Authentification (identifiants incorrects)', 'security_signin', '/'),
    ('Nouvelle DIT', 'dit_new', '/dit/new'),
    ('Liste DIT', 'dit_index', '/dit'),
    ('Recherche sur la liste DIT', 'dit_index_search', '/dit'),
    ('Duplication d''une DIT', 'dit_duplication', '/ditDuplication/{id<\d+>}/{numDit<\w+>}'),
    ('Consultation intervention atelier avec DIT', 'dw_interv_ate_avec_dit', '/dw-intervention-atelier-avec-dit/{numDit}'),
    ('Consultation dossier DIT', 'dit_dossier_intervention_atelier', '/dit-dossier-intervention-atelier'),
    ('Nouvelle ordre de mission premier formulaire', 'dom_first_form', '/dom-first-form'),
    ('Nouvelle ordre de mission deuxième formulaire', 'dom_second_form', '/dom-second-form'),
    ('Liste DOM (tous)', 'doms_liste', '/dom-liste'),
    ('Recherche sur la liste DOM (tous)', 'doms_liste_search', '/dom-liste'),
    ('Liste DOM (annulées)', 'dom_list_annuler', '/dom-list-annuler'),
    ('Fiche détail d''un DOM', 'Dom_detail', '/detailDom/{id}'),
    ('Nouvelle BADM premier formulaire', 'badms_newForm1', '/badm-form1'),
    ('Nouvelle BADM deuxième formulaire', 'badms_newForm2', '/badm-form2'),
    ('Liste BADM (tous)', 'badmListe_AffichageListeBadm', '/listBadm'),
    ('Recherche sur la liste BADM (tous)', 'badmListe_AffichageListeBadm_search', '/listBadm'),
    ('Fiche détail d''un BADM', 'BadmDetail_detailBadm', '/detailBadm/{id}'), 
    ('Duplication d''un BADM', 'BadmDupli_dupliBadm', '/dupliBADM/{numBadm}/{id}'), 
    ('Liste BADM (annulées)', 'badm_list_annuler', '/badm-list-annuler'),
    ('Recherche sur la liste BADM (annulées)', 'badm_list_annuler_search', '/badm-list-annuler'),
    ('Nouveau casier premier formulaire', 'casier_nouveau', '/nouveauCasier'),
    ('Nouveau casier deuxième formulaire', 'casiser_formulaireCasier', '/createCasier'),
    ('Liste casier (temporaire)', 'listeTemporaire_affichageListeCasier', '/listTemporaireCasier'),
    ('Recherche sur la liste casier (temporaire)', 'listeTemporaire_affichageListeCasier_search', '/listTemporaireCasier'),
    ('Liste casier (définitive)', 'liste_affichageListeCasier', '/listCasier'),
    ('Recherche sur la liste casier (définitive)', 'liste_affichageListeCasier_search', '/listCasier'),
    ('Liste OR à traiter', 'magasinListe_index', '/liste-magasin'),
    ('Recherche sur la liste OR à traiter', 'magasinListe_index_search', '/liste-magasin'),
    ('Liste OR à livrer', 'magasinListe_or_Livrer', '/liste-or-livrer'),
    ('Recherche sur la liste OR à livrer', 'magasinListe_or_Livrer_search', '/liste-or-livrer'),
    ('Liste CIS à traiter', 'cis_liste_a_traiter', '/cis-liste-a-traiter'),
    ('Recherche sur la liste CIS à traiter', 'cis_liste_a_traiter_search', '/cis-liste-a-traiter'),
    ('Liste CIS à livrer', 'cis_liste_a_livrer', '/cis-liste-a-livrer'),
    ('Recherche sur la liste CIS à livrer', 'cis_liste_a_livrer_search', '/cis-liste-a-livrer'),
    ('Nouvelle demande de support informatique (ticket)', 'demande_support_informatique', '/demande-support-informatique'),
    ('Liste Ticket', 'liste_tik_index', '/tik-liste'),
    ('Recherche sur la liste Ticket', 'liste_tik_index_search', '/tik-liste'),
    ('Fiche détail d''un ticket', 'detail_tik', '/tik-detail/{id<\d+>}'),
    ('Fiche de modification d''un ticket', 'tik_modification_edit', '/tik-modification-edit/{id}'),
    ('Calendrier planning', 'tik_calendar_planning', '/tik-calendar-planning'),
    ('Liste Planning', 'planning_vue', '/planning'),
    ('Recherche sur la liste Planning', 'planning_vue_search', '/planning'),
    ('Procédure d''utilisation', 'badm_index', '/doc/badm'),
    ('Soumission de la facture d''un DIT', 'dit_insertion_facture', '/soumission-facture/{numDit}'),
    ('Soumission de l''OR d''un DIT', 'dit_insertion_or', '/soumission-or/{numDit}'),
    ('Soumission de la RI d''un DIT', 'dit_insertion_ri', '/soumission-ri/{numDit}'),
    ('Fiche détail d''un DIT', 'dit_validationDit', '/ditValidation/{id<\d+>}/{numDit<\w+>}'),
    ('Soumission d''une commande', 'dit_insertion_cde', '/soumission-cde'),
    ('Recherche sur la liste DOM (annulées)', 'dom_list_annuler_search', '/dom-list-annuler'),
;


UPDATE Hff_pages
SET 
    nom = CASE
        WHEN id=1 THEN 'Accueil'
        WHEN id=2 THEN 'Authentification (identifiants incorrects)'
        WHEN id=3 THEN 'Nouvelle DIT'
        WHEN id=4 THEN 'Liste DIT'
        WHEN id=5 THEN 'Recherche sur la liste DIT'
        WHEN id=6 THEN 'Duplication d''une DIT'
        WHEN id=7 THEN 'Consultation intervention atelier avec DIT'
        WHEN id=8 THEN 'Consultation dossier DIT'
        WHEN id=9 THEN 'Nouvelle ordre de mission premier formulaire'
        WHEN id=10 THEN 'Nouvelle ordre de mission deuxième formulaire'
        WHEN id=11 THEN 'Liste DOM (tous)'
        WHEN id=12 THEN 'Recherche sur la liste DOM (tous)'
        WHEN id=13 THEN 'Liste DOM (annulées)'
        WHEN id=14 THEN 'Fiche détail d''un DOM'
        WHEN id=15 THEN 'Nouvelle BADM premier formulaire'
        WHEN id=16 THEN 'Nouvelle BADM deuxième formulaire'
        WHEN id=17 THEN 'Liste BADM (tous)'
        WHEN id=18 THEN 'Recherche sur la liste BADM (tous)'
        WHEN id=19 THEN 'Fiche détail d''un BADM'
        WHEN id=20 THEN 'Duplication d''un BADM'
        WHEN id=21 THEN 'Liste BADM (annulées)'
        WHEN id=22 THEN 'Recherche sur la liste BADM (annulées)'
        WHEN id=23 THEN 'Nouveau casier premier formulaire'
        WHEN id=24 THEN 'Nouveau casier deuxième formulaire'
        WHEN id=25 THEN 'Liste casier (temporaire)'
        WHEN id=26 THEN 'Recherche sur la liste casier (temporaire)'
        WHEN id=27 THEN 'Liste casier (définitive)'
        WHEN id=28 THEN 'Recherche sur la liste casier (définitive)'
        WHEN id=29 THEN 'Liste OR à traiter'
        WHEN id=30 THEN 'Recherche sur la liste OR à traiter'
        WHEN id=31 THEN 'Liste OR à livrer'
        WHEN id=32 THEN 'Recherche sur la liste OR à livrer'
        WHEN id=33 THEN 'Liste CIS à traiter'
        WHEN id=34 THEN 'Recherche sur la liste CIS à traiter'
        WHEN id=35 THEN 'Liste CIS à livrer'
        WHEN id=36 THEN 'Recherche sur la liste CIS à livrer'
        WHEN id=37 THEN 'Nouvelle demande de support informatique (ticket)'
        WHEN id=38 THEN 'Liste Ticket'
        WHEN id=39 THEN 'Recherche sur la liste Ticket'
        WHEN id=40 THEN 'Fiche détail d''un ticket'
        WHEN id=41 THEN 'Fiche de modification d''un ticket'
        WHEN id=42 THEN 'Calendrier planning'
        WHEN id=43 THEN 'Liste Planning'
        WHEN id=44 THEN 'Recherche sur la liste Planning'
        WHEN id=45 THEN 'Procédure d''utilisation'
        WHEN id=46 THEN 'Soumission de la facture d''un DIT'
        WHEN id=47 THEN 'Soumission de l''OR d''un DIT'
        WHEN id=48 THEN 'Soumission de la RI d''un DIT'
        WHEN id=49 THEN 'Fiche détail d''un DIT'
        WHEN id=50 THEN 'Soumission d''une commande'
        WHEN id=51 THEN 'Recherche sur la liste DOM (annulées)'
    END,
    nom_route = CASE
        WHEN id=1 THEN 'profil_acceuil'
        WHEN id=2 THEN 'security_signin'
        WHEN id=3 THEN 'dit_new'
        WHEN id=4 THEN 'dit_index'
        WHEN id=5 THEN 'dit_index_search'
        WHEN id=6 THEN 'dit_duplication'
        WHEN id=7 THEN 'dw_interv_ate_avec_dit'
        WHEN id=8 THEN 'dit_dossier_intervention_atelier'
        WHEN id=9 THEN 'dom_first_form'
        WHEN id=10 THEN 'dom_second_form'
        WHEN id=11 THEN 'doms_liste'
        WHEN id=12 THEN 'doms_liste_search'
        WHEN id=13 THEN 'dom_list_annuler'
        WHEN id=14 THEN 'Dom_detail'
        WHEN id=15 THEN 'badms_newForm1'
        WHEN id=16 THEN 'badms_newForm2'
        WHEN id=17 THEN 'badmListe_AffichageListeBadm'
        WHEN id=18 THEN 'badmListe_AffichageListeBadm_search'
        WHEN id=19 THEN 'BadmDetail_detailBadm'
        WHEN id=20 THEN 'BadmDupli_dupliBadm'
        WHEN id=21 THEN 'badm_list_annuler'
        WHEN id=22 THEN 'badm_list_annuler_search'
        WHEN id=23 THEN 'casier_nouveau'
        WHEN id=24 THEN 'casiser_formulaireCasier'
        WHEN id=25 THEN 'listeTemporaire_affichageListeCasier'
        WHEN id=26 THEN 'listeTemporaire_affichageListeCasier_search'
        WHEN id=27 THEN 'liste_affichageListeCasier'
        WHEN id=28 THEN 'liste_affichageListeCasier_search'
        WHEN id=29 THEN 'magasinListe_index'
        WHEN id=30 THEN 'magasinListe_index_search'
        WHEN id=31 THEN 'magasinListe_or_Livrer'
        WHEN id=32 THEN 'magasinListe_or_Livrer_search'
        WHEN id=33 THEN 'cis_liste_a_traiter'
        WHEN id=34 THEN 'cis_liste_a_traiter_search'
        WHEN id=35 THEN 'cis_liste_a_livrer'
        WHEN id=36 THEN 'cis_liste_a_livrer_search'
        WHEN id=37 THEN 'demande_support_informatique'
        WHEN id=38 THEN 'liste_tik_index'
        WHEN id=39 THEN 'liste_tik_index_search'
        WHEN id=40 THEN 'detail_tik'
        WHEN id=41 THEN 'tik_modification_edit'
        WHEN id=42 THEN 'tik_calendar_planning'
        WHEN id=43 THEN 'planning_vue'
        WHEN id=44 THEN 'planning_vue_search'
        WHEN id=45 THEN 'badm_index'
        WHEN id=46 THEN 'dit_insertion_facture'
        WHEN id=47 THEN 'dit_insertion_or'
        WHEN id=48 THEN 'dit_insertion_ri'
        WHEN id=49 THEN 'dit_validationDit'
        WHEN id=50 THEN 'dit_insertion_cde'
        WHEN id=51 THEN 'dom_list_annuler_search'
    END,
    lien = CASE
        WHEN id=1 THEN '/Acceuil'
        WHEN id=2 THEN '/'
        WHEN id=3 THEN '/dit/new'
        WHEN id=4 THEN '/dit'
        WHEN id=5 THEN '/dit'
        WHEN id=6 THEN '/ditDuplication/{id<\d+>}/{numDit<\w+>}'
        WHEN id=7 THEN '/dw-intervention-atelier-avec-dit/{numDit}'
        WHEN id=8 THEN '/dit-dossier-intervention-atelier'
        WHEN id=9 THEN '/dom-first-form'
        WHEN id=10 THEN '/dom-second-form'
        WHEN id=11 THEN '/dom-liste'
        WHEN id=12 THEN '/dom-liste'
        WHEN id=13 THEN '/dom-list-annuler'
        WHEN id=14 THEN '/detailDom/{id}'
        WHEN id=15 THEN '/badm-form1'
        WHEN id=16 THEN '/badm-form2'
        WHEN id=17 THEN '/listBadm'
        WHEN id=18 THEN '/listBadm'
        WHEN id=19 THEN '/detailBadm/{id}'
        WHEN id=20 THEN '/dupliBADM/{numBadm}/{id}'
        WHEN id=21 THEN '/badm-list-annuler'
        WHEN id=22 THEN '/badm-list-annuler'
        WHEN id=23 THEN '/nouveauCasier'
        WHEN id=24 THEN '/createCasier'
        WHEN id=25 THEN '/listTemporaireCasier'
        WHEN id=26 THEN '/listTemporaireCasier'
        WHEN id=27 THEN '/listCasier'
        WHEN id=28 THEN '/listCasier'
        WHEN id=29 THEN '/liste-magasin'
        WHEN id=30 THEN '/liste-magasin'
        WHEN id=31 THEN '/liste-or-livrer'
        WHEN id=32 THEN '/liste-or-livrer'
        WHEN id=33 THEN '/cis-liste-a-traiter'
        WHEN id=34 THEN '/cis-liste-a-traiter'
        WHEN id=35 THEN '/cis-liste-a-livrer'
        WHEN id=36 THEN '/cis-liste-a-livrer'
        WHEN id=37 THEN '/demande-support-informatique'
        WHEN id=38 THEN '/tik-liste'
        WHEN id=39 THEN '/tik-liste'
        WHEN id=40 THEN '/tik-detail/{id<\d+>}'
        WHEN id=41 THEN '/tik-modification-edit/{id}'
        WHEN id=42 THEN '/tik-calendar-planning'
        WHEN id=43 THEN '/planning'
        WHEN id=44 THEN '/planning'
        WHEN id=45 THEN '/doc/badm'
        WHEN id=46 THEN '/soumission-facture/{numDit}'
        WHEN id=47 THEN '/soumission-or/{numDit}'
        WHEN id=48 THEN '/soumission-ri/{numDit}'
        WHEN id=49 THEN '/ditValidation/{id<\d+>}/{numDit<\w+>}'
        WHEN id=50 THEN '/soumission-cde'
        WHEN id=51 THEN '/dom-list-annuler'
    END
WHERE id BETWEEN 1 AND 51;



