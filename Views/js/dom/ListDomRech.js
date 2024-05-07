const statutInput = document.querySelector("#statut");
const matriculeInput = document.querySelector("#matricule");
const sousTypeDocInput = document.querySelector("#sousTypeDoc");
const numDomInput = document.querySelector("#numDom");
const dateCreationDebutInput = document.querySelector("#dateCreationDebut");
const dateCreationFinInput = document.querySelector("#dateCreationFin");
const dateDebutDebutInput = document.querySelector("#dateDebutDebut");
const dateDebutFinInput = document.querySelector("#dateDebutFin");
const exportExcelButton = document.querySelector("#export");
const rechercheInput = document.querySelector("#recherche");
const resetInput = document.querySelector("#reset");
const nombreLigneInput = document.querySelector("#nombreLigne");
const nombreResultatInput = document.querySelector("#nombreResultat");

const urlStatut = "/Hffintranet/index.php?action=listStatut";

/**
 * @Andryrkt
 * récupère les donnée JSON et faire le traitement du recherhce, affichage, export excel
 */
async function fetchvaleur() {
  const url = "/Hffintranet/index.php?action=recherche";
  return await fetch(url).then((response) => {
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    return response.json();
  });
}

fetchvaleur()
  .then((raw_data) => {
    console.log(raw_data);
    // afficher les donnée du selecte statut
    SelectStatutValue1(raw_data);

    SousTypeDoc(raw_data);

    dateCreationDebutInput.addEventListener("change", (e) => {
      e.preventDefault();
      // Vérifier si la date de début est supérieure à la date de fin
      if (
        new Date(dateCreationDebutInput.value) >
        new Date(dateCreationFinInput.value)
      ) {
        const message = document.getElementById("dateCreationMessage");
        message.textContent =
          "La date de début doit être inférieure à la date de fin.";
      } else {
        const message = document.getElementById("dateCreationMessage");
        message.textContent = "";
      }
    });

    dateCreationFinInput.addEventListener("change", (e) => {
      e.preventDefault();

      if (
        new Date(dateCreationDebutInput.value) >
        new Date(dateCreationFinInput.value)
      ) {
        const message = document.getElementById("dateCreationMessage");
        message.textContent =
          "La date de début doit être inférieure à la date de fin.";
      } else {
        const message = document.getElementById("dateCreationMessage");
        message.textContent = "";
      }
    });

    dateDebutDebutInput.addEventListener("change", (e) => {
      e.preventDefault();
      if (
        new Date(dateDebutDebutInput.value) > new Date(dateDebutFinInput.value)
      ) {
        const message = document.getElementById("dateCreationMessage");
        message.textContent =
          "La date de début doit être inférieure à la date de fin.";
      } else {
        const message = document.getElementById("dateCreationMessage");
        message.textContent = "";
      }
    });
    dateDebutFinInput.addEventListener("change", (e) => {
      e.preventDefault();
      if (
        new Date(dateDebutDebutInput.value) > new Date(dateDebutFinInput.value)
      ) {
        const message = document.getElementById("dateCreationMessage");
        message.textContent =
          "La date de début doit être inférieure à la date de fin.";
      } else {
        const message = document.getElementById("dateCreationMessage");
        message.textContent = "";
      }
    });

    /* 
        bouton de recherche
    */
    recherche.addEventListener("click", (e) => {
      e.preventDefault();
      executeFiltreEtRendu();
    });

    /*
        bouton effacer tous les recherches
   */
    reset.addEventListener("click", (e) => {
      e.preventDefault();
      statutInput.value = "";
      matriculeInput.value = "";
      sousTypeDocInput.value = "";
      numDomInput.value = "";
      dateCreationDebutInput.value = "";
      dateCreationFinInput.value = "";
      dateDebutDebutInput.value = "";
      dateDebutFinInput.value = "";
    });

    /**
     *fonction qui fait le rendu filtré
     *
     */
    function executeFiltreEtRendu() {
      let donner = filtre(raw_data);
      // Filtre les données
      console.log(donner);
      if (donner.length > 0) {
        const container = document.querySelector("#noResult");
        container.innerHTML = "";
        renderData1(donner);
        nombreResultat.textContent = donner.length + " résultats";
      } else {
        console.log(
          new Date(dateCreationDebutInput.value) >
            new Date(dateCreationFinInput.value)
        );

        // Afficher un message si le tableau est vide
        const noResult = document.querySelector("#noResult");
        noResult.innerHTML = `<p class="fw-bold" style="text-align: center;">Il n'y a pas de données correspondant à votre recherche.</p>`;
        var container = document.getElementById("table-container");
        container.innerHTML = "";
        nombreResultat.textContent = 0 + " résultats";
      }
    }

    //export excel
    exportExcelButton.addEventListener("click", () => {
      ExportExcel(raw_data);
    });
  })
  .catch((error) => {
    console.error("There has been a problem with your fetch operation:", error);
  });

/**
 * @Andryrkt
 * remplire le selecte du statu
 */
function SelectStatutValue1(data) {
  const uniqueStatuts = new Set();
  data.forEach((element) => uniqueStatuts.add(element.Statut));

  const select = document.getElementById("statut");

  // Ajouter une option vide
  const emptyOption = document.createElement("option");
  emptyOption.value = ""; // Valeur vide
  emptyOption.textContent = "Sélectionnez un statut"; // Texte optionnel
  select.appendChild(emptyOption);

  uniqueStatuts.forEach((statut) => {
    const option = document.createElement("option");
    option.value = statut;
    option.textContent = statut;
    // Ajouter l'option à l'élément <select>
    select.appendChild(option);
  });
}

function SousTypeDoc(data) {
  const uniqueSousTypeDoc = new Set();
  data.forEach((element) => uniqueSousTypeDoc.add(element.Sous_type_document));

  const select = document.getElementById("sousTypeDoc");

  // Ajouter une option vide
  const emptyOption = document.createElement("option");
  emptyOption.value = ""; // Valeur vide
  emptyOption.textContent = "Sélectionnez un sous type"; // Texte optionnel
  select.appendChild(emptyOption);

  uniqueSousTypeDoc.forEach((sousTypeDoc) => {
    const option = document.createElement("option");
    option.value = sousTypeDoc;
    option.textContent = sousTypeDoc;
    // Ajouter l'option à l'élément <select>
    select.appendChild(option);
  });
}

/**
 * @Andryrkt
 * rendre le tableau afficher sur l'écran
 */
function renderData1(data) {
  const trCorps = document.querySelector("#trCorps");

  // Création du corps du tableau s'il n'existe pas encore

  const container = document.getElementById("table-container");
  container.innerHTML = "";

  // Ajouter les nouvelles lignes pour les données filtrées
  data.forEach(function (item, index) {
    const row = document.createElement("tr");
    row.classList.add(index % 2 === 0 ? "table-gray-700" : "table-secondary"); // Alternance des couleurs de ligne

    // Créer une cellule pour les boutons
    const buttonCell = document.createElement("td");

    // Ajouter un bouton d'annulation à chaque ligne
    const annulationButton = document.createElement("button");
    annulationButton.innerHTML = `<a href="/Hffintranet/index.php?action=annuler&NumDOM=${item["Numero_Ordre_Mission"]}&IdDOM=${item["ID_Demande_Ordre_Mission"]}&check=${item["Matricule"]}" style="text-decoration: none; color:red; font-weight: 600">Annuler</a>`;
    annulationButton.classList.add("btn", "btn-warning", "mx-2", "my-1");
    annulationButton.style.backgroundColor = "#000";
    annulationButton.style.borderStyle = "none";

    buttonCell.appendChild(annulationButton);

    // Ajouter un bouton de duplication à chaque ligne
    const duplicateButton = document.createElement("button");
    duplicateButton.innerHTML = `<a href="/Hffintranet/index.php?action=DuplifierForm&NumDOM=${item["Numero_Ordre_Mission"]}&IdDOM=${item["ID_Demande_Ordre_Mission"]}&check=${item["Matricule"]}" style="text-decoration: none; color: #000000; font-weight: 600">Dupliquer</a>`;
    duplicateButton.classList.add("btn", "btn-warning", "mx-2", "my-1");
    duplicateButton.style.backgroundColor = "#FBBB01";
    buttonCell.appendChild(duplicateButton);

    // Ajouter la cellule des boutons à la ligne
    row.appendChild(buttonCell);

    for (var key in item) {
      const cellule = document.createElement("td");
      cellule.classList.add("w-50");

      if (
        key === "Matricule" ||
        key === "Date_Demande" ||
        key === "Date_Debut" ||
        key === "Date_Fin" ||
        key === "Nombre_Jour"
      ) {
        cellule.style.textAlign = "center";
      }

      if (key === "Total_Autres_Depenses" || key === "Total_General_Payer") {
        cellule.style.textAlign = "end";
      }

      // if(key==='ID_Demande_Ordre_Mission'){
      //     cellule.style.display = 'none';
      // }

      if (
        key === "Date_Demande" ||
        key === "Date_Debut" ||
        key === "Date_Fin"
      ) {
        cellule.textContent = item[key].split("-").reverse().join("/");
      } else if (key === "Numero_Ordre_Mission") {
        var lien = document.createElement("a");
        lien.href = `/Hffintranet/index.php?action=DetailDOM&NumDom=${item[key]}&Id=${item["ID_Demande_Ordre_Mission"]}`;
        lien.textContent = item[key];
        cellule.appendChild(lien);
      } else {
        cellule.textContent = item[key];
      }

      // Vérifier si la clé est "statut" et attribuer une classe en conséquence
      if (key === "Statut") {
        switch (item[key]) {
          case "Ouvert":
            cellule.style.backgroundColor = "#FBBB01";
            break;
          case "Payé":
            cellule.style.backgroundColor = "#34c924";
            break;
          case "Annulé":
            cellule.style.backgroundColor = "#FF0000";
            break;
          case "Compta":
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
  // Récupérer les valeurs des champs de saisie

  const critereStatutValue = statutInput.value.trim();
  const critereSousTypeDocValue = sousTypeDocInput.value.trim();
  const critereMatriculeValue = matriculeInput.value.trim().toLowerCase();
  const critereNumDomValue = numDomInput.value.trim();
  const dateCreationDebutValue = dateCreationDebutInput.value;
  const dateCreationFinValue = dateCreationFinInput.value;
  const dateDebutDebutValue = dateDebutDebutInput.value;
  const dateDebutFinValue = dateDebutFinInput.value;

  // Filtrer les données en fonction des critères
  return (resultatsFiltres = data.filter(function (demande) {
    // Filtrer par statut (si un critère est fourni)
    var filtreStatut =
      !critereStatutValue || demande.Statut === critereStatutValue;
    var filtreMatricule =
      !critereMatriculeValue ||
      demande.Matricule.toLowerCase().includes(critereMatriculeValue);
    var filtreNumDom =
      !critereNumDomValue ||
      demande.Numero_Ordre_Mission.includes(critereNumDomValue);
    var filtreSousTypeDoc =
      !critereSousTypeDocValue ||
      demande.Sous_type_document === critereSousTypeDocValue;

    // Filtrer par date de début de création (si un critère est fourni)
    var filtreDateDebutCreation =
      !dateCreationDebutValue || demande.Date_Demande >= dateCreationDebutValue;
    // Filtrer par date de fin de création (si un critère est fourni)
    var filtreDateFinCreation =
      !dateCreationFinValue || demande.Date_Demande <= dateCreationFinValue;
    // Filtrer par date de création/demande (si un critère est fourni)
    var filtreDateCreation =
      !dateCreationDebutValue ||
      !dateCreationFinValue ||
      (demande.Date_Demande >= dateCreationDebutValue &&
        demande.Date_Demande <= dateCreationFinValue);

    // Filtrer par date de début de mission ou début (si un critère est fourni)
    var filtreDateDebutMission =
      !dateDebutDebutValue || demande.Date_Debut >= dateDebutDebutValue;
    // Filtrer par date de fin de mission ou début (si un critère est fourni)
    var filtreDateFinMission =
      !dateDebutFinValue || demande.Date_Debut <= dateDebutFinValue;
    // Filtrer par date de début (si un critère est fourni)
    var filtreDateDebut =
      !dateDebutDebutValue ||
      !dateDebutFinValue ||
      (demande.Date_Debut >= dateDebutDebutValue &&
        demande.Date_Debut <= dateDebutFinValue);

    // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
    return (
      (filtreMatricule &&
        filtreNumDom &&
        filtreStatut &&
        filtreDateCreation &&
        filtreDateDebut &&
        filtreDateDebutCreation &&
        filtreDateFinCreation &&
        filtreDateDebutMission &&
        filtreDateFinMission &&
        filtreSousTypeDoc) ||
      (!critereMatriculeValue &&
        !critereNumDomValue &&
        !critereStatutValue &&
        !dateCreationDebutValue &&
        !dateCreationFinValue &&
        !dateDebutDebutValue &&
        !dateDebutFinValue &&
        !critereSousTypeDocValue &&
        !dateDebutCreation &&
        !filtreDateFinCreation &&
        !filtreDateDebutMission &&
        !filtreDateFinMission &&
        !filtreSousTypeDoc)
    );
  }));
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
    "Id",
    "Statut",
    "type document",
    "Numéro d'Ordre de Mission",
    "Date de Demande",
    "Motif de Déplacement",
    "Numero Matricule",
    "Nom",
    "Prénoms",
    "Mode de paiement",
    "Agence de service",
    "Date de Debut",
    "Date de Fin",
    "Nombre de Jour",
    "Client",
    "Numéro OR",
    "Lieu d'intervention",
    "Numero Vehicule",
    "Total Autres Dépenses",
    "Total Général Payer",
    "Devis",
  ];
  XLSX.utils.sheet_add_aoa(worksheet, [headers], {
    origin: "A1",
  });

  // Ajoute la feuille Excel au classeur
  XLSX.utils.book_append_sheet(workbook, worksheet, "Données");

  // Télécharge le fichier Excel
  XLSX.writeFile(workbook, "Exportation-Excel.xlsx", {
    compression: true,
  });
}

//////////////////////////////////////////////////////////////////////////////////////

//  // Écouteur d'événement pour le changement de statut
//  statutInput.addEventListener('change', (e) => {
//     e.preventDefault();
//     executeFiltreEtRendu();
// });
// let timeoutId;
// // Écouteur d'événement pour la saisie dans le champ de matricule
// matriculeInput.addEventListener('input', (e) => {
//     e.preventDefault();
//     clearTimeout(timeoutId); // Effacer le délai précédent

//     // Définir un délai avant d'exécuter le filtrage et le rendu des données
//     timeoutId = setTimeout(executeFiltreEtRendu, 500);
// });

// //

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

// // Pagination
// const itemsPerPage = 20; // Nombre d'éléments par page
// const totalPages = Math.ceil(donner.length / itemsPerPage); // Calculer le nombre total de pages
// let currentPage = 1; // Page actuelle

// // Fonction pour afficher les données pour la page donnée
// const renderDataForPage = (page) => {
//     const startIndex = (page - 1) * itemsPerPage;
//     const endIndex = Math.min(startIndex + itemsPerPage, raw_data.length); // Correction pour gérer la dernière page avec moins d'éléments
//     const dataForPage = donner.slice(startIndex, endIndex);
//     renderData1(dataForPage); // Appel de votre fonction de rendu des données avec les données de la page
// };

// // Fonction pour mettre à jour la classe active du bouton de pagination
// const updateActivePage = () => {
//     const paginationItems = document.querySelectorAll('.pagination li');
//     console.log(paginationItems);
//     if (paginationItems.length >= currentPage) {
//         paginationItems.forEach(item => {
//             item.classList.remove('active');
//         });
//         paginationItems[currentPage].classList.add('active');
//     }
// };

// // Fonction pour rendre la pagination
// //Affichage des boutons de pagination
// const renderPagination = () => {
//     const paginationContainer = document.querySelector('.pagination');
//     paginationContainer.innerHTML = ''; // Effacer le contenu précédent

//     const previousButton = document.createElement('li');
//     previousButton.classList.add('page-item');
//     previousButton.innerHTML = `<a class="page-link cursor-pointer">Précédent</a>`;
//     previousButton.addEventListener('click', () => {
//         if (currentPage > 1) {
//             currentPage--;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         }
//     });
//     paginationContainer.appendChild(previousButton);

//     for (let i = 1; i <= totalPages; i++) {
//         const li = document.createElement('li');
//         li.classList.add('page-item');
//         li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
//         li.addEventListener('click', () => {
//             currentPage = i;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         });
//         paginationContainer.appendChild(li);
//     }

//     const nextButton = document.createElement('li');
//     nextButton.classList.add('page-item');
//     nextButton.innerHTML = `<a class="page-link" href="#">Suivant</a>`;
//     nextButton.addEventListener('click', () => {
//         if (currentPage < totalPages) {
//             currentPage++;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         }
//     });
//     paginationContainer.appendChild(nextButton);
//     updateActivePage(); // Mettre à jour la classe active du bouton de pagination
// };
// // Appel initial pour afficher la pagination avec le numéro de page 1 activé
// renderPagination();

// // Initialisation : afficher les données pour la première page et la pagination
// renderDataForPage(currentPage);

//  //DEBUT PAGINATION

// // Traitement des données
// console.log(raw_data);

// // Pagination
// const itemsPerPage = 20; // Nombre d'éléments par page
// const totalPages = Math.ceil(raw_data.length / itemsPerPage); // Calculer le nombre total de pages
// let currentPage = 1; // Page actuelle

// // Fonction pour afficher les données pour la page donnée
// const renderDataForPage = (page) => {
//     const startIndex = (page - 1) * itemsPerPage;
//     const endIndex = Math.min(startIndex + itemsPerPage, raw_data.length); // Correction pour gérer la dernière page avec moins d'éléments
//     const dataForPage = raw_data.slice(startIndex, endIndex);
//     renderData1(dataForPage); // Appel de votre fonction de rendu des données avec les données de la page
// };

// // Fonction pour mettre à jour la classe active du bouton de pagination
// const updateActivePage = () => {
//     const paginationItems = document.querySelectorAll('.pagination li');
//     console.log(paginationItems);
//     if (paginationItems.length >= currentPage) {
//         paginationItems.forEach(item => {
//             item.classList.remove('active');
//         });
//         paginationItems[currentPage].classList.add('active');
//     }
// };

// // Fonction pour rendre la pagination
// //Affichage des boutons de pagination
// const renderPagination = () => {
//     const paginationContainer = document.querySelector('.pagination');
//     paginationContainer.innerHTML = ''; // Effacer le contenu précédent

//     const previousButton = document.createElement('li');
//     previousButton.classList.add('page-item');
//     previousButton.innerHTML = `<a class="page-link cursor-pointer">Précédent</a>`;
//     previousButton.addEventListener('click', () => {
//         if (currentPage > 1) {
//             currentPage--;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         }
//     });
//     paginationContainer.appendChild(previousButton);

//     for (let i = 1; i <= totalPages; i++) {
//         const li = document.createElement('li');
//         li.classList.add('page-item');
//         li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
//         li.addEventListener('click', () => {
//             currentPage = i;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         });
//         paginationContainer.appendChild(li);
//     }

//     const nextButton = document.createElement('li');
//     nextButton.classList.add('page-item');
//     nextButton.innerHTML = `<a class="page-link" href="#">Suivant</a>`;
//     nextButton.addEventListener('click', () => {
//         if (currentPage < totalPages) {
//             currentPage++;
//             renderDataForPage(currentPage);
//             updateActivePage(); // Mettre à jour la classe active du bouton de pagination
//         }
//     });
//     paginationContainer.appendChild(nextButton);
//     updateActivePage(); // Mettre à jour la classe active du bouton de pagination
// };
// // Appel initial pour afficher la pagination avec le numéro de page 1 activé
// renderPagination();

// // Initialisation : afficher les données pour la première page et la pagination
// renderDataForPage(currentPage);

//FIN PAGINATION
