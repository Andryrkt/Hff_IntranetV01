<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hff INtranet</title>


    <style>
        .Contenue {
            width: 100%;
            max-height: 800px;
            overflow-y: auto;
        }

        th {
            position: sticky;
            top: 0;
            background-color: #f2f2f2;
        }

        .statut-paye {
            background-color: #34c924;
        }
    </style>
</head>

<body>
    <div class="row">


        <div class="row">
            <div class="col-2">
                <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                    <span class="input-group-text ">Statut</span>
                    <select name="Statut" id="statut" class="form-control">
                    </select>
                </div>
            </div>
            <div class="col-2">
                <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                    <span class="input-group-text">Matricule</span>
                    <input type="search" name="Matricule" id="matricule" class="form-control">
                </div>
            </div>
            <div class="col-3">
                <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                    <span class="input-group-text">Date de création</span>
                    <input type="date" name="Date_debut" id="dateCreationDebut" class="form-control">
                    <input type="date" name="Date_Fin" id="dateCreationFin" class="form-control">
                    <small id="dateCreationMessage"></small>
                </div>
            </div>
            <div class="col-3">
                <div class="input-group " style="margin-left: 2%; margin-bottom: 2%;">
                    <span class="input-group-text">Date de début</span>
                    <input type="date" name="Date_debut_D" id="dateDebutDebut" class="form-control">
                    <input type="date" name="Date_Fin_D" id="dateDebutFin" class="form-control">
                    <small id="dateCreationMessage"></small>
                </div>
            </div>
            <div class="col-2">
                <input type="submit" name="exportExcel" id="export" class="btn btn-success" value="Export Excel">
            </div>
        </div>



        <div class="Contenue " id="table-container">

        </div>

    </div>
    <!-- <div id="Contenue">
        <table class="table">
            <thead class=" table-dark">
                <tr>
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
            <tbody>
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
    </div> -->


    <script script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        /**
         * @Andryrkt
         * récupère les donnée JSON et faire le traitement du recherhce, affichage, export excel
         */
        fetch("/Hffintranet/index.php?action=recherche")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(raw_data => {

                console.log(raw_data);
                // afficher les donnée du selecte statut
                SelectStatutValue1(raw_data);
                // Appeler la fonction de rendu des données avec les données récupérées
                renderData1(raw_data);

                //filtre les donées et l'afficher
                ////////////////////////////////////////////////////

                const statutInput = document.querySelector('#statut');
                const matriculeInput = document.querySelector('#matricule');
                const dateCreationDebutInput = document.querySelector('#dateCreationDebut');
                const dateCreationFinInput = document.querySelector('#dateCreationFin');
                const dateDebutDebutInput = document.querySelector('#dateDebutDebut');
                const dateDebutFinInput = document.querySelector('#dateDebutFin');

                let dateCreationDebut = dateCreationDebutInput.value;
                let dateCreationFin = dateCreationFinInput.value;
                let dateDebutDebut = dateDebutDebutInput.value;
                let dateDebutFin = dateDebutFinInput.value;

                let timeoutId; // variable pour stocker l'identifiant du délai

                // Écouteur d'événement pour le changement de statut
                statutInput.addEventListener('change', (e) => {
                    e.preventDefault();
                    executeFiltreEtRendu();
                });

                // Écouteur d'événement pour la saisie dans le champ de matricule
                matriculeInput.addEventListener('input', (e) => {
                    e.preventDefault();
                    clearTimeout(timeoutId); // Effacer le délai précédent

                    // Définir un délai avant d'exécuter le filtrage et le rendu des données
                    timeoutId = setTimeout(executeFiltreEtRendu, 500);
                });

                dateCreationDebutInput.addEventListener('change', (e) => {
                    e.preventDefault();
                    // Vérifier si la date de début est supérieure à la date de fin
                    if (new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value)) {
                        // Afficher un message d'erreur
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = "La date de début doit être inférieure à la date de fin.";
                        // Effacer les résultats précédents
                        // const container = document.getElementById('table-container');
                        // container.innerHTML = '';
                    } else {
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = '';
                        executeFiltreEtRendu();
                    }
                });


                dateCreationFinInput.addEventListener('change', (e) => {
                    e.preventDefault();
                    // Vérifier si la date de début est supérieure à la date de fin
                    if (new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value)) {
                        // Afficher un message d'erreur
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = "La date de début doit être inférieure à la date de fin.";
                        // Effacer les résultats précédents
                        // const container = document.getElementById('table-container');
                        // container.innerHTML = '';
                    } else {
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = '';
                        executeFiltreEtRendu();
                    }

                });

                dateDebutDebutInput.addEventListener('change', (e) => {
                    e.preventDefault();
                    if (new Date(dateDebutDebutInput.value) > new Date(dateDebutFinInput.value)) {
                        // Afficher un message d'erreur
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = "La date de début doit être inférieure à la date de fin.";
                        // Effacer les résultats précédents
                        // const container = document.getElementById('table-container');
                        // container.innerHTML = '';
                    } else {
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = '';
                        executeFiltreEtRendu();
                    }
                });
                dateDebutFinInput.addEventListener('change', (e) => {
                    e.preventDefault();
                    if (new Date(dateDebutDebutInput.value) > new Date(dateDebutFinInput.value)) {
                        // Afficher un message d'erreur
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = "La date de début doit être inférieure à la date de fin.";
                        // Effacer les résultats précédents
                        // const container = document.getElementById('table-container');
                        // container.innerHTML = '';
                    } else {
                        const message = document.getElementById('dateCreationMessage');
                        message.textContent = '';
                        executeFiltreEtRendu();
                    }
                });

                function executeFiltreEtRendu() {
                    let donner = filtre(raw_data); // Filtre les données
                    console.log(donner);
                    if (donner.length > 0) {
                        renderData1(donner); // Rend les données filtrées
                    } else {
                        console.log(new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value))


                        // Afficher un message si le tableau est vide
                        const container = document.getElementById('table-container');
                        container.innerHTML = "Il n'y a pas de donnée qui correspond à votre recherche.";
                    }
                }

                ////////////////////////////////////////////////////

                //export excel
                // Sélection du bouton d'export Excel
                const exportExcelButton = document.querySelector('#export');

                // Ajout d'un écouteur d'événements pour le clic sur le bouton
                exportExcelButton.addEventListener('click', () => {
                    ExportExcel(raw_data);
                });

            })
            .catch(error => {
                console.error('There has been a problem with your fetch operation:', error);
            });


        /** 
         * @Andryrkt
         * remplire le selecte du statu 
         */
        function SelectStatutValue1(data) {
            const uniqueStatuts = new Set();
            data.forEach(element => uniqueStatuts.add(element.Statut));

            const select = document.getElementById('statut');

            // Ajouter une option vide
            const emptyOption = document.createElement('option');
            emptyOption.value = ""; // Valeur vide
            emptyOption.textContent = "Sélectionnez un statut"; // Texte optionnel
            select.appendChild(emptyOption);

            uniqueStatuts.forEach(statut => {
                const option = document.createElement('option');
                option.value = statut;
                option.textContent = statut;
                // Ajouter l'option à l'élément <select>
                select.appendChild(option);
            });
        }

        /** 
         * @Andryrkt
         * rendre le tableau afficher sur l'écran
         */
        function renderData1(data) {
            var table = document.querySelector('.table'); // Sélectionnez le tableau existant

            // Création du corps du tableau s'il n'existe pas encore
            if (!table) {
                var container = document.getElementById('table-container');

                // Création d'un élément de tableau
                table = document.createElement('table');
                table.classList.add('table', 'table-striped', 'table-hover', 'table-shadow', 'shadow', 'bg-body-tertiary', 'rounded');

                // Création de l'en-tête du tableau
                var thead = document.createElement('thead');
                thead.classList.add('table-dark');
                var headerRow = document.createElement('tr');
                for (var key in data[0]) {
                    var th = document.createElement('th');
                    th.textContent = key.toUpperCase();
                    headerRow.appendChild(th);
                }
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Ajout du tableau au conteneur
                container.appendChild(table);
            }

            // Création du corps du tableau
            var tbody = table.querySelector('tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                table.appendChild(tbody);
            } else {
                tbody.innerHTML = ''; // Effacer le contenu précédent
            }

            // Ajouter les nouvelles lignes pour les données filtrées
            data.forEach(function(item, index) {
                var row = document.createElement('tr');
                row.classList.add(index % 2 === 0 ? 'table-dark-emphasis' : 'table-secondary'); // Alternance des couleurs de ligne
                for (var key in item) {
                    var cellule = document.createElement('td');
                    cellule.textContent = item[key];
                    // Vérifier si la clé est "statut" et attribuer une classe en conséquence
                    if (key === 'Statut') {
                        switch (item[key]) {
                            case 'Ouvert':
                                cellule.style.backgroundColor = "#efd807";
                                break;
                            case 'Payé':
                                cellule.style.backgroundColor = "#34c924";
                                break;
                            case 'Annulé':
                                cellule.style.backgroundColor = "#FF0000";
                                break;
                            case 'Compta':
                                cellule.style.backgroundColor = "#77b5fe";
                                break;
                        }
                    }
                    row.appendChild(cellule);
                }
                tbody.appendChild(row);
            });
        }



        /**
         * @Andryrkt
         * filtrer les données JSON à partir des critère entrer par l'utilisateur
         * returner une tableau de donnée filtré
         * 
         */
        function filtre(data) {

            const statutInput = document.querySelector('#statut');
            const matriculeInput = document.querySelector('#matricule');
            const dateCreationDebutInput = document.querySelector('#dateCreationDebut');
            const dateCreationFinInput = document.querySelector('#dateCreationFin');
            const dateDebutDebutInput = document.querySelector('#dateDebutDebut');
            const dateDebutFinInput = document.querySelector('#dateDebutFin');

            // Récupérer les valeurs des champs de saisie

            var critereStatut = statutInput.value.trim();
            var critereMatricule = matriculeInput.value.trim().toLowerCase();
            var dateCreationDebut = dateCreationDebutInput.value;
            var dateCreationFin = dateCreationFinInput.value;
            var dateDebutDebut = dateDebutDebutInput.value;
            var dateDebutFin = dateDebutFinInput.value;


            // Filtrer les données en fonction des critères
            var resultatsFiltres = data.filter(function(demande) {

                // Filtrer par statut (si un critère est fourni)
                var filtreStatut = !critereStatut || demande.Statut === critereStatut;
                var filtreMatricule = !critereMatricule || demande.Matricule.toLowerCase().includes(critereMatricule);
                // Filtrer par date de création/demande (si un critère est fourni)


                var filtreDateCreation = !dateCreationDebut || !dateCreationFin || (demande.Date_Demande >= dateCreationDebut && demande.Date_Demande <= dateCreationFin);
                // Filtrer par date de début (si un critère est fourni)
                var filtreDateDebut = !dateDebutDebut || !dateDebutFin || (demande.Date_Debut >= dateDebutDebut && demande.Date_Debut <= dateDebutFin);

                // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
                //return filtreStatut && filtreMatricule && filtreDateCreation && filtreDateDebut
                return (filtreMatricule && filtreStatut && filtreDateCreation && filtreDateDebut) || (!critereMatricule && !critereStatut && !dateCreationDebut && !dateCreationFin && !dateDebutDebut && !dateDebutFin);

            });

            return resultatsFiltres

        }

        /**
         * @Andryrkt
         * cette fonction permet d'exporter les données filtrée ou non dans une fichier excel
         */
        function ExportExcel(data) {

            // Filtre les données
            let donner = filtre(data);

            // Crée une feuille Excel
            const worksheet = XLSX.utils.json_to_sheet(donner);
            const workbook = XLSX.utils.book_new();

            // Ajoute les en-têtes à la feuille Excel
            const headers = [
                'Statut',
                'type document',
                'Numero Ordre Mission',
                'Date_Demande',
                'Motif_Deplacement',
                'Matricule',
                'Nom',
                'Prenoms',
                'Mode_Paiement',
                'Agence service',
                'Date_Debut',
                'Date_Fin',
                'Nombre_Jour',
                'Client',
                'Numero OR',
                'Lieu_Intervention',
                'Numero Vehicule',
                'Total Autres Depenses',
                'Total_General_Payer',
                'Devis'
            ];
            XLSX.utils.sheet_add_aoa(worksheet, [headers], {
                origin: "A1"
            });

            // Ajoute la feuille Excel au classeur
            XLSX.utils.book_append_sheet(workbook, worksheet, "Données");

            // Télécharge le fichier Excel
            XLSX.writeFile(workbook, "Exportation-Excel.xlsx", {
                compression: true
            });


        }
    </script>

</body>

</html>