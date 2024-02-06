<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff Intranet</title>
</head>

<body>
    <div class=" container">
        <div class="card">
            <div class="card-body">

                <div class="col" style="text-align: right;font-weight: bold;">
                    <h4>Personnel</h4>
                </div>
                <div class="d-flex flex-row ">
                    <div class="p-2">
                        <a href="/Hffintranet/index.php?action=PersonnelList" class="btn btn-warning">
                            Liste des personnels
                        </a>

                    </div>
                </div>
                <form action="" method="POST"> 
                    <div class=" row">
                        <div class="col">
                            <label for="AgenceSage" class="label-form">Agence - Service</label>
                            <select name="Agence_servSage" id="AgenceSage" class="form-select" required>
                                <option value=""> Agence - Service dans SAGE</option>
                            </select>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="Matricule" class="label-form">Matricule:</label>
                            <input type="text" class="form-control" id="Matricule" name="Matricule" required>
                        </div>
                        <div class="col">
                            <label for="nomPrenoms" class="label-form">Nom - Prénoms:</label>
                            <input type="text" class="form-control" id="nomPrenoms" name="nomPrenoms" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="NUm_frs" class="label-form">Numero Fournisseur</label>
                            <input type="text" name="numfrs" id="NUm_frs" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="AgenceIrium" class="label-form">Agence -service</label>
                            <select name="Agence_servIrum" id="AgenceIrium" class="form-select" required>
                                <option value=""> Agence - Service dans IRIUM</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="tel" class="label-form">N° Tel:</label>
                            <input type="text" id="tel" name="tel" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="banque" class="label-form">N° Compte Bancaire:</label>
                            <input type="text" id="banque" name="compte_bancaire" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-2">
                            <input type="submit" class="btn btn-success md-2" value="Enregistrer">

                        </div>
                    </div>
                </form>

            </div>
        </div>
</body>

</html>