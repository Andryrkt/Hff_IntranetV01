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
      }
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

    let currentValues = {
      ditNumber: currentRow.getElementsByTagName("td")[1].textContent.trim(),
      orNumber: currentRow.getElementsByTagName("td")[2].textContent.trim(),
      planningDate: currentRow.getElementsByTagName("td")[3].textContent.trim(),
      urgencyLevel: currentRow.getElementsByTagName("td")[4].textContent.trim(),
      agencyEmet: currentRow.getElementsByTagName("td")[6].textContent.trim(),
      serviceEmet: currentRow.getElementsByTagName("td")[7].textContent.trim(),
      agencyDebit: currentRow.getElementsByTagName("td")[8].textContent.trim(),
      serviceDebit: currentRow.getElementsByTagName("td")[9].textContent.trim(),
      interventionNumber: currentRow
        .getElementsByTagName("td")[10]
        .textContent.trim(),
      user: currentRow.getElementsByTagName("td")[18].textContent.trim(),
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
