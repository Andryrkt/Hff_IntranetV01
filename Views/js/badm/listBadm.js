import { FetchManager } from "./../FetchManager.js";

const excelBadmInput = document.querySelector("#excelBadm");
console.log(excelBadmInput);

excelBadmInput.addEventListener("click", fetchvaleur);

function fetchvaleur() {
  const fetchManager = new FetchManager("/Hffintranet");
  fetchManager
    .get("ListJsonBadm")
    .then((raw_data) => {
      console.log(raw_data);

      //export excel
      exportExcelButton.addEventListener("click", () => {
        ExportExcel(raw_data);
      });
    })
    .catch((error) => {
      console.error(
        "There has been a problem with your fetch operation:",
        error
      );
    });
}

/**
 * @Andryrkt
 * cette fonction permet d'exporter les données filtrée ou non dans une fichier excel
 */
function ExportExcel(data) {
  // Crée une feuille Excel
  const worksheet = XLSX.utils.json_to_sheet(data);
  const workbook = XLSX.utils.book_new();

  // Ajoute les en-têtes à la feuille Excel
  const headers = [
    "Statut",
    "type de Mouvement",
    "Numéro de demande BADM",
    "Date de Demande",
    "Agence_Service_Emetteur",
    "Casier_Emetteur",
    "Agence_Service_Destinataire",
    "Casier_Destinataire",
    "Motif_Arret_Materiel",
    "Etat_Achat",
    "Date_Mise_Location",
    "Cout_Acquisition",
    "Amortissement",
    "Valeur_Net_Comptable",
    "Nom_Client",
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

// let page = 1;
// let donner;
// const typeMouvemnetInput = document.querySelector("#typeMouvement");
// const idMaterielInput = document.querySelector("#idMateriel");

// const dateDemandeDebutInput = document.querySelector("#dateDemandeDebut");
// const dateDemandeFinInput = document.querySelector("#dateDemandeFin");

// const exportExcelButton = document.querySelector("#export");
// const rechercheInput = document.querySelector("#recherche");
// const resetInput = document.querySelector("#reset");
// const nombreLigneInput = document.querySelector("#nombreLigne");
// const nombreResultatInput = document.querySelector("#nombreResultat");

// // const urlStatut = "/Hffintranet/index.php?action=listStatut"
// console.log("okey");
// /**
//  * @Andryrkt
//  * récupère les donnée JSON et faire le traitement du recherhce, affichage, export excel
//  */
// function fetchvaleur() {
//   const fetchManager = new FetchManager("/Hffintranet");
//   fetchManager
//     .get("ListJsonBadm")
//     .then((raw_data) => {
//       console.log(raw_data);

//       validateDateRange(
//         "#dateDemandeDebut",
//         "#dateDemandeFin",
//         "dateCreationMessage"
//       );

//       // bouton de recherche
//       recherche.addEventListener("click", (e) => {
//         e.preventDefault();
//         donner = filtre(raw_data);
//         executeFiltreEtRendu(donner, page);
//       });

//       //bouton effacer tous les recherches
//       reset.addEventListener("click", (e) => {
//         e.preventDefault();
//         idMaterielInput.value = "";
//         typeMouvemnetInput.value = "";
//         dateDemandeDebutInput.value = "";
//         dateDemandeFinInput.value = "";
//       });

//       //export excel
//       exportExcelButton.addEventListener("click", () => {
//         ExportExcel(raw_data);
//       });
//     })
//     .catch((error) => {
//       console.error(
//         "There has been a problem with your fetch operation:",
//         error
//       );
//     });
// }

// fetchvaleur();
// function validateDateRange(
//   startDateSelector,
//   endDateSelector,
//   messageElementId
// ) {
//   const startDateInput = document.querySelector(startDateSelector);
//   const endDateInput = document.querySelector(endDateSelector);
//   const messageDisplay = document.getElementById(messageElementId);

//   function validateDates() {
//     const startDate = new Date(startDateInput.value);
//     const endDate = new Date(endDateInput.value);
//     if (startDate > endDate) {
//       messageDisplay.textContent =
//         "La date de début doit être inférieure à la date de fin.";
//     } else {
//       messageDisplay.textContent = "";
//     }
//   }

//   startDateInput.addEventListener("change", (e) => {
//     e.preventDefault();
//     validateDates();
//   });

//   endDateInput.addEventListener("change", (e) => {
//     e.preventDefault();
//     validateDates();
//   });
// }

// /**
//  *fonction qui rendre les données filtrée
//  *
//  */
// function executeFiltreEtRendu(raw_data, page) {
//   var state = {
//     querySet: donner,

//     page: page,
//     rows: 10,
//     window: 5,
//   };
//   console.log(state.page);
//   var data = pagination(state.querySet, state.page, state.rows);
//   var myList = data.querySet;
//   // Filtre les données
//   console.log(myList);
//   if (myList.length > 0) {
//     const container = document.querySelector("#noResult");
//     container.innerHTML = "";

//     pageButtons(data.pages, state);
//     renderData1(myList);
//     nombreResultat.textContent = donner.length + " résultats";
//   } else {
//     //console.log(new Date(dateCreationDebutInput.value) > new Date(dateCreationFinInput.value))

//     // Afficher un message si le tableau est vide
//     const noResult = document.querySelector("#noResult");
//     noResult.innerHTML = `<p class="fw-bold" style="text-align: center;">Il n'y a pas de données correspondant à votre recherche.</p>`;
//     var container = document.getElementById("table-container");
//     container.innerHTML = "";
//     nombreResultat.textContent = 0 + " résultats";
//   }
// }

/**
 * @Andryrkt
 * remplire le selecte du statu
 */
// function SelectStatutValue1(data) {
// const uniqueStatuts = new Set();
// data.forEach(element => uniqueStatuts.add(element.Statut));

// const select = document.getElementById('statut');

// // Ajouter une option vide
// const emptyOption = document.createElement('option');
// emptyOption.value = ""; // Valeur vide
// emptyOption.textContent = "Sélectionnez un statut"; // Texte optionnel
// select.appendChild(emptyOption);

// uniqueStatuts.forEach(statut => {
//     const option = document.createElement('option');
//     option.value = statut;
//     option.textContent = statut;
//     // Ajouter l'option à l'élément <select>
//     select.appendChild(option);
// });
// }

// function SousTypeDoc(data) {
//     const uniqueSousTypeDoc = new Set();
// data.forEach(element => uniqueSousTypeDoc.add(element.Sous_type_document));

// const select = document.getElementById('sousTypeDoc');

// // Ajouter une option vide
// const emptyOption = document.createElement('option');
// emptyOption.value = ""; // Valeur vide
// emptyOption.textContent = "Sélectionnez un sous type"; // Texte optionnel
// select.appendChild(emptyOption);

// uniqueSousTypeDoc.forEach(sousTypeDoc => {
//     const option = document.createElement('option');
//     option.value = sousTypeDoc;
//     option.textContent = sousTypeDoc;
//     // Ajouter l'option à l'élément <select>
//     select.appendChild(option);
// });

//}

// function pagination(querySet, page, rows) {
//   var trimStart = (page - 1) * rows;
//   var trimEnd = trimStart + rows;

//   var trimmedData = querySet.slice(trimStart, trimEnd);

//   var pages = Math.round(querySet.length / rows);

//   return {
//     querySet: trimmedData,
//     pages: pages,
//   };
// }

// function pageButtons(pages, state) {
//   var wrapper = document.getElementById("pagination-wrapper");

//   wrapper.innerHTML = ``;
//   console.log("Pages:", pages);
//   console.log(state);
//   var maxLeft = state.page - Math.floor(state.window / 2);
//   var maxRight = state.page + Math.floor(state.window / 2);

//   if (maxLeft < 1) {
//     maxLeft = 1;
//     maxRight = state.window;
//   }

//   if (maxRight > pages) {
//     maxLeft = pages - (state.window - 1);

//     if (maxLeft < 1) {
//       maxLeft = 1;
//     }
//     maxRight = pages;
//   }

//   for (var page = maxLeft; page <= maxRight; page++) {
//     wrapper.innerHTML += `<button value=${page} class="page btn btn-sm bg-black text-warning">${page}</button>`;
//   }

//   if (state.page != 1) {
//     wrapper.innerHTML =
//       `<button value=${1} class="page btn btn-sm bg-black text-warning">&#171; First</button>` +
//       wrapper.innerHTML;
//   }

//   if (state.page != pages) {
//     wrapper.innerHTML += `<button value=${pages} class="page btn btn-sm bg-black text-warning">Last &#187;</button>`;
//   }

//   document.querySelectorAll(".page").forEach(function (element) {
//     element.addEventListener("click", function () {
//       // Vide le conteneur de la table
//       document.getElementById("table-container").innerHTML = "";

//       // Met à jour l'état de la page
//       state.page = Number(this.value);
//       console.log(state.page);
//       executeFiltreEtRendu(state.querySet, state.page);
//       // Reconstruit la table
//       // buildTable();
//     });
//   });
// }

/**
 * @Andryrkt
 * rendre le tableau afficher sur l'écran
 */
// function renderData1(data) {
//   const trCorps = document.querySelector("#trCorps");

//   // Création du corps du tableau s'il n'existe pas encore

//   const container = document.getElementById("table-container");
//   container.innerHTML = "";

//   // Ajouter les nouvelles lignes pour les données filtrées
//   data.forEach(function (item, index) {
//     //console.log("les items:" + index);
//     const row = document.createElement("tr");
//     row.classList.add(index % 2 === 0 ? "table-gray-700" : "table-secondary"); // Alternance des couleurs de ligne
//     // Ajouter un bouton de duplication à chaque ligne
//     const duplicateButton = document.createElement("button");
//     duplicateButton.innerHTML = `<a href="/Hffintranet/dupliBADM/${item["Numero_Demande_BADM"]}/${item["ID_Demande_Mouvement_Materiel"]}" style="text-decoration: none; color: #000000; font-weight: 600">Dupliquer</a>`;
//     //duplicateButton.innerHTML = `<a href="/Hffintranet/index.php?action=DuplifierForm&NumDOM=${item['Numero_Ordre_Mission']}&IdDOM=${item['ID_Demande_Ordre_Mission']}&check=${item['Matricule']}" style="text-decoration: none;
//     //color: #000000; font-weight: 600">Dupliquer</a>`;
//     duplicateButton.classList.add("btn", "btn-warning", "mx-2", "my-2");
//     duplicateButton.style.backgroundColor = "#FBBB01";

//     row.appendChild(duplicateButton);

//     for (var key in item) {
//       var cellule = document.createElement("td");
//       cellule.classList.add("w-50");

//       if (key === "Date_Demande" || key === "Date_Mise_Location") {
//         cellule.textContent = item[key].split("-").reverse().join("/");
//       } else if (key === "Numero_Demande_BADM") {
//         var lien = document.createElement("a");
//         lien.href = `/Hffintranet/detailBadm/${item[key]}/${item["ID_Demande_Mouvement_Materiel"]}`;
//         lien.textContent = item[key];
//         cellule.appendChild(lien);
//       } else {
//         cellule.textContent = item[key];
//       }

//       //console.log(key === "ID_Statut_Demande");

//       // Vérifier si la clé est "statut" et attribuer une classe en conséquence
//       if (key === "Statut") {
//         switch (item[key]) {
//           case "Ouvert":
//             cellule.style.backgroundColor = "#FBBB01";
//             break;
//           case "Clôturé":
//             cellule.style.backgroundColor = "#34c924";
//             break;
//           case "Annulé":
//             cellule.style.backgroundColor = "#FF0000";
//             break;
//           case "Encours":
//             cellule.style.backgroundColor = "#77b5fe";
//             break;
//         }
//       }

//       row.appendChild(cellule);
//     }

//     container.appendChild(row);
//   });
// }

/**
 * @Andryrkt
 * filtrer les données JSON à partir des critère entrer par l'utilisateur
 * returner une tableau de donnée filtré
 *
 */
// function filtre(data) {
//   const critereTypeMouvemnetValue = typeMouvemnetInput.value.trim();
//   const critereIdMaterielValue = idMaterielInput.value.trim();
//   const dateDemandeDebutValue = dateDemandeDebutInput.value;
//   const dateDemandeFinValue = dateDemandeFinInput.value;

//   console.log(typeMouvemnetInput.value.trim());
//   console.log(critereIdMaterielValue);
//   console.log(dateDemandeDebutValue);
//   console.log(dateDemandeFinValue);

//   // Filtrer les données en fonction des critères
//   return data.filter(function (demande) {
//     //console.log(demande.Code_Mouvement);
//     // Filtrer par statut (si un critère est fourni)
//     var filtreTypeMouvemnet =
//       !critereTypeMouvemnetValue ||
//       demande.Description === critereTypeMouvemnetValue;
//     var filtreIdMateriel =
//       !critereIdMaterielValue ||
//       demande.ID_Materiel.includes(critereIdMaterielValue);

//     var filtreDateDebutDemande =
//       !dateDemandeDebutValue || demande.Date_Demande >= dateDemandeDebutValue;
//     var filtreDateFinDemande =
//       !dateDemandeFinValue || demande.Date_Demande <= dateDemandeFinValue;
//     var filtreDateDemande =
//       !dateDemandeDebutValue ||
//       !dateDemandeFinValue ||
//       (demande.Date_Demande >= dateDemandeDebutValue &&
//         demande.Date_Demande <= dateDemandeFinValue);

//     // Retourner true si toutes les conditions sont remplies ou si aucun critère n'est fourni, sinon false
//     return (
//       (filtreTypeMouvemnet &&
//         filtreIdMateriel &&
//         filtreDateDebutDemande &&
//         filtreDateFinDemande &&
//         filtreDateDemande) ||
//       (!critereTypeMouvemnetValue &&
//         !critereIdMaterielValue &&
//         !dateDemandeDebutValue &&
//         !dateDemandeFinValue &&
//         !filtreTypeMouvemnet &&
//         !filtreIdMateriel &&
//         !filtreDateDebutDemande &&
//         !filtreDateFinDemande)
//     );
//   });
// }
