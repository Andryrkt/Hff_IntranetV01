<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff Intranet</title>
</head>

<body>
    <div class="container">
        <div class="row" style="margin-top: 1%;">
            <div class="col">
                <h3 style="text-align: center;">Type de Document</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"> Document</label>
                        <div class="col-sm-10">
                            <!-- <input class="form-control" id="focusedInput" type="text" value="Click to focus...">-->
                            <input type="text" class="form-control" name="TypeDoc">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Sous Type</label>
                        <div class="col-sm-10">
                            <input class="form-control" id="Soutyp" type="text" >
                        </div>
                    </div>
                    <div class="btn-group" style="margin-top: 1%;">
                        <button name="btn_coms" type="submit" class="btn btn-success" value="ADD">Ajouter</button>
                        <a href="/Hff_IntranetV01/index.php?action=Acceuil " class="btn btn-danger"> Annuler</a>
                    </div>

                </form>
            </div>
            <div class="col">
                <table class=" table">
                    <thead class="table-dark">
                        <tr>
                            <th style="text-align: center;">Code Document </th>
                            <th style="text-align: center;">Sous Type</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</body>

</html>