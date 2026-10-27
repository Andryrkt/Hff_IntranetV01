-- ============================================================
-- Guides utilisateur Compta et Magasin : premières versions rédigées
-- à partir du code, importées en BROUILLON (publie = 0) pour relecture
-- par un administrateur avant publication.
-- À exécuter après documentation_utilisateur_rubrique_publication.sql.
-- Rejouable : une page déjà présente n'est pas écrasée.
-- ============================================================

-- ---------- COMPTA ----------

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-consultation')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('ddp-consultation', N'Consultation des demandes de paiement', 'Compta', 2, 0, N'
<p>Cet écran (menu <strong>Compta › Demande de paiement › Consultation</strong>) liste les demandes de paiement : DDPA, DDPL, DDPR et BAP, magasin et appro.</p>
<h2>Rechercher</h2>
<p>Les filtres se combinent : agence et service débiteur, type de document, N° demande, N° commande, N° facture, utilisateur, période de création, statut et fournisseur. Cliquez sur <strong>Rechercher</strong>.</p>
<h2>Lire la liste</h2>
<p>Chaque ligne affiche le N° CLA (attribué à la transmission en validation), le N° de demande, la date, le type, le statut de la demande et celui du dossier de régularisation, le motif, les factures, la commande, le dossier de douane, le bénéficiaire, le montant HT, la devise, le mode de paiement et l’utilisateur.</p>
<h2>Actions (menu ⋮ en début de ligne)</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Action</th><th>Quand elle apparaît</th></tr></thead>
<tbody>
<tr><td>Dossier régularisation</td><td>Demande à l’avance (DDPA)</td></tr>
<tr><td>Modifier</td><td>Demande refusée : corrigez puis renvoyez-la</td></tr>
<tr><td>Consultation Facture</td><td>Demande après arrivage appro</td></tr>
<tr><td>Confirmation Demande paiement</td><td>Demande au statut « A confirmer »</td></tr>
</tbody>
</table>
<p>À vérifier : les profils Finance voient-ils toutes les demandes et les autres seulement les leurs ?</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'bon-de-caisse')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('bon-de-caisse', N'Bon de caisse', 'Compta', 3, 0, N'
<h2>Créer un bon de caisse</h2>
<p>Menu <strong>Compta › Bon de caisse › Nouveau bon de caisse</strong>. Le formulaire est celui de DocuWare, affiché dans l’intranet : remplissez-le et validez-le comme dans DocuWare. La demande part ensuite dans le circuit de validation DocuWare.</p>
<h2>Consulter les bons de caisse</h2>
<p>Menu <strong>Compta › Bon de caisse › Consultation</strong>. La liste affiche 10 demandes par page.</p>
<ul>
<li><strong>Filtres :</strong> numéro, période de la demande, statut, caisse de retrait (principale ou secondaire), type de paiement (espèces ou chèque), retrait lié à (avance ou remboursement), nom du validateur final, agence et service émetteur, agence et service débiteur.</li>
<li><strong>Colonnes :</strong> PDF de la demande, statut, numéro, date, caisse de retrait, retrait lié à, agence et service émetteur et débiteur, adresse mail du demandeur, montant, devise, motif, validateur final.</li>
<li><strong>PDF :</strong> le bouton de la première colonne ouvre la demande.</li>
<li><strong>Excel :</strong> le bouton Excel exporte toute la liste filtrée, pas seulement la page affichée.</li>
</ul>
<h2>Ce que vous voyez</h2>
<p>Vous voyez les demandes de votre agence et de votre service, ainsi que celles des agences et services autorisés sur votre profil. Les profils « multi-succursale » voient toutes les agences.</p>
', GETDATE(), 'import initial');

-- ---------- MAGASIN ----------

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-or')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-or', N'OR : liste à traiter et liste à livrer', 'Magasin', 1, 0, N'
<p>Ces deux listes montrent, ligne par ligne, les pièces demandées par l’atelier sur les ordres de réparation (OR) validés. Elles servent au magasin à préparer puis livrer les pièces.</p>
<table border="1" cellpadding="4">
<thead><tr><th>Liste</th><th>Ce qu’elle contient</th></tr></thead>
<tbody>
<tr><td><strong>À traiter</strong> (Magasin › OR › Liste à traiter)</td><td>Lignes de pièces d’OR atelier en cours, sans aucune quantité réservée ni livrée : le magasin doit encore les traiter.</td></tr>
<tr><td><strong>À livrer</strong> (Magasin › OR › Liste à livrer)</td><td>OR dont des pièces sont réservées et prêtes à être livrées, avec les quantités à livrer et déjà livrées.</td></tr>
</tbody>
</table>
<h2>Filtres</h2>
<ul>
<li>Niveau d’urgence, N° DIT, N° OR, référence pièce, désignation, constructeur, période de création de l’OR.</li>
<li><strong>Pièces :</strong> toutes les lignes, pièces magasin (par défaut), LUB, achats locaux, pneumatiques (à traiter uniquement).</li>
<li><strong>Etat OR</strong> (à livrer) : <em>complets</em> (par défaut), toutes les pièces demandées sont réservées ou livrées ; <em>incomplets</em>, une partie seulement est réservée ; ou tous.</li>
<li>Agence et service débiteur, agence émettrice.</li>
</ul>
<h2>Utiliser la liste</h2>
<ul>
<li>Le menu ⋮ d’une ligne ouvre le dossier de la DIT dans DocuWare.</li>
<li>Le bouton <strong>Excel</strong> exporte la liste filtrée.</li>
<li>Vous voyez les OR des agences autorisées sur votre profil, ou de votre agence par défaut. Les profils « multi-succursale » voient toutes les agences.</li>
</ul>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-cis')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-cis', N'CIS : liste à traiter et liste à livrer', 'Magasin', 2, 0, N'
<p>Les listes CIS fonctionnent comme les listes OR, pour les pièces des CIS liés aux OR (menu <strong>Magasin › CIS</strong>). À vérifier : définition du CIS à ajouter ici.</p>
<ul>
<li><strong>À traiter :</strong> lignes de pièces CIS pas encore traitées par le magasin (colonne « Qté à traiter »).</li>
<li><strong>À livrer :</strong> lignes avec des quantités à livrer et déjà livrées ; filtre <em>Etat OR</em> complets / incomplets / tous.</li>
</ul>
<h2>Filtres</h2>
<p>N° DIT, N° OR, N° CIS, référence pièce, désignation, constructeur, période de création du CIS et de l’OR, type de pièces (pièces magasin par défaut), agence et service débiteur, agence émettrice, et <strong>OR validé</strong> (coché par défaut).</p>
<h2>Colonnes</h2>
<p>N° DIT, N° CIS et date, agence et service des travaux, N° OR et date, agence et service débiteur ou client, intervention, ligne, constructeur, référence, désignation et quantités.</p>
<p>Le bouton <strong>Excel</strong> exporte la liste filtrée. Le périmètre des agences visibles est le même que pour les OR.</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-inventaire')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-inventaire', N'Inventaire', 'Magasin', 3, 0, N'
<h2>Liste inventaire</h2>
<p>Menu <strong>Magasin › INVENTAIRE › Liste inventaire</strong>. La liste est vide à l’ouverture : renseignez les filtres puis cliquez sur <strong>Rechercher</strong>.</p>
<p>Chaque inventaire affiche son numéro, sa description, ses dates d’ouverture et de clôture, le nombre de casiers et de références, la quantité comptée, le statut, le montant et les écarts : nombre de références en écart positif et négatif, % de références en écart, montant de l’écart et % d’écart. La première ligne totalise la liste.</p>
<p><strong>Menu ⋮ d’un inventaire :</strong></p>
<ul>
<li><strong>Détail :</strong> une ligne par référence avec la quantité théorique, les comptages 1, 2 et 3, l’écart, le PMP, le montant inventorié et le montant de l’écart. Le détail s’exporte en Excel et en PDF.</li>
<li><strong>Impression feuille de comptage</strong> et <strong>Voir fichier</strong> (fichier d’analyse) : réservés à certains utilisateurs. À vérifier : liste des personnes autorisées.</li>
</ul>
<p>Le bouton <strong>Excel</strong> exporte la liste filtrée.</p>
<h2>Inventaire détaillé</h2>
<p>Menu <strong>Magasin › INVENTAIRE › Inventaire détaillé</strong> : toutes les lignes d’inventaire avec leurs comptages, le nombre de bordereaux, le casier, le prix, la valeur du stock et les écarts. Export Excel disponible.</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-sortie-pieces')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-sortie-pieces', N'Sortie de pièces : soumettre un BL', 'Magasin', 4, 0, N'
<p>Menu <strong>Magasin › SORTIE DE PIECES › Nouvelle demande</strong>. Cet écran envoie un bon de livraison (BL) en validation dans DocuWare.</p>
<ol>
<li><strong>Type de BL :</strong> « BL interne » ou « Facture - BL (clients) ». Le type choisit le dossier DocuWare de destination.</li>
<li><strong>Document à soumettre :</strong> joignez le fichier (PDF, JPG, PNG, Word, Excel ou PowerPoint ; 5 Mo maximum).</li>
<li>Validez : le document est enregistré puis déposé dans DocuWare. Le message « Le document est soumis pour validation » confirme l’envoi.</li>
</ol>
<p>En cas d’erreur, le fichier joint est supprimé : recommencez la soumission.</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-devis-neg')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-devis-neg', N'Devis négoce', 'Magasin', 5, 0, N'
<p>Menu <strong>Magasin › DEMATERIALISATION › Devis</strong>. La liste suit chaque devis négoce de sa création à la commande client.</p>
<h2>Lire la liste</h2>
<p>Chaque ligne affiche le statut du devis et celui du BC, le numéro, la date de création, l’émetteur, le client, le libellé, le montant, la date d’envoi au client, les relances 1 à 3, l’arrêt des relances, la position IPS, le PO ou BC client, le créateur et la personne qui a soumis le devis.</p>
<h2>Actions (menu ⋮ d’un devis)</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Action</th><th>À quoi elle sert</th></tr></thead>
<tbody>
<tr><td>Soumission devis pour vérification prix</td><td>Envoyer le devis, avec son fichier Excel, en vérification des prix</td></tr>
<tr><td>Soumission devis pour validation</td><td>Envoyer le devis en validation</td></tr>
<tr><td>Pointage envoyé devis au client</td><td>Indiquer la date d’envoi du devis au client (quand l’action est proposée)</td></tr>
<tr><td>Pointer relance devis client</td><td>Enregistrer une relance du client ; l’historique des relances s’affiche dans la fenêtre</td></tr>
<tr><td>Soumission BC client pour validation</td><td>Envoyer le bon de commande du client, en indiquant ligne par ligne les quantités à modifier ou les lignes à supprimer</td></tr>
</tbody>
</table>
<p>Le bouton <strong>Excel</strong> du formulaire de recherche exporte la liste filtrée.</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-plannings')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-plannings', N'Plannings magasin', 'Magasin', 6, 0, N'
<p>Le menu <strong>Magasin › DEMATERIALISATION</strong> propose deux plannings, présentés par mois.</p>
<h2>Planning de commande Magasin</h2>
<p>Commandes clients du magasin, réparties par mois. À l’ouverture : année en cours, commandes en cours (non facturées), planifiées, sur 3 mois.</p>
<ul>
<li><strong>Filtres :</strong> N° devis, commercial, N° commande, PO client, client, agence et service, interne ou externe, facturé ou en cours, planifié ou non, type de lignes (pièces magasin, achats locaux, lubrifiants), commandes avec back order, BC non validé dans DocuWare, nombre de mois affichés.</li>
<li>Un clic sur la légende filtre le planning sur la catégorie choisie.</li>
<li>Vous voyez les agences autorisées sur votre profil. Les profils « multi-succursale » voient toutes les agences.</li>
</ul>
<h2>Planning Magasin</h2>
<p>Commandes fournisseurs, regroupées par fournisseur et par agence-service, et placées dans le mois de leur date de commande.</p>
<ul>
<li><strong>Filtres :</strong> fournisseur (nom ou code), numéro de commande, agence et service, période de commande, nombre de mois affichés.</li>
<li><strong>Légende cliquable :</strong> tout afficher, partiellement facturé, partiellement dispo, complet non facturé, complet facturé. Le filtre « back order » ne renvoie encore aucune commande.</li>
</ul>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-commande-fournisseur')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-commande-fournisseur', N'Soumission des commandes fournisseur', 'Magasin', 7, 0, N'
<p>Menu <strong>Magasin › COMMANDE FOURNISSEUR › Soumission commandes fournisseur</strong>. Cet écran génère le PDF d’une commande fournisseur IPS et l’envoie en validation dans DocuWare.</p>
<ol>
<li>Saisissez le <strong>numéro de commande</strong>.</li>
<li>Cliquez sur <strong>Générer le PDF</strong> et vérifiez l’aperçu.</li>
<li>Ajoutez si besoin des <strong>pièces jointes PDF</strong> (5 Mo maximum chacune). Elles sont fusionnées après le PDF de la commande.</li>
<li>Cliquez sur le bouton de validation, qui apparaît une fois le PDF généré.</li>
</ol>
<h2>Messages possibles</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>La génération du PDF a expiré ou est introuvable</td><td>Cliquez à nouveau sur Générer le PDF</td></tr>
<tr><td>Le numéro de commande a changé depuis la génération</td><td>Régénérez le PDF après avoir modifié le numéro</td></tr>
<tr><td>Ce document a déjà été soumis</td><td>Cette version est déjà dans DocuWare ; régénérez le PDF seulement si vous voulez la soumettre à nouveau</td></tr>
<tr><td>Seuls les fichiers PDF sont acceptés</td><td>Convertissez la pièce jointe en PDF</td></tr>
</tbody>
</table>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-cmd-non-placees')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-cmd-non-placees', N'Liste des commandes fournisseur non placées', 'Magasin', 8, 0, N'
<p>Menu <strong>Magasin › Liste des cmds non placées</strong>. La liste montre les commandes fournisseur qui ne sont pas encore placées. La liste est vide à l’ouverture : renseignez les filtres puis cliquez sur <strong>Rechercher</strong>.</p>
<ul>
<li><strong>Commande :</strong> toutes, atelier, magasin (négoce) ou réappro.</li>
<li><strong>Autres filtres :</strong> N° OR, période, code ou nom fournisseur, N° commande fournisseur, N° client, et <strong>OR validé</strong> (coché par défaut : seulement les OR validés).</li>
<li><strong>Colonnes :</strong> N° et date de la commande fournisseur, N° et nom du fournisseur, montant, devis, N° OR.</li>
</ul>
<p>À vérifier : définition de « placée » à préciser ici.</p>
', GETDATE(), 'import initial');

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'magasin-consultation-facture')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
VALUES ('magasin-consultation-facture', N'Consultation facture et transmission en validation', 'Magasin', 9, 0, N'
<p>Menu <strong>Magasin › Consultation facture</strong>. Cet écran liste les demandes de paiement magasin (BAP, DDPL, DDPR…) et sert à les <strong>transmettre en validation</strong> dans DocuWare.</p>
<h2>Rechercher</h2>
<p>Filtres : numéro DA, numéro de commande, numéro de livraison IPS, numéro DDP, facture BL, fournisseur, numéro CLA, et <strong>A traiter</strong> pour n’afficher que les demandes à transmettre.</p>
<h2>Transmettre en validation</h2>
<ol>
<li>Cochez les demandes à transmettre. Seules celles au statut « … à transmettre » (BAP, DDPL ou DDPR à transmettre) peuvent être cochées.</li>
<li>Cliquez sur <strong>Transmettre à validation</strong>.</li>
<li>Les demandes cochées reçoivent un même numéro CLA, passent au statut « Soumis à validation », et leur PDF est déposé dans DocuWare.</li>
</ol>
<p>Les colonnes affichent le numéro CLA, la DA, le numéro de demande, le type, le fournisseur, la commande, la livraison, la facture BL, le statut et la date de transmission.</p>
', GETDATE(), 'import initial');
