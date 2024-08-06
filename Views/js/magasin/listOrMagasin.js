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

  /** MISE EN MAJUSCULE */
  const numDitInput = document.querySelector("#magasin_list_or_search_numDit");
  const refPieceInput = document.querySelector(
    "#magasin_list_or_search_referencePiece"
  );

  numDitInput.addEventListener("input", MiseMajusculeNumDit);
  refPieceInput.addEventListener("input", MiseMajusculeRefPiece);
  designationInput.addEventListener("input", MiseMajusculeDesignation);

  function MiseMajusculeNumDit() {
    numDitInput.value = numDitInput.value.toUpperCase();
  }
  function MiseMajusculeRefPiece() {
    refPieceInput.value = refPieceInput.value.toUpperCase();
  }

  function MiseMajusculeDesignation() {
    designationInput.value = designationInput.value.toUpperCase();
  }

  /** chiffre au lieu de lettre */
  const numOrInput = document.querySelector("#magasin_list_or_search_numOr");

  numOrInput.addEventListener("input", () => {
    numOrInput.value = numOrInput.value.replace(/[^0-9]/g, "");
  });
});

/** Autocompletion Designation */
/** Autocompletion Designation */
const designationInput = document.querySelector(
  "#magasin_list_or_search_designation"
);
const suggestions = document.getElementById("suggestions");

let debounceTimer;

function debounce(func, delay) {
  return function (...args) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => func.apply(this, args), delay);
  };
}

designationInput.addEventListener(
  "input",
  debounce(autocompleteDesignation, 300)
); // 300ms delay

function autocompleteDesignation() {
  const designation = designationInput.value.trim().toUpperCase();

  if (designation.length < 2) {
    // Minimum length for input
    suggestions.innerHTML = "";
    return;
  }

  let url = `/Hffintranet/designation-fetch/${encodeURIComponent(designation)}`;

  fetch(url)
    .then((response) => response.json())
    .then((designations) => {
      suggestions.innerHTML = "";
      designations.forEach((item) => {
        const li = document.createElement("li");
        li.className = "list-group-item";
        li.textContent = item.designationi.toUpperCase();
        li.addEventListener("click", function () {
          designationInput.value = this.textContent;
          suggestions.innerHTML = "";
        });
        suggestions.appendChild(li);
      });
    })
    .catch((error) => console.error("Error:", error));
}

/** Autocompletion RefPiece */
/** Autocompletion RefPiece */
const refPieceInput = document.querySelector(
  "#magasin_list_or_search_referencePiece"
);
const suggestionsRefPiece = document.getElementById("suggestionsRefPiece");

let debounceTimerRefpiece;

refPieceInput.addEventListener("input", debounce(autocompleteRefPiece, 300));

function autocompleteRefPiece() {
  const refPiece = refPieceInput.value.trim().toUpperCase();

  if (refPiece.length < 2) {
    // Minimum length for input
    suggestionsRefPiece.innerHTML = "";
    return;
  }

  let url = `/Hffintranet/refpiece-fetch/${encodeURIComponent(refPiece)}`;

  fetch(url)
    .then((response) => response.json())
    .then((refPieces) => {
      suggestionsRefPiece.innerHTML = "";
      refPieces.forEach((item) => {
        const li = document.createElement("li");
        li.className = "list-group-item";
        li.textContent = item.referencepiece.toUpperCase();
        li.addEventListener("click", function () {
          refPieceInput.value = this.textContent;
          suggestionsRefPiece.innerHTML = "";
        });
        suggestionsRefPiece.appendChild(li);
      });
    })
    .catch((error) => console.error("Error:", error));
}
