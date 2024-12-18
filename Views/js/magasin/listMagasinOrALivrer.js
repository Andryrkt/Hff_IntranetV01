document.addEventListener("DOMContentLoaded", function () {
  /** RECHERCHE */
  // document.getElementById("searchInput").addEventListener("keyup", function () {
  //   let filter = this.value.toLowerCase();
  //   let rows = document.querySelectorAll("#tableBody tr");

  //   rows.forEach(function (row) {
  //     let text = row.textContent.toLowerCase();
  //     row.style.display = text.includes(filter) ? "" : "none";
  //   });
  // });

  /** pour le separateur et fusion des numOR */
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
      orNumber: 2, // N° OR
      planningDate: 3, // Date planning
      urgencyLevel: 4, // Niv. d'urg
      agencyEmet: 6, //Agences Emetteur
      serviceEmet: 7, //service Emetteur
      agencyDebit: 8, // Agences debiteur
      serviceDebit: 9, // Services debiteur
      interventionNumber: 10, // N° Intv
      user: 18, // Utilisateur
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
    const cellIndices = [1, 2, 3, 4, 6, 7, 8, 9, 10, 18]; // Indices des colonnes à masquer
    cellIndices.forEach((index) => {
      let cell = row.getElementsByTagName("td")[index];
      if (cell) {
        cell.style.display = "none";
      }
    });
  }

  for (let i = 0; i < rows.length; i++) {
    let currentRow = rows[i];
    let cells = Array.from(currentRow.getElementsByTagName("td"));

    let currentValues = {
      ditNumber: cells[1].textContent.trim(),
      orNumber: cells[2].textContent.trim(),
      planningDate: cells[3].textContent.trim(),
      urgencyLevel: cells[4].textContent.trim(),
      agencyEmet: cells[6].textContent.trim(),
      serviceEmet: cells[7].textContent.trim(),
      agencyDebit: cells[8].textContent.trim(),
      serviceDebit: cells[9].textContent.trim(),
      interventionNumber: cells[10].textContent.trim(),
      user: cells[18].textContent.trim(),
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

  /**===============================
   * Autocompletion Designation
   * ================================
   * */
  const designationInput = document.querySelector(
    "#magasin_liste_or_a_livrer_search_designation"
  );

  designationInput.addEventListener("input", autocompleteDesignation);

  function autocompleteDesignation() {
    const designation = designationInput.value;
    const url = `/Hffintranet/designation-fetch/${designation}`;
    fetch(url)
      .then((response) => response.json())
      .then((designations) => {
        console.log(designations);
        const suggestions = document.getElementById("suggestions");
        suggestions.innerHTML = "";
        designations.forEach((item) => {
          const li = document.createElement("li");
          li.className = "list-group-item";
          li.textContent = item.designationi; // Changez 'designation' par le champ pertinent
          li.addEventListener("click", function () {
            designationInput.value = this.textContent;
            suggestions.innerHTML = ""; // Effacez les suggestions après sélection
          });
          suggestions.appendChild(li);
        });
      })
      .catch((error) => console.error("Error:", error));
  }

  /** MISE EN MAJUSCULE */
  const numDitInput = document.querySelector(
    "#magasin_liste_or_a_livrer_search_numDit"
  );
  const refPieceInput = document.querySelector(
    "#magasin_liste_or_a_livrer_search_referencePiece"
  );

  numDitInput.addEventListener("input", MiseMajusculeNumDit);
  refPieceInput.addEventListener("input", MiseMajusculeRefPiece);

  function MiseMajusculeNumDit() {
    numDitInput.value = numDitInput.value.toUpperCase();
  }
  function MiseMajusculeRefPiece() {
    refPieceInput.value = refPieceInput.value.toUpperCase();
  }

  /** chiffre au lielu de lettre */
  const numOrInput = document.querySelector(
    "#magasin_liste_or_a_livrer_search_numOr"
  );

  numOrInput.addEventListener("input", () => {
    numOrInput.value = numOrInput.value.replace(/[^0-9]/g, "");
  });
});

/** AFFICHER LES SERVICES SELON L'AGENCE SELECTIONNER  */
const agenceInput = document.querySelector(
  "#magasin_liste_or_a_livrer_search_agence"
);
const serviceInput = document.querySelector(
  "#magasin_liste_or_a_livrer_search_service"
);

agenceInput.addEventListener("change", selectAgence);

function selectAgence() {
  serviceInput.disabled = false;

  const agence = agenceInput.value;
  let url = `/Hffintranet/service-informix-fetch/${agence}`;
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
    .catch((error) => console.error("Error:", error));
}
