<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff intranet</title>

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

    function Matricule() {
        var names = document.getElementById('nomprenom').value;
        let result = names.substring(names.length - 4);
        document.getElementById('matricule').value = result;
    }

 
</script>

<body onload="visible(); Matricule()">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="col" style="text-align: right;font-weight: bold;">
                    <h4>Demande d'Order de Mission</h4>
                </div>
                <form method="POST" action="/Hff_IntranetV01/index.php?action=checkMatricule">
                    <div class="row">
                        <div class="col">
                            <label for="NumDOM" class="label-form">N° DOM</label>
                            <input type="text" class="form-control" name="NumDOM" id="NumDOM" value="<?php echo $NumDOM ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?php foreach ($CodeServiceofCours as $CodeServiceofCours) : ?>
                                <label for="Serv" class="label-form">Code :</label>
                                <input type="text" name="Serv" class="form-control" id="Serv" value="<?php echo $CodeServiceofCours['Code_AgenceService_Sage'] ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="LibServ" class="label-form">Service :</label>
                            <input type="text" name="LibServ" class="form-control" id="LibServ" value="<?php echo $CodeServiceofCours['Libelle_AgenceService_Sage'] ?>" readonly>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="typeMission" class="label-form"> Type de Mission</label>
                            <select name="typeMission" id="typeMission" class="form-select" onchange="visible()"><!--à discuter voir dans le fiche -->
                                <option value="MISSION">MISSION</option>MISSION
                                <option value="COMPLEMENT">COMPLEMENT</option>
                                <option value="FORMATION">FORMATION</option>
                                <option value="MUTATION">MUTATION</option>
                                <option value="AUTRES A PRECISER">AUTRES A PRECISER</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="AutreType" class="label-form" id="labAutre"> Autre</label>
                            <input type="text" name="AutreType" class="form-control" id="AutreType">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="Nomprenoms" class="label-form"> Nom - Matricule</label>
                            <select name="nomprenom" id="nomprenom" class="form-control" onchange="Matricule()" onblur="envoyerDonnees()">
                                <?php foreach ($PersonelServOfCours as $PersonelServOfCours) : ?>
                                    <option value="<?php echo $PersonelServOfCours['Noms_Prenoms'] . " - " . $PersonelServOfCours['Matricule'] ?>"> <?php echo $PersonelServOfCours['Noms_Prenoms'] . " - " . $PersonelServOfCours['Matricule'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="matricule" class="label-form"> Matricule</label>
                            <input type="text" name="matricule" id="matricule" class="form-control" readonly> 
                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-2">
                            <button type="submit" class="btn btn-info"> Suivant</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>

</html>