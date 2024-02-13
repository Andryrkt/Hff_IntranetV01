<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff INtranet</title>
</head>

<body>
   
    <table class=" table">
        <thead class="table-dark">
            <tr>
                <th style="text-align: center;">ID </th>
                <th style="text-align: center;">Agence - Service </th>
                <th style="text-align: center;">Utilisateur Créateur</th>
                <th style="text-align: center;">Type Document</th>
                <th style="text-align: center;">Sous Type </th>
                <th style="text-align: center;">Matricule concerné</th>
                <th style="text-align: center;">Date de demande </th>
                <th style="text-align: center;">Nombre de Jour</th>
                <th style="text-align: center;">Date début</th>
                <th style="text-align: center;">Date Fin</th>
                <th style="text-align: center;">N° DOM</th>
                <th style="text-align: center;">Statut</th>
                <th style="text-align: center;">Objet</th>
                <th style="text-align: center;">Client </th>
                <th style="text-align: center;">Lieu d'intervention</th>
                <th style="text-align: center;">Montant à payer</th>
                <th style="text-align: center;">Devise</th>
            </tr>
        </thead>
        <tbody>
            <?php   foreach ($ListDom as $ListDom) : ?>
                <tr>
                    <td><?php echo $ListDom['ID_Demande_Ordre_Mission'] ?></td>
                    <td><?php echo iconv('Windows-1252', 'UTF-8',  $ListDom['LibelleCodeAgence_Service']) ?></td>
                    <td><?php echo $ListDom['Nom_Session_Utilisateur'] ?></td>
                    <td><?php echo $ListDom['Type_Document'] ?></td>
                    <td><?php echo $ListDom['Sous_type_document'] ?></td>
                    <td><?php echo $ListDom['Matricule'] ?></td>
                    <td><?php $dateDemande = $ListDom['Date_Demande'];
                        $DDEM = date("d/m/Y", strtotime($dateDemande));
                        echo $DDEM;
                        ?></td>
                    <td><?php echo $ListDom['Nombre_Jour'] ?></td>
                    <td><?php $dateDeb = $ListDom['Date_Debut'];
                        $DD = date("d/m/Y", strtotime($dateDeb));
                        echo $DD;
                        ?></td>
                    <td><?php $dateFin = $ListDom['Date_Fin'];
                        $DF = date("d/m/Y", strtotime($dateFin));
                        echo $DF;
                        ?></td>
                    <td><a href="/Hffintranet/index.php?action=DetailDOM&NumDom=<?php echo $ListDom['Numero_Ordre_Mission'] ?> "> <?php echo $ListDom['Numero_Ordre_Mission'] ?> </a></td>
                    <td><?php echo $ListDom['Statut'] ?></td>
                    <td><?php echo $ListDom['Motif_Deplacement'] ?></td>
                    <td><?php echo $ListDom['Client'] ?></td>
                    <td><?php echo $ListDom['Lieu_Intervention'] ?></td>
                    <td><?php echo $ListDom['Total_General_Payer'] ?></td>
                    <td><?php echo $ListDom['Devis'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>