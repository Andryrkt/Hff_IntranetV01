<?php
include($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/DOM/FormPJ.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Ordre</title>
</head>

<body onload="visible_espece();Interne_externe(); typeCatge(); "><!--/Hffintranet/Views/tcpdf/examples/Flight_brief_pdf.php-->
    <div class="container">
        <div class="d-flex  flex-row-reverse  col">
            <div class="tablinks p-2 btn btn-outline-warning ">
                <a href="/Hffintranet/index.php?action=New_DOM" style="text-decoration: none;color:black">Retour</a>
            </div>
        </div>

        <form action="/Hffintranet/index.php?action=EnvoyerImprime" method="POST" enctype="multipart/form-data" id="Formulaire">

            <div class="row">
                <!-- <div class="col">
                    <label for="NumDOM" class="label-form">N° DOM</label>
                    <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $NumDom ?>" readonly>
                </div>-->
                <div class="col-4 offset-6">
                    <label for="datesyst" class="label-form"> Date</label>
                    <input type="date" name="datesyst" id="datesyst" class="form-control" value="<?php echo $valeur = isset($dateDemande) ? $dateDemande : $datesyst; ?>" readonly>
                </div>
            </div>

            <div class="row">

                <div class="col">


                    <!-- DEBUT Debiteur selecte -->
                    <label for="" class="col-4  fw-bold">Agence service débiteur</label>

                    <div class="row">
                        <div class="col-6">
                            <label for="Serv" class="label-form">Agence :</label>
                            <select class="form-select " aria-label="Default select example" id="select1" name="codeService">

                            </select>
                        </div>

                        <div class="col-6 ">
                            <label for="LibServ" class="label-form">Service :</label>
                            <select class="form-select" name="service" aria-label="Default select example" id="serviceIrium">
                            </select>
                        </div>
                    </div>
                </div>
                <!-- FIN Debiteur selecte -->

                <!--DEBUT emetteur select -->
                <div class="col">
                    <label for="" class="col-4  fw-bold">Agence service émetteur</label>
                    <!-- extern (temporaire) -->
                    <div class="row" id="ext">
                        <div class="col-4 ">
                            <label for="Serv" class="label-form">Agence :</label>
                            <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $valeur = isset($agentEmetteur) ? $agentEmetteur : $code_service ?>" readonly>
                        </div>
                        <div class="col-4 ">
                            <label for="LibServ" class="label-form">Service :</label>
                            <input type="text" name="LibServ" class="form-control" id="LibServ" value="<?php echo $valeur = isset($serviceEmetteur) ? $serviceEmetteur : $service ?>" readonly>
                        </div>

                    </div>
                    <!-- interne (permanent) -->
                    <div class="row" id="int">

                        <div class="col-4 ">
                            <label for="Serv" class="label-form">Agence :</label>
                            <input type="text" name="ServINt" class="form-control" id="ServINt" value="<?php echo $valeur = isset($agentEmetteur) ? $agentEmetteur : $codeServ ?>" readonly>
                        </div>
                        <div class="col-4 ">
                            <label for="LibServ" class="label-form">Service :</label>
                            <input type="text" name="LibServINT" class="form-control" id="LibServINT" value="<?php echo $valeur = isset($serviceEmetteur) ? $serviceEmetteur : $servLib ?>" readonly>
                        </div>

                    </div>

                </div>
                <!-- FIN émetteur select -->


            </div>




            <div class="row">
                <div class="col-6">
                    <label for="typeMission" class="label-form"> Type de Mission</label>
                    <input name="typeMission" id="typeMission" class="form-control" value="<?php echo $valeur = isset($data[0]['Sous_type_document']) ? $data[0]['Sous_type_document']  : $typeMission ?>" readonly />
                </div>


                <!--<div class="col">
                    <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                    <input type="text" name="AutreType" class="form-control" id="AutreType" value="<?php echo $autrtype ?>">
                </div>-->
            </div>
            <div class="row">
                <div class="col" id="categ">
                    <label for="catego" class="label-form"> Catégorie:</label>
                    <input type="text" name="catego" id="catego" class="form-control" value="<?php echo $valeur = isset($data[0]['Categorie']) ? $data[0]['Categorie'] : $CategPers ?>">

                </div>
                <!---->
                <div class="col" id="MUTARENTAL"></div>
                <div class="col" id="SITE"></div>
                <!---->
            </div>

            <input type="hidden" name="radiochek" id="radiochek" value="<?php echo $valeur = isset($statutSalarier) ? $statutSalarier : $check; ?>">

            <div class="row" id="Interne">
                <div class="col-6">
                    <label for="matricule" class="label-form"> Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="form-control" value="<?php echo $valeur = isset($data[0]['Matricule']) ? $data[0]['Matricule'] : $Maricule ?>" readonly>
                </div>

                <div class="col-6">
                    <label for="Nomprenoms" class="label-form"> Nom </label>
                    <input name="nomprenom" id="nomprenom" class="form-control" value="<?php echo $valeur = isset($data[0]['Nom']) ? $data[0]['Nom'] : $nom ?>" readonly />
                </div>
                <div class="col-6">
                    <label for="prenoms" class="label-form"> Prénoms </label>
                    <input name="prenom" id="prenom" class="form-control" value="<?php echo $valeur = isset($data[0]['Prenom']) ? $data[0]['Prenom'] : $prenom ?>" readonly />
                </div>


            </div>
            <div class="row" id="externe">
                <div class="col">
                    <label for="namesExt" class="label-form"> Nom</label>
                    <input type="text" name="namesExt" id="namesExt" class="form-control" value="<?php echo $valeur = isset($data[0]['Nom']) ? $data[0]['Nom'] : $nomExt ?>" readonly>
                </div>
                <div class="col">
                    <label for="firstnamesExt" class="label-form"> Prénoms</label>
                    <input type="text" name="firstnamesExt" id="firstnamesExt" class="form-control" value="<?php echo $valeur = isset($data[0]['Prenom']) ? $data[0]['Prenom'] : $prenomExt ?>" readonly>
                </div>
                <div class="col">
                    <label for="cin" class="label-form"> CIN</label>
                    <input type="text" name="cin" id="cin" class="form-control" value="<?php echo $valeur = isset($cin) ? $cin : $CINext ?>" readonly>
                </div>
            </div>


            <div class="row">
                <div class="col">
                    <label for="dateDebut" class="label-form"> Date début</label>
                    <input type="date" name="dateDebut" id="dateDebut" class="form-control" required style="border-color: orange;" value="<?php echo $valeur = isset($data[0]['Date_Debut']) ? $data[0]['Date_Debut'] : ''  ?>">
                </div>
                <div class="col">
                    <label for="heureDebut" class="label-form"> Heure début</label>
                    <input type="time" name="heureDebut" id="heureDebut" class="form-control" required value="<?php echo $valeur = isset($data[0]['Heure_Debut']) ? $data[0]['Heure_Debut'] : '08:00' ?>" style="border-color: orange;">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="dateFin" class="label-form"> Date Fin</label>
                    <input type="date" name="dateFin" id="dateFin" class="form-control" onblur="recupeVal();Difference_date();sommeEtIndemnite('idemForfait','Nbjour','idemForfait01','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense') " required style="border-color: orange;" value="<?php echo $valeur = isset($data[0]['Date_Fin']) ? $data[0]['Date_Fin'] : ''  ?>">
                </div>
                <div class="col">
                    <label for="heureFin" class="label-form"> Heure Fin</label>
                    <input type="time" name="heureFin" id="heureFin" class="form-control" required value=" <?php echo $valeur = isset($data[0]['Heure_Fin']) ? $data[0]['Heure_Fin'] : '18:00' ?>" style="border-color: orange;">

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="periode" class="label-form" id="nomprenom"> Nombre de Jour</label>
                    <input type="text" name="Nbjour" id="Nbjour" class="form-control" required style="text-align: right;" value=" <?php echo $valeur = isset($data[0]['Nombre_Jour']) ? $data[0]['Nombre_Jour'] : '' ?>" readonly>
                </div>

                <div class="col">
                    <label for="motif" class="label-form"> Motif</label>
                    <input type="text" name="motif" id="motif" class="form-control" style="border-color: orange;" maxlength="100" value=" <?php echo $valeur = isset($data[0]['Motif_Deplacement']) ? $data[0]['Motif_Deplacement'] : '' ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="client" class="label-form"> Client</label>
                    <input type="text" name="client" id="client" class="form-control" style="border-color: orange;" maxlength="100" value=" <?php echo $valeur = isset($data[0]['Client']) ? $data[0]['Client'] : '' ?>">
                </div>
                <div class="col">
                    <label for="fiche" class="label-form"> N°fiche</label>
                    <input type="text" name="fiche" id="fiche" class="form-control" maxlength="50" value=" <?php echo $valeur = isset($data[0]['Fiche']) ? $data[0]['Fiche'] : '' ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="lieuInterv" class="label-form"> Lieu D'intervention</label>
                    <input type="text" name="lieuInterv" id="lieuInterv" class="form-control" style="border-color: orange;" maxlength="100" value=" <?php echo $valeur = isset($data[0]['Lieu_Intervention']) ? $data[0]['Lieu_Intervention'] : '' ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="vehicule" class="label-form"> Véhicule Société</label>
                    <?php if ($data[0]['Vehicule_Societe'] === "OUI") { ?>
                        <select name="vehicule" id="vehicule" class="form-select" style="border-color: orange;">
                            <option value="OUI" selected>OUI</option>
                            <option value="NON">NON</option>
                        </select>
                    <?php } elseif ($data[0]['Vehicule_Societe'] === "NON") { ?>
                        <select name="vehicule" id="vehicule" class="form-select" style="border-color: orange;">
                            <option value="OUI">OUI</option>
                            <option value="NON">NON</option>
                        </select>
                    <?php } else { ?>
                        <select name="vehicule" id="vehicule" class="form-select" style="border-color: orange;">
                            <option value="OUI">OUI</option>
                            <option value="NON">NON</option>
                        </select>
                    <?php } ?>
                </div>
                <div class="col">
                    <label for="N_vehicule" class="label-form"> N°</label>
                    <input type="text" name="N_vehicule" id="N_vehicule" class="form-control" maxlength="50" value=" <?php echo $valeur = isset($data[0]['NumVehicule']) ? $data[0]['NumVehicule'] : '' ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="IdemDeplac" class="label-form">indemnité de déplacement</label>
                    <input type="text" name="IdemDeplac" id="IdemDeplac" class="form-control" style="border-color: orange;" oninput="formatEtMettreAJour('IdemDeplac')" onblur="sommeEtIndemniteDeplac('IdemDeplac','Nbjour','TotalIdemDeplac');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense')" value=" <?php echo $valeur = isset($data[0]['idemnity_depl']) ? $data[0]['idemnity_depl'] : '' ?>" />
                </div>
                <div class="col">
                    <label for="TotalIdemDeplac" class="label-form"> Total indemnité de déplacement</label>
                    <input type="text" name="TotalIdemDeplac" id="TotalIdemDeplac" class="form-control" style="border-color: orange;" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <label for="Devis" class="label-form">Devise:</label>
                    <?php if ($data[0]['Devis'] === "MGA") { ?>
                        <select name="Devis" id="Devis" class="form-select">
                            <option value="MGA" selected>MGA</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    <?php } elseif ($data[0]['Devis'] === "EUR") { ?>
                        <select name="Devis" id="Devis" class="form-select">
                            <option value="MGA">MGA</option>
                            <option value="EUR" selected>EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    <?php } elseif ($data[0]['Devis'] === "USD") { ?>
                        <select name="Devis" id="Devis" class="form-select">
                            <option value="MGA">MGA</option>
                            <option value="EUR">EUR</option>
                            <option value="USD" semected>USD</option>
                        </select>
                    <?php } else { ?>
                        <select name="Devis" id="Devis" class="form-select">
                            <option value="MGA">MGA</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    <?php } ?>
                </div>
                <div class="col">
                    <label for="idemForfait" class="label-form"> Indemnité Forfaitaire Journalière(s)</label>
                    <input type="text" name="idemForfait" id="idemForfait" class="form-control" oninput="formatEtMettreAJour('idemForfait', 'TotalidemForfait');" onblur="sommeEtIndemnite('idemForfait','Nbjour','idemForfait01','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense') " style="border-color: orange;" readonly />
                </div>
                <div class="col">
                    <label for="idemForfait01" class="label-form"> supplément journalier</label>
                    <input type="text" name="idemForfait01" id="idemForfait01" class="form-control" oninput="formatEtMettreAJour('idemForfait01');" onblur="sommeEtIndemnite('idemForfait','Nbjour','idemForfait01','TotalidemForfait');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense') " style="border-color: orange;" value=" <?php echo $valeur = isset($data[0]['Doit_indemnite']) ? $data[0]['Doit_indemnite'] : '' ?>" />
                </div>

                <div class="col">
                    <label for="TotalidemForfait" class="label-form"> Total d'Indemnité Forfaitaire</label>
                    <input type="text" name="TotalidemForfait" id="TotalidemForfait" class="form-control" readonly onblur='Somme();' />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep" class="label-form"> Motif Autre dépense 1</label>
                    <input type="text" name="MotifAutredep" id="MotifAutredep" class="form-control" style="border-color: orange;" maxlength="50" value=" <?php echo $valeur = isset($data[0]['Motif_Autres_depense_1']) ? $data[0]['Motif_Autres_depense_1'] : '' ?>" />
                </div>
                <div class="col">
                    <label for="Autredep1" class="label-form"> Montant </label>
                    <input type="text" name="Autredep1" id="Autredep1" class="form-control" oninput="formatEtMettreAJour('Autredep1');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense')" value=" <?php echo $valeur = isset($data[0]['Autres_depense_1']) ? $data[0]['Autres_depense_1'] : '0' ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep2" class="label-form"> Motif Autre dépense 2</label>
                    <input type="text" name="MotifAutredep2" id="MotifAutredep2" class="form-control" style="border-color: orange;" maxlength="50" value=" <?php echo $valeur = isset($data[0]['Motif_Autres_depense_2']) ? $data[0]['Motif_Autres_depense_2'] : '' ?>" />
                </div>
                <div class="col">
                    <label for="Autredep2" class="label-form"> Montant </label>
                    <input type="text" name="Autredep2" id="Autredep2" class="form-control" oninput="formatEtMettreAJour('Autredep2');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense')" value=" <?php echo $valeur = isset($data[0]['Autres_depense_2']) ? $data[0]['Autres_depense_2'] : '0' ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="MotifAutredep3" class="label-form"> Motif Autre dépense 3</label>
                    <input type="text" name="MotifAutredep3" id="MotifAutredep3" class="form-control" style="border-color: orange;" maxlength="50" value=" <?php echo $valeur = isset($data[0]['Motif_Autres_depense_3']) ? $data[0]['Motif_Autres_depense_3'] : '' ?>" />
                </div>
                <div class="col">
                    <label for="Autredep3" class="label-form"> Montant </label>
                    <input type="text" name="Autredep3" id="Autredep3" class="form-control" oninput="formatEtMettreAJour('Autredep3');" style="border-color: orange;" onblur="calculerSomme('Autredep1','Autredep2','Autredep3','TotalAutredep');calculerSommeAll('TotalidemForfait', 'TotalAutredep','TotalIdemDeplac', 'Alldepense');negative('Alldepense')" value=" <?php echo $valeur = isset($data[0]['Autres_depense_3']) ? $data[0]['Autres_depense_3'] : '0' ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="TotalAutredep" class="label-form"> Total Montant Autre Dépense</label>
                    <input type="text" name="TotalAutredep" id="TotalAutredep" class="form-control" oninput="formatEtMettreAJour('TotalAutredep');" readonly>
                </div>
                <div class="col">
                    <label for="Alldepense" class="label-form"> Montant Total</label>
                    <input type="text" name="Alldepense" id="Alldepense" class="form-control" oninput="formatEtMettreAJour('Alldepense');" readonly>
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
                    <?php if ($modePaiement === "MOBILE MONEY") { ?>
                        <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme(); Interne_externe()" style="border-color: orange;">
                            <option value="MOBILE MONEY" selected>MOBILE MONEY</option>
                            <option value="ESPECES">ESPECES</option>
                            <option value="VIREMENT BANCAIRE">VIREMENT BANCAIRE</option>
                        </select>
                    <?php } elseif ($modePaiement === "ESPECES") { ?>
                        <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme(); Interne_externe()" style="border-color: orange;">
                            <option value="MOBILE MONEY">MOBILE MONEY</option>
                            <option value="ESPECES" selected>ESPECES</option>
                            <option value="VIREMENT BANCAIRE">VIREMENT BANCAIRE</option>
                        </select>
                    <?php } elseif ($modePaiement === "VIREMENT BANCAIRE") { ?>
                        <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme(); Interne_externe()" style="border-color: orange;">
                            <option value="MOBILE MONEY">MOBILE MONEY</option>
                            <option value="ESPECES">ESPECES</option>
                            <option value="VIREMENT BANCAIRE" selected>VIREMENT BANCAIRE</option>
                        </select>
                    <?php } else { ?>
                        <select name="modepaie" id="modepaie" class="form-select" onchange="visible_espece()" onfocus="Somme(); Interne_externe()" style="border-color: orange;">
                            <option value="MOBILE MONEY">MOBILE MONEY</option>
                            <option value="ESPECES">ESPECES</option>
                            <option value="VIREMENT BANCAIRE">VIREMENT BANCAIRE</option>
                        </select>
                    <?php } ?>
                </div>
                <div class="col" id="OpInter">
                    <label for="modeesp" class="label-form" id="labelMode"> Mode</label>
                    <input type="text" name="valModesp" id="modeespece" class="form-control">

                    <input type="text" name="valModemob" id="modeMob" class="form-control" value="<?php echo $valeur = isset($modePaiementNumero) ? $modePaiementNumero : $numTel ?>" style="border-color: orange;" maxlength="10" minlength="10" required>
                    <input type="text" name="valModecompt" id="modecompte" class="form-control" value="<?php echo $valeur = isset($modePaiementNumero) ? $modePaiementNumero : $numCompteBancaire ?>">

                </div>
                <div class="col" id="OpExter">
                    <label for="modeesp" class="label-form" id="labelMode01"> Mode</label>
                    <input type="text" name="valModespExt" id="valModespExt" class="form-control" value=" <?php echo $valeur = isset($modePaiementNumero) ? $modePaiementNumero : '' ?>">
                </div>
            </div>

            <div class="row" id="label_PJ">
                <div class="col">
                    <h4 style="text-align: center;">Pièce Jointe</h4>
                </div>
            </div>
            <div class="row" id="PJ">
                <div class="col">
                    <label for="file01" class="label-form"> Fichier joint 01 (Merci de mettre un fichier PDF):</label>
                    <?php
                    inputFields("", "file01", "file01", "", "file");
                    ?>
                </div>
                <div class="col">
                    <label for="file02" class="label-form"> Fichier joint 02 (Merci de mettre un fichier PDF):</label>
                    <?php
                    inputFields("", "file02", "file02", "", "file");
                    ?>
                </div>

            </div>
            <div class="row">
                <div class="mt-2 ">
                    <a onclick="return confirm('Voulez-vous envoyer la demande ?')"> <button type="submit" name="Envoyer" class="btn btn-info md-5" data-bs-toggle="tooltip"> <i class="fa fa-print"> Envoyer</i></button> </a>
                </div>
            </div>

        </form>
    </div>

    <script src="/Hffintranet/Views/js/FormCompleDom.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="/Hffintranet/Views/js/FormCompleDomAjax.js"></script>
</body>

</html>