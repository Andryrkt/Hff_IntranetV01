document.addEventListener("DOMContentLoaded", function () {
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
        cellToRowspanNumDit.rowSpan = rowSpanCount;
        cellToRowspanNumOr.rowSpan = rowSpanCount;
        cellToRowspanNumDit.classList.add("rowspan-cell"); // Appliquer les styles pour centrer le texte
        cellToRowspanNumOr.classList.add("rowspan-cell"); // Appliquer les styles pour centrer le texte
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
        currentRow.getElementsByTagName("td")[2].style.display = "none"; // Masquer la cellule OR dans les lignes suivantes
        currentRow.getElementsByTagName("td")[1].style.display = "none";
      }
    }

    previousOrNumber = currentOrNumber;
  }

  // Appliquer le rowspan à la dernière série de lignes
  if (firstRowInGroup) {
    let cellToRowspanNumDit = firstRowInGroup.getElementsByTagName("td")[1]; // Modifier l'indice selon la position du numéro OR
    let cellToRowspanNumOr = firstRowInGroup.getElementsByTagName("td")[2];
    cellToRowspanNumDit.rowSpan = rowSpanCount;
    cellToRowspanNumOr.rowSpan = rowSpanCount;
    cellToRowspanNumDit.classList.add("rowspan-cell"); // Appliquer les styles pour centrer le texte
    cellToRowspanNumOr.classList.add("rowspan-cell");
  }

  /** Autocompletion Designation */
  const designationInput = document.querySelector(
    "#magasin_list_or_search_designation"
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
  const numDitInput = document.querySelector("#magasin_list_or_search_numDit");
  const refPieceInput = document.querySelector(
    "#magasin_list_or_search_referencePiece"
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
  const numOrInput = document.querySelector("#magasin_list_or_search_numOr");
  console.log(numOrInput);

  numOrInput.addEventListener("input", () => {
    numOrInput.value = numOrInput.value.replace(/[^0-9]/g, "");
  });
});
