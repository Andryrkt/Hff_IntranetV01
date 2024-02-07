<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Ordre</title>
</head>
<script>
    function visible() {
        var select = document.getElementById('typeMission');
        var labelINput = document.getElementById('labAutre');
        var input = document.getElementById('AutreType');
        if (select.value == "AUTRES A PRECISER") {
            labelINput.style.display = 'block';
            input.style.display = 'block';

        } else {
            labelINput.style.display = 'none';
            input.style.display = 'none';
        }
    }

    function Mat() {
        let Matr = document.getElementById('matricule01').value;
        var pj = document.getElementById('PJ');
        var libPJ = document.getElementById('label_PJ')
       if(Matr.length >4){
        libPJ.style.display = 'block';
        pj.style.display = 'block';
       }else{
        libPJ.style.display = 'none';
        pj.style.display = 'none';
       }
    }
</script>

<body onload="visible();Mat()"><!--/Hffintranet/Views/tcpdf/examples/Flight_brief_pdf.php-->
    <?php foreach ($detailDom as $detailDom) : ?>
        <div class="container">
            <form action="#" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col">
                        <label for="NumDOM" class="label-form">N° DOM</label>
                        <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $detailDom['Numero_Ordre_Mission'] ?>" readonly>
                    </div>
                    <div class="col">
                        <label for="datesyst" class="label-form"> Date</label>
                        <input type="date" name="datesyst" id="modeesp" class="form-control" value="<?php echo $detailDom['Date_Demande'] ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <label for="Serv" class="label-form">Agence - Service:</label>
                        <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $detailDom['LibelleCodeAgence_Service'] ?>" readonly>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="typeMission" class="label-form"> Type de Mission</label>
                            <input name="typeMission" id="typeMission" class="form-control" value="<?php echo $detailDom['Sous_Type_Document'] ?>" readonly />
                        </div>
                        <div class="col">
                            <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                            <input type="text" name="AutreType" class="form-control" id="AutreType" value="<?php echo $detailDom['Autre_Type_Document'] ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="matricule" class="label-form"> Matricule</label>
                            <input type="text" name="matricule" id="matricule01" class="form-control" value="<?php echo $detailDom['Matricule'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="Nomprenoms" class="label-form"> Nom </label>
                            <input name="nomprenom" id="nomprenom" class="form-control" value="<?php echo $detailDom['Nom'] ?>" readonly/>
                        </div>
                        <div class="col">
                            <label for="prenoms" class="label-form"> Prénoms </label>
                            <input name="prenom" id="prenom" class="form-control" value="<?php echo $detailDom['Prenom'] ?>" readonly/>
                        </div>
                    </div>



                    <div class="row">
                        <div class="col">
                            <label for="dateDebut" class="label-form"> Date début</label>
                            <input type="date" name="dateDebut" id="dateDebut" class="form-control" value="<?php echo $detailDom['Date_Debut'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="heureDebut" class="label-form"> Heure début</label>
                            <input type="time" name="heureDebut" id="heureDebut" class="form-control" value="<?php echo $detailDom['Heure_Debut'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="dateFin" class="label-form"> Date Fin</label>
                            <input type="date" name="dateFin" id="dateFin" class="form-control" value="<?php echo $detailDom['Date_Fin'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="heureFin" class="label-form"> Heure Fin</label>
                            <input type="time" name="heureFin" id="heureFin" class="form-control" value="<?php echo $detailDom['Heure_Fin'] ?>" readonly> 

                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="periode" class="label-form" id="nomprenom"> Période</label>
                            <input type="text" name="Nbjour" id="Nbjour" class="form-control" value="<?php echo $detailDom['Nombre_Jour'] ?>" style="text-align: right;" readonly>
                        </div>

                        <div class="col">
                            <label for="motif" class="label-form"> Motif</label>
                            <input type="text" name="motif" id="motif" class="form-control" value="<?php echo $detailDom['Motif_Deplacement'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="client" class="label-form"> Client</label>
                            <input type="text" name="client" id="client" class="form-control" value="<?php echo $detailDom['Client'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="fiche" class="label-form"> N°fiche</label>
                            <input type="text" name="fiche" id="fiche" class="form-control" value="<?php echo $detailDom['Fiche'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="lieuInterv" class="label-form"> Lieu D'intervention</label>
                            <input type="text" name="lieuInterv" id="lieuInterv" class="form-control" value="<?php echo $detailDom['Lieu_Intervention'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="vehicule" class="label-form"> Véhicule Société</label>
                            <input type="text" name="vehicule" id="vehicule" class="form-control" value="<?php echo $detailDom['Vehicule_Societe'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="N_vehicule" class="label-form"> N°</label>
                            <input type="text" name="N_vehicule" id="N_vehicule" class="form-control" value="<?php echo $detailDom['NumVehicule'] ?>" readonly />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="Devis" class="label-form">Devise:</label>
                            <input type="text" name="Devis" id="Devis" class="form-control" value="<?php echo $detailDom['Devis'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="idemForfait" class="label-form"> Indemnité Forfaitaire</label>
                            <input type="text" name="idemForfait" id="idemForfait" class="form-control" value="<?php echo $detailDom['Indemnite_Forfaitaire'] ?>" readonly/>
                        </div>
                        <div class="col">
                            <label for="TotalidemForfait" class="label-form"> Total d'Indemnité Forfaitaire</label>
                            <input type="text" name="TotalidemForfait" id="TotalidemForfait" class="form-control" value="<?php echo $detailDom['Total_Indemnite_Forfaitaire'] ?>" readonly/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="MotifAutredep" class="label-form"> Motif Autre dépense 1</label>
                            <input type="text" name="MotifAutredep" id="MotifAutredep" class="form-control" value="<?php echo $detailDom['Motif_Autres_depense_1'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="Autredep1" class="label-form"> Montant </label>
                            <input type="text" name="Autredep1" id="Autredep1" class="form-control" value="<?php echo $detailDom['Autres_depense_1'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="MotifAutredep2" class="label-form"> Motif Autre dépense 2</label>
                            <input type="text" name="MotifAutredep2" id="MotifAutredep2" class="form-control" value="<?php echo $detailDom['Motif_Autres_depense_2'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="Autredep2" class="label-form"> Montant </label>
                            <input type="text" name="Autredep2" id="Autredep2" class="form-control" value="<?php echo $detailDom['Autres_depense_2'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="MotifAutredep3" class="label-form"> Motif Autre dépense 3</label>
                            <input type="text" name="MotifAutredep3" id="MotifAutredep3" class="form-control" value="<?php echo $detailDom['Motif_Autres_depense_3'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="Autredep3" class="label-form"> Montant </label>
                            <input type="text" name="Autredep3" id="Autredep3" class="form-control" value="<?php echo $detailDom['Autres_depense_3'] ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="TotalAutredep" class="label-form"> Total Montant Autre Dépense</label>
                            <input type="text" name="TotalAutredep" id="TotalAutredep" class="form-control" value="<?php echo $detailDom['Total_Autres_Depenses'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="Alldepense" class="label-form"> Montant Total</label>
                            <input type="text" name="Alldepense" id="Alldepense" class="form-control" value="<?php echo $detailDom['Total_General_Payer'] ?>" readonly>
                        </div>
                    </div>



                    <div class="row">
                        <div class="col">
                            <h4 style="text-align: center;">Mode de paiement</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="modepaie" class="label-form"> Mode paiement</label>
                            <input type="text" name="modepaie" id="modepaie" class="form-control" value="<?php echo $detailDom['Mode_Paiement'] ?>" readonly>
                        </div>
                    </div>

                    <div class="row" id="label_PJ">
                        <div class="col">
                            <h4 style="text-align: center;">Pièce Jointe</h4>
                        </div>
                    </div>
                    <div class="row" id="PJ">
                        <div class="col">
                            <label for="file01" class="label-form"> Fichier joint 01 </label>
                            <a href="/Hffintranet/Views/DOM/SeePdf.php?Pdf=<?php echo $detailDom['Piece_Jointe_1'] ?>"> <input type="text" name="file01" id="file01" class="form-control" value="<?php echo $detailDom['Piece_Jointe_1'] ?>" readonly></a>
                        </div>
                        <div class="col">
                            <label for="file02" class="label-form"> Fichier joint 02 </label>
                            <input type="text" name="file02" id="file02" class="form-control" value="<?php echo $detailDom['Piece_Jointe_2'] ?>" readonly>
                        </div>

                    </div>


            </form>
        </div>
    <?php endforeach; ?>
</body>

</html>