-- ============================================================
-- documentation_utilisateur : pages d'aide des modules, lues par tous
-- et modifiées par les administrateurs (/doc/utilisateur/{slug}/modifier).
-- Sans effet si la table ou la page existe déjà.
-- ============================================================
IF OBJECT_ID ('documentation_utilisateur', 'U') IS NULL
    CREATE TABLE documentation_utilisateur
    (
        id INT IDENTITY (1, 1) NOT NULL,
        slug NVARCHAR(100) NOT NULL,
        titre NVARCHAR(255) NOT NULL,
        contenu NVARCHAR(MAX) NULL,
        date_modification DATETIME2 (6) NULL,
        modifie_par NVARCHAR(100) NULL,
        CONSTRAINT PK_documentation_utilisateur PRIMARY KEY (id),
        CONSTRAINT UQ_documentation_utilisateur_slug UNIQUE (slug)
    );
GO

-- Page initiale : guide de la demande de paiement magasin
IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin')
    INSERT INTO documentation_utilisateur (slug, titre, contenu, date_modification, modifie_par)
    VALUES ('ddp-magasin', N'Guide utilisateur – Demande de paiement magasin', N'
<h2>Présentation</h2>
<p>La demande de paiement magasin sert à faire payer un fournisseur pour une ou plusieurs commandes fournisseur. Elle se remplit dans l''intranet, génère un PDF et l''envoie dans DocuWare pour validation.</p>
<table border="1" cellpadding="4">
<thead><tr><th>Formulaire</th><th>Quand l''utiliser</th><th>Ce que vous saisissez</th></tr></thead>
<tbody>
<tr><td><strong>Paiement à l''avance</strong> (DDPA)</td><td>La marchandise n''est pas encore arrivée ; le fournisseur demande un acompte sur facture proforma</td><td>Une commande, le montant facture proforma, le montant total du commande</td></tr>
<tr><td><strong>Paiement après arrivage</strong></td><td>La marchandise est arrivée et la facture fournisseur est dans GCOT</td><td>Une ou plusieurs factures, et pour chaque commande le montant du commande à payer</td></tr>
</tbody>
</table>
<p>Le formulaire « après arrivage » choisit tout seul le bon type de document (BAP, DDPR ou DDPL) selon les acomptes déjà payés sur chaque commande. Vous n''avez rien à choisir.</p>

<h2>Paiement à l''avance (DDPA)</h2>
<p>Utilisez ce formulaire pour payer un acompte sur une commande validée mais pas encore réceptionnée. Une demande ne concerne qu''une commande.</p>
<ol>
<li><strong>Fournisseur</strong> : tapez le numéro ou le nom, puis choisissez dans la liste. Le RIB et les commandes se chargent.</li>
<li><strong>Mode de paiement, devise, motif</strong> : complétez-les.</li>
<li><strong>N° commande</strong> : choisissez la commande. Seules les commandes validées et pas encore réceptionnées apparaissent.</li>
<li>L''<strong>historique des demandes de paiement</strong> de cette commande s''affiche en haut : demandes déjà faites, % de chacune, cumul, total déjà validé et reste.</li>
<li><strong>Montant facture proforma *</strong> : le montant à payer au fournisseur, tel qu''il figure sur la proforma.</li>
<li><strong>Montant total du commande *</strong> : le montant total de la commande.</li>
<li><strong>Pièces jointes</strong> : la proforma facture fournisseur et le RIB fournisseur sont attendus ; le BC client et les autres documents sont facultatifs.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<p>Le pourcentage payé de la demande vaut montant facture proforma ÷ montant total du commande × 100. Le montant saisi n''est pas plafonné.</p>
<p><strong>Blocage :</strong> si les demandes à l''avance déjà validées atteignent 100 % de la commande, l''enregistrement est refusé. La commande est soldée par acomptes ; le reste se traite par une demande après arrivage.</p>

<h2>Paiement après arrivage</h2>
<p>Utilisez ce formulaire quand la marchandise est arrivée : vous partez des factures fournisseur, les commandes suivent.</p>
<ol>
<li><strong>Fournisseur, mode de paiement, devise, motif</strong> : comme pour le paiement à l''avance.</li>
<li><strong>N° facture fournisseur</strong> : choisissez une ou plusieurs factures. Les commandes correspondantes sont sélectionnées automatiquement.</li>
<li>Le <strong>tableau des commandes</strong> apparaît, une ligne par commande (colonnes ci-dessous).</li>
<li>Dans chaque ligne, saisissez le <strong>montant du commande à payer</strong>. Il est obligatoire pour toutes les commandes.</li>
<li><strong>Montant à payer *</strong> : il doit être égal au total des factures choisies.</li>
<li><strong>Pièces jointes</strong> : le contrôle livraison et le RIB fournisseur sont attendus ; les documents de douane et de commande DocuWare sont ajoutés automatiquement.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<table border="1" cellpadding="4">
<thead><tr><th>Colonne</th><th>Ce qu''elle montre</th><th>À saisir</th></tr></thead>
<tbody>
<tr><td>N° commande</td><td>La commande liée aux factures choisies</td><td>Non</td></tr>
<tr><td>N° facture</td><td>La ou les factures de cette commande, une par ligne</td><td>Non</td></tr>
<tr><td>Montant facture</td><td>Le montant de chaque facture (GCOT)</td><td>Non</td></tr>
<tr><td>DDPA</td><td>Le % de la commande déjà payé par des demandes à l''avance validées</td><td>Non</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande (voir section suivante)</td><td>Non</td></tr>
<tr><td>DDP à générer</td><td>Le document qui sera créé : BAP, DDPR ou DDPL</td><td>Non</td></tr>
<tr><td>Montant du commande à payer *</td><td>Le montant à payer pour cette commande</td><td><strong>Oui</strong></td></tr>
</tbody>
</table>

<h2>Ce qui est créé à l''enregistrement (après arrivage)</h2>
<p>À l''enregistrement, chaque commande reçoit le document qui correspond aux acomptes déjà validés sur elle.</p>
<table border="1" cellpadding="4">
<thead><tr><th>% DDPA validé de la commande</th><th>Document créé</th><th>PDF</th><th>Statut</th><th>Solde avance</th></tr></thead>
<tbody>
<tr><td>0 %</td><td><strong>BAP</strong> – bon à payer</td><td>BAP MAGASIN</td><td>BAP à transmettre</td><td>0</td></tr>
<tr><td>Entre 0 et 100 %</td><td><strong>DDPL</strong> – après arrivage</td><td>DDP</td><td>DDPL à transmettre</td><td>Calculé</td></tr>
<tr><td>100 %</td><td><strong>DDPR</strong> – paiement régul</td><td>DDP</td><td>DDPR à transmettre</td><td>0</td></tr>
</tbody>
</table>
<ul>
<li><strong>Plusieurs situations dans une saisie :</strong> les commandes sont regroupées par type. Une demande est créée par groupe, avec son propre numéro, ses factures, son PDF et une copie de vos pièces jointes.</li>
<li><strong>Montant à payer d''une demande :</strong> le total des factures de son groupe.</li>
<li><strong>PDF :</strong> la page de garde (BAP MAGASIN ou DDP) est fusionnée avec toutes les pièces jointes, puis déposée dans DocuWare.</li>
<li><strong>BAP MAGASIN :</strong> résumé du BC, info validation BC, livraisons, demandes de paiement déjà faites, factures fournisseur, montant BAP et son %.</li>
</ul>
<p>Pour une DDPL, le solde avance de chaque commande se calcule ainsi :</p>
<p><strong>Solde avance = Montant total du commande − (somme des proformas des DDPA + montant déjà payé)</strong></p>
<p>Le montant total du commande est celui de la DDPA la plus récente ; le montant déjà payé additionne les montants du commande à payer des demandes validées qui ne sont pas des DDPA. Le solde est enregistré et imprimé sur le PDF.</p>

<h2>Messages d''erreur et que faire</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Cause</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>La commande X est déjà payée à 100 % par des demandes de paiement à l''avance validées</td><td>Paiement à l''avance sur une commande déjà soldée par acomptes</td><td>Ne pas faire de nouvel acompte ; utiliser le paiement après arrivage à la réception</td></tr>
<tr><td>Le montant total du commande est obligatoire</td><td>Champ vide (paiement à l''avance)</td><td>Saisir le montant total de la commande</td></tr>
<tr><td>Le montant du commande à payer X est obligatoire</td><td>Une ligne du tableau est vide (après arrivage)</td><td>Saisir un montant sur chaque ligne</td></tr>
<tr><td>Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s)</td><td>Le montant à payer diffère du total des factures (après arrivage)</td><td>Saisir exactement le total des factures choisies</td></tr>
<tr><td>La facture X concerne des commandes de situations différentes</td><td>Une même facture couvre des commandes qui donneraient des documents différents (ex. une en BAP, une en DDPL)</td><td>Contacter la comptabilité pour traiter cette facture à part</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
</tbody>
</table>
<p>Après une erreur, le formulaire se recharge : resaisissez les montants du tableau des commandes.</p>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>BAP</td><td>Bon à payer : paiement après arrivage d''une commande sans acompte validé</td></tr>
<tr><td>DDPR</td><td>Demande de paiement régul : paiement après arrivage d''une commande déjà payée à 100 % par acomptes</td></tr>
<tr><td>DDPL</td><td>Demande de paiement après arrivage d''une commande partiellement payée par acomptes</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur une commande (calcul dans la section précédente)</td></tr>
</tbody>
</table>
<p><strong>Pourquoi ai-je reçu plusieurs numéros de demande pour une seule saisie ?</strong> Vos factures couvraient des commandes de situations différentes. Une demande est créée par type de document ; tous les numéros s''affichent dans le message de confirmation.</p>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, la commande part en BAP.</p>
<p><strong>Puis-je choisir moi-même BAP, DDPR ou DDPL ?</strong> Non. Le type dépend uniquement des acomptes validés sur la commande ; la colonne « DDP à générer » l''annonce avant l''enregistrement.</p>
', GETDATE(), 'import initial');
