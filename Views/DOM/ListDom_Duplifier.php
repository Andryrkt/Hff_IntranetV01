<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff INtranet</title>
</head>

<style>
    #Contenue {
        width: 100%;
        max-height: 800px;
        overflow-y: auto;
    }

    th {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
    }
</style>

<body>
    <div class="row">

        <form action="">
            <div class="row">
                <div class="col-2">
                    <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                        <span class="input-group-text ">Statut</span>
                        <select name="Statut" id="Statut" class="form-control">
                            <?php foreach ($Statut as $statut) : ?>
                                <option value="<?php echo $statut['LibStatut'] ?>"><?php echo $statut['LibStatut'] ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

        </form>



    </div>
    <div id="Contenue">
        <table class="table">
            <thead class=" table-dark">
                <tr>
                  <th>Action</th>
                    <th>Statut</th>
                    <th>Type document</th>
                    <th>N° Ordre Mission</th>
                    <th>Date_Demande</th>
                    <th>Motif_Deplacement</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Mode_Paiement</th>
                    <th>Agence service</th>
                    <th>Date_Debut</th>
                    <th>Date_Fin</th>
                    <th>Nombre_Jour</th>
                    <th>Client</th>
                    <th>N° OR</th>
                    <th>Lieu_Intervention</th>
                    <th>N° Vehicule</th>
                    <th>Total Autres Depenses</th>
                    <th>Total_General_Payer</th>
                    <th>Devis</th>
                </tr>
            </thead>
            <tbody id="BodyTable">
                <?php foreach ($ListDomRech as $ListDomRech) : ?>
                    <tr>
                        <?php
                        $color_ouvert = "#efd807";
                        $color_compta = "#77b5fe";
                        $color_payer = "#34c924";
                        $statut = strtolower(trim($ListDomRech['Statut']));
                        switch ($statut) {
                            case 'ouvert':
                                $color = $color_ouvert;
                                break;
                            case 'compta':
                                $color = $color_compta;
                                break;
                            default:
                                $color = $color_payer;
                        }

                        ?>
                        <td> <button type="button" class="btn btn-warning"><a href="/Hffintranet/index.php?action=DuplifierForm&NumDOM=<?php echo $ListDomRech['Numero_Ordre_Mission']?> & IdDOM=<?php echo $ListDomRech['ID_Demande_Ordre_Mission']?>" style="text-decoration: none;color: black;">Duplifier</a> </button></td>
                        <td style="background-color: <?php echo $color; ?>;"><?php echo $ListDomRech['Statut']; ?></td>
                        <td><?php echo $ListDomRech['Sous_type_document']; ?></td>
                        <td><?php echo $ListDomRech['Numero_Ordre_Mission']; ?></td>
                        <td><?php
                            $dateDemande = $ListDomRech['Date_Demande'];
                            $DDEM = date("d/m/Y", strtotime($dateDemande));
                            echo $DDEM;
                            ?>
                        </td>

                        <td><?php echo $ListDomRech['Motif_Deplacement']; ?></td>
                        <td><?php echo $ListDomRech['Matricule']; ?></td>
                        <td><?php echo $ListDomRech['Nom']; ?></td>
                        <td><?php echo $ListDomRech['Prenom']; ?></td>
                        <td><?php echo $ListDomRech['Mode_Paiement']; ?></td>
                        <td><?php echo $ListDomRech['LibelleCodeAgence_Service']; ?></td>
                        <td><?php
                            $dateDeb = $ListDomRech['Date_Debut'];
                            $DD = date("d/m/Y", strtotime($dateDeb));
                            echo $DD;
                            ?>
                        </td>
                        <td><?php $dateFin = $ListDomRech['Date_Fin'];
                            $DF = date("d/m/Y", strtotime($dateFin));
                            echo $DF;
                            ?></td>
                        <td><?php echo $ListDomRech['Nombre_Jour']; ?></td>
                        <td><?php echo $ListDomRech['Client']; ?></td>
                        <td><?php echo $ListDomRech['Fiche']; ?></td>
                        <td><?php echo $ListDomRech['Lieu_Intervention']; ?></td>
                        <td><?php echo $ListDomRech['NumVehicule']; ?></td>
                        <td><?php echo $ListDomRech['Total_Autres_Depenses']; ?></td>
                        <td><?php echo $ListDomRech['Total_General_Payer']; ?></td>
                        <td><?php echo $ListDomRech['Devis']; ?></td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        
        function StatutFilter() {
        var StatutLib = $('#Statut option:selected').text();
            var Matr = $('#Matricule').val();
        $.ajax({
            type: 'POST',
            url: '/Hffintranet/index.php?action=LibStatut',
            data: {
                LibStatut: StatutLib
            },
            dataType:'json',
            success: function (data) {
               // console.log(data);
                updateTableData(data);
            },
            error: function (error) {
                console.error(error);
            }
        });
    }

    function updateTableData(data) {
        // Supprimez les lignes existantes du tableau
       $('#BodyTable').empty();

        //Ajoutez les nouvelles lignes basées sur les données du serveur
        for (var i = 0; i < data.length; i++) {
            var color = getColorForStatut(data[i]['Statut']);
            
            var row = '<tr>';
            row += '<td><button type="button" class="btn btn-warning"><a style="text-decoration: none;color: black;" href="/Hffintranet/index.php?action=DuplifierForm&NumDOM=' + data[i]['Numero_Ordre_Mission'] + '&IdDOM=' + data[i]['ID_Demande_Ordre_Mission'] + '">Duplifier</a></button></td>';
            row += '<td style="background-color: ' + color + ';">' + data[i]['Statut'] + '</td>';
            row += '<td>' + data[i]['Sous_type_document'] + '</td>';
            row += '<td>' + data[i]['Numero_Ordre_Mission'] + '</td>';
            row += '<td>' + formatDate(data[i]['Date_Demande']) + '</td>';  // Nouvelle colonne de date
            row += '<td>' + data[i]['Motif_Deplacement'] + '</td>';
            row += '<td>' + data[i]['Matricule'] + '</td>';
            row += '<td>' + data[i]['Nom'] + '</td>';
            row += '<td>' + data[i]['Prenom'] + '</td>';
            row += '<td>' + data[i]['Mode_Paiement'] + '</td>';
            row += '<td>' + data[i]['LibelleCodeAgence_Service'] + '</td>';
            row += '<td>' + formatDate(data[i]['Date_Debut']) + '</td>';
            row += '<td>' + formatDate(data[i]['Date_Fin']) + '</td>';
            row += '<td>' + data[i]['Nombre_Jour'] + '</td>';
            row += '<td>' + data[i]['Client'] + '</td>';
            row += '<td>' + data[i]['Fiche'] + '</td>';
            row += '<td>' + data[i]['Lieu_Intervention'] + '</td>';
            row += '<td>' + data[i]['NumVehicule'] + '</td>';
            row += '<td>' + data[i]['Total_Autres_Depenses'] + '</td>';
            row += '<td>' + data[i]['Total_General_Payer'] + '</td>';
            row += '<td>' + data[i]['Devis'] + '</td>';
            row += '</tr>';

            $('#BodyTable').append(row);
        }
    }

    function getColorForStatut(statut) {
        var color_ouvert = "#efd807";
        var color_compta = "#77b5fe";
        var color_payer = "#34c924";
        var statutLowerCase = statut.toLowerCase().trim();

        switch (statutLowerCase) {
            case 'ouvert':
                return color_ouvert;
            case 'compta':
                return color_compta;
            default:
                return color_payer;
        }
    }

    function formatDate(dateString) {
        // Convertir la date formatée en objet Date
        var date = new Date(dateString);
        
        // Extraire le jour, le mois et l'année
        var day = date.getDate();
        var month = date.getMonth() + 1; // Les mois commencent à 0, donc ajoutez 1
        var year = date.getFullYear();
        
        // Ajouter un zéro devant le jour ou le mois si nécessaire (pour obtenir le format dd/mm/yyyy)
        day = (day < 10) ? '0' + day : day;
        month = (month < 10) ? '0' + month : month;
        
        // Retourner la date formatée
        return day + '/' + month + '/' + year;
    }
    //StatutFilter();
    $('#Statut').change(function() {
        StatutFilter();
        });
    })
</script>

</html>