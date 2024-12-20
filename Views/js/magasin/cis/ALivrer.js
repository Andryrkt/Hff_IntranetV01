/** pour le separateur et fusion des numOR */
const tableBody = document.querySelector("#tableBody");
const rows = document.querySelectorAll("#tableBody tr");

let previousValues = {
  orNumber: null,
  ditNumber: null,
  planningDate: null,
  urgencyLevel: null,
  agencyEmet: null,
  serviceEmet: null,
  agencyDebit: null,
  serviceDebit: null,
  interventionNumber: null,
  user: null,
};

let rowSpanCount = 0;
let firstRowInGroup = null;

function applyRowspanAndClass(row, rowSpanCount) {
  const cellIndices = {
    ditNumber: 1, // N° DIT
    cisNumber: 2, // N° CIS
    agServWork: 4, //Agence et service travaux
    orNumber: 5, // N° OR
    agServDebit: 7, // Agences et service debiteur
    interventionNumber: 8, // N° Intv
  };

  Object.keys(cellIndices).forEach((key) => {
    let cell = row.getElementsByTagName("td")[cellIndices[key]];
    if (cell) {
      cell.rowSpan = rowSpanCount;
      cell.classList.add("rowspan-cell");

      if (key === "ditNumber") {
        // Crée le rectangle
        let rectangle = document.createElement("div");
        //let matMarqueCasier = row.getElementsByTagName("td")[5]?.textContent.trim() || "N/A"; // Colonne 5, à ajuster
        rectangle.textContent = "Loading ...";
        rectangle.classList.add("rectangle");

        // Ajouter le rectangle au début de la cellule
        cell.insertBefore(rectangle, cell.firstChild);

        // Récupérer la valeur de numOr
        let numOr = row
          .getElementsByTagName("td")
          [cellIndices["orNumber"]]?.textContent.trim(); // Colonne définie par "orNumber" dans cellIndices
        console.log(numOr);

        // Passer la valeur de numOr et le rectangle à la fonction
        if (numOr) {
          NumMatMarqueCasier(numOr, rectangle);
        } else {
          console.error("numOr introuvable ou vide pour cette ligne.");
        }
      }
    }
  });
}

function NumMatMarqueCasier(numOr, rectangle) {
  // Fetch les données dynamiques
  const url = `/Hffintranet/api/numMat-marq-casier/${numOr}`;
  fetch(url) // Remplacez avec votre URL d'API
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des données");
      }
      return response.json();
    })
    .then((data) => {
      let contenu = `${data.numMat} | ${data.marque} | ${data.casier}`;
      // Mettre à jour le contenu du rectangle avec les données récupérées
      rectangle.textContent = contenu || "N/A"; // Exemple avec une propriété
    })
    .catch((error) => {
      console.error("Erreur :", error);
      rectangle.textContent = "Erreur de chargement";
    });
}

function hideCells(row) {
  const cellIndices = [1, 2, 4, 5, 7, 8]; // Indices des colonnes à masquer
  cellIndices.forEach((index) => {
    let cell = row.getElementsByTagName("td")[index];
    if (cell) {
      cell.style.display = "none";
    }
  });
}

for (let i = 0; i < rows.length; i++) {
  let currentRow = rows[i];

  let currentValues = {
    ditNumber: currentRow.getElementsByTagName("td")[1].textContent.trim(),
    cisNumber: currentRow.getElementsByTagName("td")[2].textContent.trim(),
    agServWork: currentRow.getElementsByTagName("td")[4].textContent.trim(),
    orNumber: currentRow.getElementsByTagName("td")[5].textContent.trim(),
    agServDebit: currentRow.getElementsByTagName("td")[7].textContent.trim(),
    interventionNumber: currentRow
      .getElementsByTagName("td")[8]
      .textContent.trim(),
  };

  // Check if any of the key values differ from the previous row's values
  const hasGroupChanged = Object.keys(currentValues).some(
    (key) => previousValues[key] !== currentValues[key]
  );

  if (!previousValues.orNumber) {
    // Initialisation pour la première ligne
    firstRowInGroup = currentRow;
    rowSpanCount = 1;
  } else if (hasGroupChanged) {
    // Applique rowspan aux cellules précédentes avant de commencer un nouveau groupe
    if (firstRowInGroup) {
      applyRowspanAndClass(firstRowInGroup, rowSpanCount);
    }

    // Ajoute un séparateur
    let separatorRow = document.createElement("tr");
    separatorRow.classList.add("separator-row");
    let td = document.createElement("td");
    td.colSpan = currentRow.cells.length;
    td.classList.add("p-0");
    separatorRow.appendChild(td);
    tableBody.insertBefore(separatorRow, currentRow);

    // Réinitialisation pour le nouveau groupe
    rowSpanCount = 1;
    firstRowInGroup = currentRow;
  } else {
    // Masquer les cellules en doublon et augmenter le rowSpan
    rowSpanCount++;
    hideCells(currentRow);
  }

  // Mise à jour des valeurs précédentes
  previousValues = currentValues;
}

// Applique rowspan aux dernières lignes du groupe
if (firstRowInGroup) {
  applyRowspanAndClass(firstRowInGroup, rowSpanCount);
}

/** ====================================
 * MISE EN MAJUSCULE
 * ===================================*/
const numDitInput = document.querySelector("#a_livrer_search_numDit");
const refPieceInput = document.querySelector("#a_livrer_search_referencePiece");

numDitInput.addEventListener("input", MiseMajusculeNumDit);
refPieceInput.addEventListener("input", MiseMajusculeRefPiece);

function MiseMajusculeNumDit() {
  numDitInput.value = numDitInput.value.toUpperCase();
}
function MiseMajusculeRefPiece() {
  refPieceInput.value = refPieceInput.value.toUpperCase();
}

/** ===================================
 * chiffre au lielu de lettre
 * ===================================*/
const numOrInput = document.querySelector("#a_livrer_search_numOr");

numOrInput.addEventListener("input", () => {
  numOrInput.value = numOrInput.value.replace(/[^0-9]/g, "");
});

/** ==================================================
 * AFFICHER LES SERVICES SELON L'AGENCE SELECTIONNER
 * ==================================================*/
const agenceInput = document.querySelector("#a_livrer_search_agence");
const serviceInput = document.querySelector("#a_livrer_search_service");
const spinnerService = document.getElementById("spinner-service");
const serviceContainer = document.getElementById("service-container");

agenceInput.addEventListener("change", selectAgence);

function selectAgence() {
  serviceInput.disabled = false;

  const agence = agenceInput.value.split("-")[0];
  console.log(agence);

  let url = `/Hffintranet/service-informix-fetch/${agence}`;
  toggleSpinner(spinnerService, serviceContainer, true);

  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);

      // Supprimer toutes les options existantes
      while (serviceInput.options.length > 0) {
        serviceInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une service -- ";
      serviceInput.add(defaultOption);

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < services.length; i++) {
        var option = document.createElement("option");
        option.value = services[i].value;
        option.text = services[i].text;
        serviceInput.add(option);
      }

      //Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < serviceInput.options.length; i++) {
        var option = serviceInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => toggleSpinner(spinnerService, serviceContainer, false));
}

function toggleSpinner(spinnerService, serviceContainer, show) {
  spinnerService.style.display = show ? "inline-block" : "none";
  serviceContainer.style.display = show ? "none" : "block";
}
