-- ============================================================
-- documentation_utilisateur : le guide 'ddp-magasin' est séparé en deux,
-- 'ddp-magasin-avance' et 'ddp-magasin-apres-arrivage'.
-- Les deux pages reprennent la rubrique et la publication de l'ancienne,
-- qui repasse en brouillon (conservée, visible des administrateurs).
-- À exécuter après documentation_utilisateur_rubrique_publication.sql.
-- Rejouable : une page déjà créée n'est pas modifiée.
-- ============================================================
IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-avance')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
SELECT 'ddp-magasin-avance', N'Demande de paiement magasin – à l''avance', rubrique, 0, publie, N'
<h2>Présentation</h2>
<p>La demande de paiement à l''avance (DDPA) sert à payer un acompte à un fournisseur, sur facture proforma, quand la marchandise n''est pas encore arrivée. Une demande ne concerne qu''une commande, validée mais pas encore réceptionnée.</p>
<p>Elle se remplit dans l''intranet et génère un PDF, puis vous la transmettez en validation dans DocuWare depuis l''écran Consultation facture.</p>
<p>Si la marchandise est arrivée, utilisez plutôt la <a href="ddp-magasin-apres-arrivage">demande de paiement après arrivage</a>.</p>

<h2>Remplir la demande</h2>
<p>Menu <strong>Compta › Demande de paiement › Nouvelle demande de paiement à l''avance</strong>.</p>
<ol>
<li><strong>Fournisseur</strong> : tapez le numéro ou le nom, puis choisissez dans la liste. Le RIB et les commandes se chargent.</li>
<li><strong>Mode de paiement, devise, motif</strong> : complétez-les.</li>
<li><strong>N° commande</strong> : choisissez la commande. Seules les commandes validées et pas encore réceptionnées apparaissent, sans demande à l''avance en cours et pas encore payées à 100 %. Une commande revient dans la liste si sa demande est refusée. Une avance peut se payer en plusieurs fois : une fois la demande validée, la commande est de nouveau proposée tant qu''elle n''est pas payée à 100 %.</li>
<li>L''<strong>historique des demandes de paiement</strong> de cette commande s''affiche dans l''onglet « Historique » : demandes déjà faites, % de chacune, cumul, total déjà validé et reste.</li>
<li><strong>N° facture proforma *</strong> : le numéro inscrit sur la proforma (50 caractères maximum). Il est enregistré avec la demande et imprimé sur le PDF.</li>
<li><strong>Montant facture proforma *</strong> : le montant à payer au fournisseur, tel qu''il figure sur la proforma.</li>
<li><strong>Montant total du commande *</strong> : rempli automatiquement depuis IPS dès que vous choisissez la commande. Il n''est pas modifiable, et sans montant dans IPS l''enregistrement est bloqué.</li>
<li><strong>Pièces jointes</strong> : la proforma facture fournisseur et le RIB fournisseur sont attendus ; les autres documents sont facultatifs. Les BC client rattachés à la commande s''affichent dans leur onglet ; leur dépôt manuel est désactivé.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<p>Le pourcentage payé de la demande vaut montant facture proforma ÷ montant total du commande × 100. Le montant facture proforma ne peut pas dépasser 20 000.</p>
<p><strong>Blocage :</strong> si les demandes à l''avance déjà validées atteignent 100 % de la commande, l''enregistrement est refusé. La commande est soldée par acomptes ; le reste se traite par une demande après arrivage. L''enregistrement est aussi refusé si votre demande ferait dépasser 100 % à leur cumul : le message indique alors le montant maximum.</p>

<h2>Après l''enregistrement : transmettre en validation</h2>
<p>L''enregistrement ne lance pas la validation : la demande reste au statut « DDPA à transmettre » tant qu''elle n''est pas transmise.</p>
<ol>
<li>Après l''enregistrement, vous arrivez sur l''écran <strong>Magasin › Consultation facture</strong>.</li>
<li>Cochez les demandes à transmettre, puis cliquez sur <strong>Transmettre à validation</strong>.</li>
<li>Les demandes cochées reçoivent un même numéro CLA, passent au statut « Soumis à validation », et leur PDF est déposé dans DocuWare.</li>
</ol>

<h2>Messages d''erreur et que faire</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Cause</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>La commande X est déjà payée à 100 % par des demandes de paiement à l''avance validées</td><td>La commande est déjà soldée par acomptes</td><td>Ne pas faire de nouvel acompte ; utiliser le paiement après arrivage à la réception</td></tr>
<tr><td>La commande X a déjà une demande de paiement à l''avance en cours</td><td>Une seule demande à l''avance peut être en cours par commande</td><td>Attendre sa validation ou son refus, puis refaire la demande si besoin</td></tr>
<tr><td>Le montant facture proforma de la commande X porte le cumul des demandes de paiement à l''avance validées à Y % : il ne doit pas dépasser Z</td><td>Avec les demandes déjà validées, cette demande dépasserait 100 % de la commande</td><td>Vérifier la proforma et saisir un montant inférieur ou égal à Z</td></tr>
<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td><td>Le plafond est dépassé</td><td>Saisir un montant inférieur ou égal au plafond</td></tr>
<tr><td>Le montant total de cette commande est introuvable dans IPS pour ce fournisseur</td><td>La commande n''existe pas pour ce fournisseur dans IPS, ou son montant est nul. L''enregistrement est bloqué</td><td>Vérifier le fournisseur et la commande dans IPS</td></tr>
<tr><td>Aucune demande DDP/BAP à transmettre n''a été reçue</td><td>La transmission n''a reçu aucun numéro de demande. Rien n''est transmis</td><td>Recharger la page, cocher de nouveau les demandes, puis signaler le problème s''il revient</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
</tbody>
</table>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
</tbody>
</table>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, elle ne compte ni pour le blocage à 100 %, ni pour le paiement après arrivage, où son montant n''est pas déduit. En revanche, elle empêche de créer une autre demande à l''avance sur la même commande.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

IF NOT EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
INSERT INTO documentation_utilisateur (slug, titre, rubrique, ordre, publie, contenu, date_modification, modifie_par)
SELECT 'ddp-magasin-apres-arrivage', N'Demande de paiement magasin – après arrivage', rubrique, 1, publie, N'
<h2>Présentation</h2>
<p>La demande de paiement après arrivage sert à payer un fournisseur quand la marchandise est arrivée et que sa facture est dans GCOT. Vous partez des factures, les commandes suivent.</p>
<p>Elle se remplit dans l''intranet et génère un PDF, puis vous la transmettez en validation dans DocuWare depuis l''écran Consultation facture.</p>
<p>Une saisie crée une seule demande, même si elle couvre plusieurs factures et commandes. Le montant à payer tient compte, commande par commande, des acomptes déjà validés.</p>
<p>Si la marchandise n''est pas encore arrivée, utilisez plutôt la <a href="ddp-magasin-avance">demande de paiement à l''avance</a>.</p>

<h2>Remplir la demande</h2>
<p>Menu <strong>Compta › Demande de paiement › Nouvelle demande de paiement après arrivage</strong>.</p>
<ol>
<li><strong>Fournisseur, mode de paiement, devise, motif</strong> : tapez le numéro ou le nom du fournisseur, choisissez-le dans la liste, puis complétez les autres champs.</li>
<li><strong>N° facture fournisseur</strong> : choisissez une ou plusieurs factures. Seules sont proposées les factures liées à une commande réceptionnée et dont toutes les commandes sont validées dans DocuWare. Les commandes correspondantes sont sélectionnées automatiquement. Une facture qui a déjà une demande en cours ou validée n''est plus proposée ; elle revient dans la liste si sa demande est refusée.</li>
<li>Le <strong>tableau des commandes</strong> apparaît, une ligne par commande (colonnes ci-dessous).</li>
<li>Dans chaque ligne, vérifiez le <strong>montant commande réceptionnée</strong> : il vient d''IPS et n''est pas modifiable. Le pourcentage en rouge, devant le montant, est sa part du montant total de la commande. Si IPS ne trouve aucun montant pour une commande, « - » s''affiche et l''enregistrement est bloqué.</li>
<li><strong>Montant total réceptionné *</strong> : il doit être égal à la somme des montants commande réceptionnée du tableau. Le <strong>montant total à payer</strong> affiche la somme des montants commande à payer. Vous pouvez le modifier : le montant saisi devient le montant de la demande. Videz le champ pour revenir au calcul. Le pourcentage en rouge à côté de ces deux libellés est leur part du montant total des commandes.</li>
<li><strong>Pièces jointes</strong> : le contrôle livraison et le RIB fournisseur sont attendus ; les documents de douane et de commande DocuWare sont ajoutés automatiquement. Les autres documents sont facultatifs.</li>
<li>Cliquez sur <strong>Enregistrer</strong> et confirmez.</li>
</ol>
<table border="1" cellpadding="4">
<thead><tr><th>Colonne</th><th>Ce qu''elle montre</th><th>À saisir</th></tr></thead>
<tbody>
<tr><td>N° commande</td><td>La commande liée aux factures choisies</td><td>Non</td></tr>
<tr><td>N° facture</td><td>La ou les factures de cette commande, une par ligne</td><td>Non</td></tr>
<tr><td>N° dossier douane, N° LTA, N° HAWB</td><td>Les références du dossier de douane de chaque facture</td><td>Non</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande, en gras et en vert (voir section suivante)</td><td>Non</td></tr>
<tr><td>Montant commande réceptionnée</td><td>Le montant réceptionné pour cette commande dans IPS, précédé de son % du montant total de la commande</td><td>Non</td></tr>
<tr><td>Montant commande à payer</td><td>Précédé de son % du montant total de la commande. Calculé : 0 si le solde avance est supérieur à 0 et couvre le montant réceptionné ; sinon montant réceptionné − solde avance. Toujours 0 si la commande est déjà payée à 100 % par acomptes</td><td>Non</td></tr>
<tr><td>Montant total du commande</td><td>Le montant total de la commande dans IPS. « - » s''il est introuvable</td><td>Non</td></tr>
</tbody>
</table>

<h2>Ce qui est créé à l''enregistrement</h2>
<p>Une saisie crée une seule demande, avec un seul numéro et une seule page de garde, quels que soient le nombre de factures et de commandes. Son type se décide automatiquement à l''enregistrement : si le montant total à payer est à 0, demande de paiement régule (DDPR) ; sinon, si la somme des soldes avance est à 0, bon à payer (BAP) ; sinon, demande de paiement après arrivage (DDPL). Son statut est alors « DDPR à transmettre », « BAP à transmettre » ou « DDPL à transmettre ».</p>
<p>Le montant à payer se calcule commande par commande, selon les acomptes déjà validés :</p>
<table border="1" cellpadding="4">
<thead><tr><th>% DDPA validé de la commande</th><th>Solde avance</th><th>Montant commande à payer</th></tr></thead>
<tbody>
<tr><td>0 %</td><td>0</td><td>Le montant réceptionné</td></tr>
<tr><td>Entre 0 et 100 %</td><td>Calculé</td><td>0 si le solde couvre le montant réceptionné, sinon montant réceptionné − solde avance</td></tr>
<tr><td>100 %</td><td>Calculé</td><td>0. L''enregistrement est refusé si le montant réceptionné dépasse le solde</td></tr>
</tbody>
</table>
<ul>
<li><strong>Montant à payer de la demande :</strong> le total des montants commande à payer, ou le montant que vous avez saisi. Une demande à 0 passe quand même en validation.</li>
<li><strong>PDF :</strong> la page de garde reprend le résumé des BC, le récapitulatif des livraisons et celui des demandes de paiement déjà enregistrées (y compris refusées, avec leur statut), puis est fusionnée avec toutes les pièces jointes. Les pourcentages sont imprimés en rouge devant les montants : dans le résumé des BC et dans la case « Montant à payer ». Le PDF n''est déposé dans DocuWare qu''à la transmission.</li>
</ul>
<p>Le solde avance de chaque commande se calcule ainsi :</p>
<p><strong>Solde avance = somme des proformas des DDPA − montant déjà payé</strong>, ramené à 0 s''il est négatif. Une réception non validée ne réduit pas le solde. L''historique des demandes de paiement de chaque commande est affiché dans l''onglet « Historique ».</p>
<p>Le montant déjà payé additionne les montants commande réceptionnée des demandes validées qui ne sont pas des DDPA. Le solde et le montant commande à payer sont enregistrés et imprimés sur le PDF, avec le solde avance restant : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0.</p>

<h2>Après l''enregistrement : transmettre en validation</h2>
<p>L''enregistrement ne lance pas la validation : la demande reste au statut « … à transmettre » tant qu''elle n''est pas transmise.</p>
<ol>
<li>Après l''enregistrement, vous arrivez sur l''écran <strong>Magasin › Consultation facture</strong>.</li>
<li>Cochez les demandes à transmettre, puis cliquez sur <strong>Transmettre à validation</strong>.</li>
<li>Les demandes cochées reçoivent un même numéro CLA, passent au statut « Soumis à validation », et leur PDF est déposé dans DocuWare.</li>
</ol>

<h2>Messages d''erreur et que faire</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Message</th><th>Cause</th><th>Que faire</th></tr></thead>
<tbody>
<tr><td>Le montant réceptionné de la commande X est introuvable dans IPS pour la/les facture(s) sélectionnée(s)</td><td>IPS ne trouve aucune réception de cette commande pour ces factures. L''enregistrement est bloqué</td><td>Vérifier le numéro de facture dans GCOT et IPS, ainsi que la réception</td></tr>
<tr><td>Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s)</td><td>Le montant total réceptionné diffère de la somme des montants commande réceptionnée</td><td>Saisir exactement la somme du tableau</td></tr>
<tr><td>La facture X a déjà une demande de paiement en cours ou validée (DDP…)</td><td>Une facture ne se paie qu''une fois. Cette demande n''est pas refusée</td><td>Retirer la facture. Si la demande citée est refusée, la facture peut être soumise de nouveau</td></tr>
<tr><td>Le montant réceptionné de la commande X dépasse son solde avance</td><td>La commande est payée à 100 % par acomptes et cette réception dépasse ce qui reste de l''avance</td><td>Vérifier la réception et les demandes déjà validées dans l''onglet « Historique »</td></tr>
<tr><td>La facture X est liée à la commande Y, qui n''est pas validée dans DocuWare</td><td>Toutes les commandes d''une facture doivent être validées</td><td>Attendre la validation de la commande Y, puis refaire la demande</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td><td>Le PDF de la commande fournisseur validée est introuvable</td><td>Vérifier que la commande est bien validée et déposée dans DocuWare</td></tr>
<tr><td>Aucune demande DDP/BAP à transmettre n''a été reçue</td><td>La transmission n''a reçu aucun numéro de demande. Rien n''est transmis</td><td>Recharger la page, cocher de nouveau les demandes, puis signaler le problème s''il revient</td></tr>
</tbody>
</table>
<p>Après une erreur, le formulaire se recharge : vérifiez vos saisies, notamment le montant total réceptionné.</p>

<h2>Glossaire et questions fréquentes</h2>
<table border="1" cellpadding="4">
<thead><tr><th>Terme</th><th>Sens</th></tr></thead>
<tbody>
<tr><td>DDPL, DDPR, BAP</td><td>Demande de paiement après arrivage, demande de paiement régule et bon à payer : types attribués par ce formulaire selon les montants</td></tr>
<tr><td>DDPA</td><td>Demande de paiement à l''avance : acompte sur facture proforma, avant l''arrivée de la marchandise</td></tr>
<tr><td>% DDPA</td><td>Montant facture proforma ÷ montant total du commande × 100, cumulé sur les DDPA validées</td></tr>
<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur une commande (calcul dans la section « Ce qui est créé à l''enregistrement »)</td></tr>
</tbody>
</table>
<p><strong>Une demande à l''avance en attente de validation compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, son montant n''est pas déduit du montant à payer.</p>
<p><strong>Pourquoi une facture n''apparaît-elle pas dans la liste ?</strong> Elle a déjà une demande non refusée (cherchez-la dans Consultation facture), l''une de ses commandes n''est pas validée dans DocuWare, ou elle est exclue par le filtre comptable. Elle revient dans la liste si sa demande est refusée ou quand toutes ses commandes sont validées.</p>
', GETDATE(), 'script separation guide'
FROM documentation_utilisateur WHERE slug = 'ddp-magasin';

-- L'ancienne page n'est plus proposée aux utilisateurs une fois les deux pages créées
IF EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-avance')
    AND EXISTS (SELECT 1 FROM documentation_utilisateur WHERE slug = 'ddp-magasin-apres-arrivage')
    UPDATE documentation_utilisateur SET publie = 0 WHERE slug = 'ddp-magasin';

-- Consultation facture : écran de transmission indiqué par les deux pages ci-dessus.
-- Publication et ajout des DDPA, qui peuvent aussi être transmises depuis cet écran.
UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu, N'(BAP, DDPL, DDPR', N'(DDPA, BAP, DDPL, DDPR'), N'(BAP, DDPL ou DDPR', N'(DDPA, BAP, DDPL ou DDPR'),
    publie = 1
WHERE slug = 'magasin-consultation-facture';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu,
        N'<p>Filtres : numéro DA, numéro de commande, numéro de livraison IPS, numéro DDP, facture BL, fournisseur, numéro CLA, et',
        N'<p>Filtres : numéro CLA, facture fournisseur, type de demande, numéro DA, numéro de commande, numéro de livraison IPS, numéro DDP, fournisseur, et'),
        N'<p>Les colonnes affichent le numéro CLA, la DA, le numéro de demande, le type, le fournisseur, la commande, la livraison, la facture BL, le statut et',
        N'<p>Les colonnes affichent le numéro CLA, la facture fournisseur, la DA, le numéro de demande, le type, le motif, le fournisseur, la commande, la livraison, le statut et')
WHERE slug = 'magasin-consultation-facture';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'Tant qu''elle n''est pas validée, son montant n''est pas déduit du montant à payer.</p>',
        N'Tant qu''elle n''est pas validée, son montant n''est pas déduit du montant à payer.</p>
<p><strong>Pourquoi une facture n''apparaît-elle pas dans la liste ?</strong> Elle a déjà une demande non refusée (cherchez-la dans Consultation facture), l''une de ses commandes n''est pas validée dans DocuWare, ou elle est exclue par le filtre comptable. Elle revient dans la liste si sa demande est refusée ou quand toutes ses commandes sont validées.</p>')
WHERE slug = 'ddp-magasin-apres-arrivage'
    AND contenu NOT LIKE N'%Pourquoi une facture n''apparaît-elle pas%';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande (voir section suivante)</td><td>Non</td></tr>',
        N'<tr><td>Solde avance</td><td>Ce qui reste de l''avance sur la commande, en gras et en vert (voir section suivante)</td><td>Non</td></tr>')
WHERE slug = 'ddp-magasin-apres-arrivage';

-- Pages déjà créées : les INSERT ci-dessus ne les modifient pas. Chaque REPLACE ne trouve plus rien une fois appliqué.
UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu, N'de cette commande s''affiche en haut : demandes déjà faites,', N'de cette commande s''affiche dans l''onglet « Historique » : demandes déjà faites,')
WHERE slug = 'ddp-magasin-avance';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'<li><strong>Montant facture proforma *</strong> : le montant à payer',
        N'<li><strong>N° facture proforma *</strong> : le numéro inscrit sur la proforma (50 caractères maximum). Il est enregistré avec la demande et imprimé sur le PDF.</li>
<li><strong>Montant facture proforma *</strong> : le montant à payer')
WHERE slug = 'ddp-magasin-avance'
    AND contenu NOT LIKE N'%N° facture proforma%';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu,
        N'Seules les commandes validées et pas encore réceptionnées apparaissent.</li>',
        N'Seules les commandes validées et pas encore réceptionnées apparaissent, sans demande à l''avance en cours et pas encore payées à 100 %. Une commande revient dans la liste si sa demande est refusée. Une avance peut se payer en plusieurs fois : une fois la demande validée, la commande est de nouveau proposée tant qu''elle n''est pas payée à 100 %.</li>'),
        N'compte-t-elle ?</strong> Non. Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, elle ne compte ni pour le blocage à 100 %, ni pour le paiement après arrivage, où son montant n''est pas déduit.</p>',
        N'compte-t-elle ?</strong> Seules les DDPA validées (statut Validé ou Transmis compta) entrent dans le % DDPA. Tant qu''elle n''est pas validée, elle ne compte ni pour le blocage à 100 %, ni pour le paiement après arrivage, où son montant n''est pas déduit. En revanche, elle empêche de créer une autre demande à l''avance sur la même commande.</p>')
WHERE slug = 'ddp-magasin-avance';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td>',
        N'<tr><td>La commande X a déjà une demande de paiement à l''avance en cours</td><td>Une seule demande à l''avance peut être en cours par commande</td><td>Attendre sa validation ou son refus, puis refaire la demande si besoin</td></tr>
<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td>')
WHERE slug = 'ddp-magasin-avance'
    AND contenu NOT LIKE N'%a déjà une demande de paiement à l''avance en cours%';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu,
        N'le reste se traite par une demande après arrivage.</p>',
        N'le reste se traite par une demande après arrivage. L''enregistrement est aussi refusé si votre demande ferait dépasser 100 % à leur cumul : le message indique alors le montant maximum.</p>'),
        N'<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td>',
        N'<tr><td>Le montant facture proforma de la commande X porte le cumul des demandes de paiement à l''avance validées à Y % : il ne doit pas dépasser Z</td><td>Avec les demandes déjà validées, cette demande dépasserait 100 % de la commande</td><td>Vérifier la proforma et saisir un montant inférieur ou égal à Z</td></tr>
<tr><td>Le montant facture proforma ne doit pas dépasser 20 000</td>')
WHERE slug = 'ddp-magasin-avance'
    AND contenu NOT LIKE N'%porte le cumul des demandes%';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(REPLACE(REPLACE(contenu,
        N'<tr><td>100 %</td><td>0</td><td>0</td></tr>',
        N'<tr><td>100 %</td><td>Calculé</td><td>0. L''enregistrement est refusé si le montant réceptionné dépasse le solde</td></tr>'),
        N'<p><strong>Solde avance = Montant total du commande − (somme des proformas des DDPA + montant déjà payé)</strong>, ramené à 0 s''il est négatif.</p>',
        N'<p><strong>Solde avance = somme des proformas des DDPA − montant déjà payé</strong>, ramené à 0 s''il est négatif. Une réception non validée ne réduit pas le solde. L''historique des demandes de paiement de chaque commande est affiché dans l''onglet « Historique ».</p>'),
        N'<p>Le montant total du commande est celui de la DDPA la plus récente ; le montant déjà payé additionne',
        N'<p>Le montant déjà payé additionne'),
        N'Les commandes correspondantes sont sélectionnées automatiquement.</li>',
        N'Les commandes correspondantes sont sélectionnées automatiquement. Une facture qui a déjà une demande en cours ou validée n''est plus proposée ; elle revient dans la liste si sa demande est refusée.</li>')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'choisissez une ou plusieurs factures. Les commandes correspondantes',
        N'choisissez une ou plusieurs factures. Seules sont proposées les factures liées à une commande réceptionnée et dont toutes les commandes sont validées dans DocuWare. Les commandes correspondantes')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td>',
        N'<tr><td>La facture X est liée à la commande Y, qui n''est pas validée dans DocuWare</td><td>Toutes les commandes d''une facture doivent être validées</td><td>Attendre la validation de la commande Y, puis refaire la demande</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td>')
WHERE slug = 'ddp-magasin-apres-arrivage'
    AND contenu NOT LIKE N'%qui n''est pas validée dans DocuWare%';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contenu, N'<tr><td>Solde avance restant</td><td>Calculé : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0</td><td>Non</td></tr>', N'<tr><td>Montant total du commande</td><td>Le montant total de la commande dans IPS. « - » s''il est introuvable</td><td>Non</td></tr>'), N'Le solde et le montant du commande à payer sont enregistrés et imprimés sur le PDF.', N'Le solde et le montant commande à payer sont enregistrés et imprimés sur le PDF, avec le solde avance restant : solde avance − montant réceptionné si le solde couvre le montant réceptionné, sinon 0.'), N'<strong>montant du commande réceptionné</strong>', N'<strong>montant commande réceptionnée</strong>'), N'<tr><td>Montant du commande réceptionné</td>', N'<tr><td>Montant commande réceptionnée</td>'), N'<tr><td>Montant du commande à payer</td>', N'<tr><td>Montant commande à payer</td>'), N'<th>Montant du commande à payer</th>', N'<th>Montant commande à payer</th>'), N'montants du commande réceptionnés', N'montants commande réceptionnée'), N'montants du commande à payer', N'montants commande à payer'), N'montant du commande réceptionné', N'montant commande réceptionnée'), N'montant du commande à payer', N'montant commande à payer')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu,
        N'</strong>, non modifiable, affiche la somme des montants commande à payer. Le pourcentage',
        N'</strong> affiche la somme des montants commande à payer. Vous pouvez le modifier : le montant saisi devient le montant de la demande. Videz le champ pour revenir au calcul. Le pourcentage'),
        N'</strong> le total des montants commande à payer. Une demande',
        N'</strong> le total des montants commande à payer, ou le montant que vous avez saisi. Une demande')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(REPLACE(contenu,
        N'<p>Une saisie crée une seule demande de paiement (DDPL) au statut « DDPL à transmettre », avec un seul numéro et une seule page de garde, quels que soient le nombre de factures et de commandes.</p>',
        N'<p>Une saisie crée une seule demande, avec un seul numéro et une seule page de garde, quels que soient le nombre de factures et de commandes. Son type se décide automatiquement à l''enregistrement : si le montant total à payer est à 0, demande de paiement régule (DDPR) ; sinon, si la somme des soldes avance est à 0, bon à payer (BAP) ; sinon, demande de paiement après arrivage (DDPL). Son statut est alors « DDPR à transmettre », « BAP à transmettre » ou « DDPL à transmettre ».</p>'),
        N'<tr><td>DDPL</td><td>Demande de paiement après arrivage, créée par ce formulaire</td></tr>',
        N'<tr><td>DDPL, DDPR, BAP</td><td>Demande de paiement après arrivage, demande de paiement régule et bon à payer : types attribués par ce formulaire selon les montants</td></tr>')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'celui des demandes de paiement déjà enregistrées, puis est fusionnée',
        N'celui des demandes de paiement déjà enregistrées (y compris refusées, avec leur statut), puis est fusionnée')
WHERE slug = 'ddp-magasin-apres-arrivage';

UPDATE documentation_utilisateur
SET contenu = REPLACE(contenu,
        N'<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td>',
        N'<tr><td>La facture X a déjà une demande de paiement en cours ou validée (DDP…)</td><td>Une facture ne se paie qu''une fois. Cette demande n''est pas refusée</td><td>Retirer la facture. Si la demande citée est refusée, la facture peut être soumise de nouveau</td></tr>
<tr><td>Le montant réceptionné de la commande X dépasse son solde avance</td><td>La commande est payée à 100 % par acomptes et cette réception dépasse ce qui reste de l''avance</td><td>Vérifier la réception et les demandes déjà validées dans l''onglet « Historique »</td></tr>
<tr><td>Bouton Enregistrer grisé et message rouge sous la commande</td>')
WHERE slug = 'ddp-magasin-apres-arrivage'
    AND contenu NOT LIKE N'%a déjà une demande de paiement en cours%';
