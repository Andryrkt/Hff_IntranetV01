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
  const tableBody = document.querySelector("#tableBody");
  const rows = document.querySelectorAll("#tableBody tr");

  let previousOrNumber = null;
  let rowSpanCount = 0;
  let firstRowInGroup = null;

  for (var i = 0; i < rows.length; i++) {
    let currentRow = rows[i];
    let orNumberCell = currentRow.getElementsByTagName("td")[2]; // Modifier l'indice selon la position du numéro OR
    let currentOrNumber = orNumberCell ? orNumberCell.textContent.trim() : null;

    if (previousOrNumber === null) {
      // Initialisation pour la première ligne
      firstRowInGroup = currentRow;
      rowSpanCount = 1;
    } else if (previousOrNumber && previousOrNumber !== currentOrNumber) {
      if (firstRowInGroup) {
        let cellToRowspanNumDit = firstRowInGroup.getElementsByTagName("td")[1]; // Modifier l'indice selon la position du numéro OR
        let cellToRowspanNumOr = firstRowInGroup.getElementsByTagName("td")[2];
        let cellToRowspanInter = firstRowInGroup.getElementsByTagName("td")[8];
        let cellToRowspanAgence =
          firstRowInGroup.getElementsByTagName("td")[15];
        let cellToRowspanUtilisateur =
          firstRowInGroup.getElementsByTagName("td")[6];
        let cellToRowspanService =
          firstRowInGroup.getElementsByTagName("td")[7];
        cellToRowspanNumDit.rowSpan = rowSpanCount;
        cellToRowspanNumOr.rowSpan = rowSpanCount;
        cellToRowspanInter.rowSpan = rowSpanCount;
        cellToRowspanAgence.rowSpan = rowSpanCount;
        cellToRowspanService.rowSpan = rowSpanCount;
        cellToRowspanUtilisateur.rowSpan = rowSpanCount;
        cellToRowspanNumDit.classList.add("rowspan-cell");
        cellToRowspanNumOr.classList.add("rowspan-cell");
        cellToRowspanInter.classList.add("rowspan-cell");
        cellToRowspanAgence.classList.add("rowspan-cell");
        cellToRowspanService.classList.add("rowspan-cell");
        cellToRowspanUtilisateur.classList.add("rowspan-cell");
      }

      // Début pour le séparateur
      let separatorRow = document.createElement("tr");
      separatorRow.classList.add("separator-row");
      let td = document.createElement("td");
      td.colSpan = currentRow.cells.length;
      td.classList.add("p-0");
      separatorRow.appendChild(td);
      tableBody.insertBefore(separatorRow, currentRow);
      // Fin pour le séparateur

      rowSpanCount = 1;
      firstRowInGroup = currentRow;
    } else {
      rowSpanCount++;
      if (firstRowInGroup !== currentRow) {
        currentRow.getElementsByTagName("td")[2].style.display = "none";
        currentRow.getElementsByTagName("td")[1].style.display = "none";
        currentRow.getElementsByTagName("td")[8].style.display = "none";
        currentRow.getElementsByTagName("td")[6].style.display = "none";
        currentRow.getElementsByTagName("td")[7].style.display = "none";
        currentRow.getElementsByTagName("td")[15].style.display = "none";
      }
    }

    previousOrNumber = currentOrNumber;
  }

  // Appliquer le rowspan à la dernière série de lignes
  if (firstRowInGroup) {
    let cellToRowspanNumDit = firstRowInGroup.getElementsByTagName("td")[1]; // Modifier l'indice selon la position du numéro OR
    let cellToRowspanNumOr = firstRowInGroup.getElementsByTagName("td")[2];
    let cellToRowspanInter = firstRowInGroup.getElementsByTagName("td")[8];
    let cellToRowspanAgence = firstRowInGroup.getElementsByTagName("td")[6];
    let cellToRowspanService = firstRowInGroup.getElementsByTagName("td")[7];
    let cellToRowspanUtilisateur =
      firstRowInGroup.getElementsByTagName("td")[15];
    cellToRowspanNumDit.rowSpan = rowSpanCount;
    cellToRowspanNumOr.rowSpan = rowSpanCount;
    cellToRowspanInter.rowSpan = rowSpanCount;
    cellToRowspanAgence.rowSpan = rowSpanCount;
    cellToRowspanService.rowSpan = rowSpanCount;
    cellToRowspanUtilisateur.rowSpan = rowSpanCount;
    cellToRowspanNumDit.classList.add("rowspan-cell");
    cellToRowspanNumOr.classList.add("rowspan-cell");
    cellToRowspanInter.classList.add("rowspan-cell");
    cellToRowspanAgence.classList.add("rowspan-cell");
    cellToRowspanService.classList.add("rowspan-cell");
    cellToRowspanUtilisateur.classList.add("rowspan-cell");
  }

  // /** Autocompletion Designation */
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
