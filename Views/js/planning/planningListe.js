const tableBody = document.querySelector("#tableBody");
const rows = document.querySelectorAll("#tableBody tr");

let previousOrNumber = null;
let rowSpanCount = 0;
let firstRowInGroup = null;

for (var i = 0; i < rows.length; i++) {
  let currentRow = rows[i];
  let orNumberCell = currentRow.getElementsByTagName("td")[8]; // Modifier l'indice selon la position du numéro OR
  let currentOrNumber = orNumberCell ? orNumberCell.textContent.trim() : null;

  if (previousOrNumber === null) {
    // Initialisation pour la première ligne
    firstRowInGroup = currentRow;
    rowSpanCount = 1;
  } else if (previousOrNumber && previousOrNumber !== currentOrNumber) {
    if (firstRowInGroup) {
      let cellToRowspanAgenceServ =
        firstRowInGroup.getElementsByTagName("td")[0];
      let cellToRowspanMarque = firstRowInGroup.getElementsByTagName("td")[1];
      let cellToRowspanModele = firstRowInGroup.getElementsByTagName("td")[2];
      let cellToRowspanIdMat = firstRowInGroup.getElementsByTagName("td")[3];
      let cellToRowspanSérie = firstRowInGroup.getElementsByTagName("td")[4];
      let cellToRowspanParc = firstRowInGroup.getElementsByTagName("td")[5];
      let cellToRowspanCasier = firstRowInGroup.getElementsByTagName("td")[6];
      let cellToRowspanIntitule = firstRowInGroup.getElementsByTagName("td")[7];
      let cellToRowspanORitv = firstRowInGroup.getElementsByTagName("td")[8];
      let cellToRowspanSdatepla = firstRowInGroup.getElementsByTagName("td")[9];

      cellToRowspanAgenceServ.rowSpan = rowSpanCount;
      cellToRowspanMarque.rowSpan = rowSpanCount;
      cellToRowspanModele.rowSpan = rowSpanCount;
      cellToRowspanIdMat.rowSpan = rowSpanCount;
      cellToRowspanSérie.rowSpan = rowSpanCount;
      cellToRowspanParc.rowSpan = rowSpanCount;
      cellToRowspanCasier.rowSpan = rowSpanCount;
      cellToRowspanIntitule.rowSpan = rowSpanCount;
      cellToRowspanORitv.rowSpan = rowSpanCount;
      cellToRowspanSdatepla.rowSpan = rowSpanCount;

      cellToRowspanAgenceServ.classList.add("rowspan-cell");
      cellToRowspanMarque.classList.add("rowspan-cell");
      cellToRowspanModele.classList.add("rowspan-cell");
      cellToRowspanIdMat.classList.add("rowspan-cell");
      cellToRowspanSérie.classList.add("rowspan-cell");
      cellToRowspanParc.classList.add("rowspan-cell");
      cellToRowspanCasier.classList.add("rowspan-cell");
      cellToRowspanIntitule.classList.add("rowspan-cell");
      cellToRowspanORitv.classList.add("rowspan-cell");
      cellToRowspanSdatepla.classList.add("rowspan-cell");
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
      currentRow.getElementsByTagName("td")[0].style.display = "none";
      currentRow.getElementsByTagName("td")[1].style.display = "none";
      currentRow.getElementsByTagName("td")[2].style.display = "none";
      currentRow.getElementsByTagName("td")[3].style.display = "none";
      currentRow.getElementsByTagName("td")[4].style.display = "none";
      currentRow.getElementsByTagName("td")[5].style.display = "none";
      currentRow.getElementsByTagName("td")[6].style.display = "none";
      currentRow.getElementsByTagName("td")[7].style.display = "none";
      currentRow.getElementsByTagName("td")[8].style.display = "none";
      currentRow.getElementsByTagName("td")[9].style.display = "none";
    }
  }

  previousOrNumber = currentOrNumber;
}

// Appliquer le rowspan à la dernière série de lignes
if (firstRowInGroup) {
  let cellToRowspanAgenceServ = firstRowInGroup.getElementsByTagName("td")[0];
  let cellToRowspanMarque = firstRowInGroup.getElementsByTagName("td")[1];
  let cellToRowspanModele = firstRowInGroup.getElementsByTagName("td")[2];
  let cellToRowspanIdMat = firstRowInGroup.getElementsByTagName("td")[3];
  let cellToRowspanSérie = firstRowInGroup.getElementsByTagName("td")[4];
  let cellToRowspanParc = firstRowInGroup.getElementsByTagName("td")[5];
  let cellToRowspanCasier = firstRowInGroup.getElementsByTagName("td")[6];
  let cellToRowspanIntitule = firstRowInGroup.getElementsByTagName("td")[7];
  let cellToRowspanORitv = firstRowInGroup.getElementsByTagName("td")[8];
  let cellToRowspanSdatepla = firstRowInGroup.getElementsByTagName("td")[9];

  cellToRowspanAgenceServ.rowSpan = rowSpanCount;
  cellToRowspanMarque.rowSpan = rowSpanCount;
  cellToRowspanModele.rowSpan = rowSpanCount;
  cellToRowspanIdMat.rowSpan = rowSpanCount;
  cellToRowspanSérie.rowSpan = rowSpanCount;
  cellToRowspanParc.rowSpan = rowSpanCount;
  cellToRowspanCasier.rowSpan = rowSpanCount;
  cellToRowspanIntitule.rowSpan = rowSpanCount;
  cellToRowspanORitv.rowSpan = rowSpanCount;
  cellToRowspanSdatepla.rowSpan = rowSpanCount;

  cellToRowspanAgenceServ.classList.add("rowspan-cell");
  cellToRowspanMarque.classList.add("rowspan-cell");
  cellToRowspanModele.classList.add("rowspan-cell");
  cellToRowspanIdMat.classList.add("rowspan-cell");
  cellToRowspanSérie.classList.add("rowspan-cell");
  cellToRowspanParc.classList.add("rowspan-cell");
  cellToRowspanCasier.classList.add("rowspan-cell");
  cellToRowspanIntitule.classList.add("rowspan-cell");
  cellToRowspanORitv.classList.add("rowspan-cell");
  cellToRowspanSdatepla.classList.add("rowspan-cell");
}
