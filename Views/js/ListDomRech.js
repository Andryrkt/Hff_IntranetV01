


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


    console.log(raw_data.length);
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
    const recherche = document.querySelector('#recherche');
    const reset = document.querySelector("#reset");
    const nombreLigne = document.querySelector("#nombreLigne");
    const nombreResultat = document.querySelector('#nombreResultat');

    nombreResultat.textContent = raw_data.length + ' résultats';

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

    //
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

    recherche.addEventListener('click', (e) => {
        e.preventDefault();
        executeFiltreEtRendu()
    });

    reset.addEventListener('click', (e) => {
        e.preventDefault();
        statutInput.value = "";
        matriculeInput.value = "";
        dateCreationDebutInput.value = "";
        dateCreationFinInput.value = "";
        dateDebutDebutInput.value = "";
        dateDebutFinInput.value = "";
        renderData1(raw_data);
        nombreResultat.textContent = raw_data.length + ' résultats';
    })


    /* 
    nombre de ligne selecte
    */
    // nombreLigne.addEventListener('change', (e) => {
    //     e.preventDefault();
    //     const itemsPerPage = nombreLigne.value; // Nombre d'éléments par page
    //     const totalPages = Math.ceil(raw_data.length / itemsPerPage); // Calculer le nombre total de pages
    //     let currentPage = 1; // Page actuelle

    //     // Fonction pour afficher les données pour la page donnée
    //     const renderDataForPage = (page) => {
    //         const startIndex = (page - 1) * itemsPerPage;
    //         const endIndex = Math.min(startIndex + itemsPerPage, raw_data.length); // Correction pour gérer la dernière page avec moins d'éléments
    //         const dataForPage = raw_data.slice(startIndex, endIndex);
    //         renderData1(dataForPage); // Appel de votre fonction de rendu des données avec les données de la page
    //     };

    //     // Fonction pour mettre à jour la classe active du bouton de pagination
    //     const updateActivePage = () => {
    //         const paginationItems = document.querySelectorAll('.pagination li');
    //         console.log(paginationItems);
    //         if (paginationItems.length >= currentPage) {
    //             paginationItems.forEach(item => {
    //                 item.classList.remove('active');
    //             });
    //             paginationItems[currentPage].classList.add('active');
    //         }
    //     };

    //     // Fonction pour rendre la pagination
    //     //Affichage des boutons de pagination
    //     const renderPagination = () => {
    //         const paginationContainer = document.querySelector('.pagination');
    //         paginationContainer.innerHTML = ''; // Effacer le contenu précédent

    //         const previousButton = document.createElement('li');
    //         previousButton.classList.add('page-item');
    //         previousButton.innerHTML = `<a class="page-link cursor-pointer">Précédent</a>`;
    //         previousButton.addEventListener('click', () => {
    //             if (currentPage > 1) {
    //                 currentPage--;
    //                 renderDataForPage(currentPage);
    //                 updateActivePage(); // Mettre à jour la classe active du bouton de pagination
    //             }
    //         });
    //         paginationContainer.appendChild(previousButton);

    //         for (let i = 1; i <= totalPages; i++) {
    //             const li = document.createElement('li');
    //             li.classList.add('page-item');
    //             li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
    //             li.addEventListener('click', () => {
    //                 currentPage = i;
    //                 renderDataForPage(currentPage);
    //                 updateActivePage(); // Mettre à jour la classe active du bouton de pagination
    //             });
    //             paginationContainer.appendChild(li);
    //         }

    //         const nextButton = document.createElement('li');
    //         nextButton.classList.add('page-item');
    //         nextButton.innerHTML = `<a class="page-link" href="#">Suivant</a>`;
    //         nextButton.addEventListener('click', () => {
    //             if (currentPage < totalPages) {
    //                 currentPage++;
    //                 renderDataForPage(currentPage);
    //                 updateActivePage(); // Mettre à jour la classe active du bouton de pagination
    //             }
    //         });
    //         paginationContainer.appendChild(nextButton);
    //         updateActivePage(); // Mettre à jour la classe active du bouton de pagination
    //     };
    //     // Appel initial pour afficher la pagination avec le numéro de page 1 activé
    //     renderPagination();

    //     // Initialisation : afficher les données pour la première page et la pagination
    //     renderDataForPage(currentPage);
    // })


    function executeFiltreEtRendu() {
        let donner = filtre(raw_data);

        nombreResultat.textContent = donner.length + ' résultats';
        // Filtre les données
        console.log(donner);
        if (donner.length > 0) {


            // Pagination
            const itemsPerPage = 20; // Nombre d'éléments par page
            const totalPages = Math.ceil(donner.length / itemsPerPage); // Calculer le nombre total de pages
            let currentPage = 1; // Page actuelle

            // Fonction pour afficher les données pour la page donnée
            const renderDataForPage = (page) => {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, raw_data.length); // Correction pour gérer la dernière page avec moins d'éléments
                const dataForPage = donner.slice(startIndex, endIndex);
                renderData1(dataForPage); // Appel de votre fonction de rendu des données avec les données de la page
            };

            // Fonction pour mettre à jour la classe active du bouton de pagination
            const updateActivePage = () => {
                const paginationItems = document.querySelectorAll('.pagination li');
                console.log(paginationItems);
                if (paginationItems.length >= currentPage) {
                    paginationItems.forEach(item => {
                        item.classList.remove('active');
                    });
                    paginationItems[currentPage].classList.add('active');
                }
            };

            // Fonction pour rendre la pagination
            //Affichage des boutons de pagination
            const renderPagination = () => {
                const paginationContainer = document.querySelector('.pagination');
                paginationContainer.innerHTML = ''; // Effacer le contenu précédent

                const previousButton = document.createElement('li');
                previousButton.classList.add('page-item');
                previousButton.innerHTML = `<a class="page-link cursor-pointer">Précédent</a>`;
                previousButton.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        renderDataForPage(currentPage);
                        updateActivePage(); // Mettre à jour la classe active du bouton de pagination
                    }
                });
                paginationContainer.appendChild(previousButton);

                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement('li');
                    li.classList.add('page-item');
                    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                    li.addEventListener('click', () => {
                        currentPage = i;
                        renderDataForPage(currentPage);
                        updateActivePage(); // Mettre à jour la classe active du bouton de pagination
                    });
                    paginationContainer.appendChild(li);
                }

                const nextButton = document.createElement('li');
                nextButton.classList.add('page-item');
                nextButton.innerHTML = `<a class="page-link" href="#">Suivant</a>`;
                nextButton.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderDataForPage(currentPage);
                        updateActivePage(); // Mettre à jour la classe active du bouton de pagination
                    }
                });
                paginationContainer.appendChild(nextButton);
                updateActivePage(); // Mettre à jour la classe active du bouton de pagination
            };
            // Appel initial pour afficher la pagination avec le numéro de page 1 activé
            renderPagination();

            // Initialisation : afficher les données pour la première page et la pagination
            renderDataForPage(currentPage);

            // Rend les données filtrées
        } else {
            console.log(new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value))

            // Afficher un message si le tableau est vide
            const container = document.getElementById('table-container');
            container.innerHTML = `<p class="fw-bold" style="text-align: center;">Il n'y a pas de donnée qui correspond à votre recherche.</p>`;

        }
    }

    ////////////////////////////////////////////////////

    //DEBUT PAGINATION

    // Traitement des données
    console.log(raw_data);

    // Pagination
    const itemsPerPage = 20; // Nombre d'éléments par page
    const totalPages = Math.ceil(raw_data.length / itemsPerPage); // Calculer le nombre total de pages
    let currentPage = 1; // Page actuelle

    // Fonction pour afficher les données pour la page donnée
    const renderDataForPage = (page) => {
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, raw_data.length); // Correction pour gérer la dernière page avec moins d'éléments
        const dataForPage = raw_data.slice(startIndex, endIndex);
        renderData1(dataForPage); // Appel de votre fonction de rendu des données avec les données de la page
    };

    // Fonction pour mettre à jour la classe active du bouton de pagination
    const updateActivePage = () => {
        const paginationItems = document.querySelectorAll('.pagination li');
        console.log(paginationItems);
        if (paginationItems.length >= currentPage) {
            paginationItems.forEach(item => {
                item.classList.remove('active');
            });
            paginationItems[currentPage].classList.add('active');
        }
    };

    // Fonction pour rendre la pagination
    //Affichage des boutons de pagination
    const renderPagination = () => {
        const paginationContainer = document.querySelector('.pagination');
        paginationContainer.innerHTML = ''; // Effacer le contenu précédent

        const previousButton = document.createElement('li');
        previousButton.classList.add('page-item');
        previousButton.innerHTML = `<a class="page-link cursor-pointer">Précédent</a>`;
        previousButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderDataForPage(currentPage);
                updateActivePage(); // Mettre à jour la classe active du bouton de pagination
            }
        });
        paginationContainer.appendChild(previousButton);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.classList.add('page-item');
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', () => {
                currentPage = i;
                renderDataForPage(currentPage);
                updateActivePage(); // Mettre à jour la classe active du bouton de pagination
            });
            paginationContainer.appendChild(li);
        }

        const nextButton = document.createElement('li');
        nextButton.classList.add('page-item');
        nextButton.innerHTML = `<a class="page-link" href="#">Suivant</a>`;
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderDataForPage(currentPage);
                updateActivePage(); // Mettre à jour la classe active du bouton de pagination
            }
        });
        paginationContainer.appendChild(nextButton);
        updateActivePage(); // Mettre à jour la classe active du bouton de pagination
    };
    // Appel initial pour afficher la pagination avec le numéro de page 1 activé
    renderPagination();

    // Initialisation : afficher les données pour la première page et la pagination
    renderDataForPage(currentPage);





    //FIN PAGINATION

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


// pagination





// 

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
const trCorps = document.querySelector('#trCorps');

// Création du corps du tableau s'il n'existe pas encore

    var container = document.getElementById('table-container');
    container.innerHTML = '';

// Ajouter les nouvelles lignes pour les données filtrées
data.forEach(function(item, index) {
    var row = document.createElement('tr');
    row.classList.add(index % 2 === 0 ? 'table-dark-emphasis' : 'table-secondary'); // Alternance des couleurs de ligne
    for (var key in item) {

        var cellule = document.createElement('td');
        cellule.classList.add('w-50');
        if (key === 'Date_Demande' || key === 'Date_Debut' || key === 'Date_Fin') {
            cellule.textContent = item[key].split('-').reverse().join('/');
        } else {

            cellule.textContent = item[key];
        }
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
    container.appendChild(row);
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

    // Filtrer par date de début de création (si un critère est fourni)
    var filtreDateDebutCreation = !dateCreationDebut || demande.Date_Demande >= dateCreationDebut;
    // Filtrer par date de fin de création (si un critère est fourni)
    var filtreDateFinCreation = !dateCreationFin || demande.Date_Demande <= dateCreationFin;


    // Filtrer par date de création/demande (si un critère est fourni)
    var filtreDateCreation = !dateCreationDebut || !dateCreationFin || (demande.Date_Demande >= dateCreationDebut && demande.Date_Demande <= dateCreationFin);

    // Filtrer par date de début de mission ou début (si un critère est fourni)
    var filtreDateDebutMission = !dateDebutDebut || demande.Date_Debut >= dateDebutDebut;

    // Filtrer par date de fin de mission ou début (si un critère est fourni)
    var filtreDateFinMission = !dateDebutFin || demande.Date_Debut <= dateDebutFin;

    // Filtrer par date de début (si un critère est fourni)
    var filtreDateDebut = !dateDebutDebut || !dateDebutFin || (demande.Date_Debut >= dateDebutDebut && demande.Date_Debut <= dateDebutFin);





    // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
    return (filtreMatricule && filtreStatut && filtreDateCreation && filtreDateDebut && filtreDateDebutCreation && filtreDateFinCreation && filtreDateDebutMission && filtreDateFinMission) || (!critereMatricule && !critereStatut && !dateCreationDebut && !dateCreationFin && !dateDebutDebut && !dateDebutFin && !dateDebutCreation && !filtreDateFinCreation && !filtreDateDebutMission && !filtreDateFinMission);

});

return resultatsFiltres;

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
    'Numéro d\'Ordre de Mission',
    'Date de Demande',
    'Motif de Déplacement',
    'Numero Matricule',
    'Nom',
    'Prénoms',
    'Mode de paiement',
    'Agence de service',
    'Date de Debut',
    'Date de Fin',
    'Nombre de Jour',
    'Client',
    'Numéro OR',
    'Lieu d\'intervention',
    'Numero Vehicule',
    'Total Autres Dépenses',
    'Total Général Payer',
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