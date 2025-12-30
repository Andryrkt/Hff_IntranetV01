/**
 * Fonction pour fusionner les cellules d'un tableau
 *
 * @export
 *
 * @param {Array} group nom du groupe
 * @param {int} cellPivotIndex index de la cellule pivot pour différencier les groupes de lignes
 */
export function mergeCellsTable(group, cellPivotIndex) {
  const tableBody = document.querySelector("#tableBody"); // obtenir tBody du tableau à l'aide de l'id "tableBody"
  const rows = Array.from(tableBody.getElementsByTagName("tr")); // obtenir tous les lignes de ce tableau

  let rowSpanCount = 0; // initialiser à 0 le row span
  let previousDap = null; // initialiser à null la valeur précédente de Dap
  let firstRowInGroup = null; // initialiser à null la première ligne dans le groupe

  rows.forEach((currentRow, index) => {
    let cells = currentRow.getElementsByTagName("td");
    let currentDap = cells[cellPivotIndex]?.textContent.trim() || null;

    if (previousDap === null || previousDap !== currentDap) {
      // Si on change de groupe, appliquer rowSpan sur l'ancien groupe
      if (firstRowInGroup) applyRowSpan(firstRowInGroup, rowSpanCount);

      // Insérer un séparateur sauf pour la première ligne
      if (previousDap !== null) insertSeparator(currentRow);

      // Réinitialisation pour le nouveau groupe
      firstRowInGroup = currentRow;
      rowSpanCount = 1;
    } else {
      rowSpanCount++;
      hideCells(currentRow);
    }

    previousDap = currentDap;

    // Appliquer le rowspan au dernier groupe
    if (index === rows.length - 1) applyRowSpan(firstRowInGroup, rowSpanCount);
  });
  insertSeparator();

  // Fonction pour appliquer le rowspan sur la première ligne du groupe
  function applyRowSpan(row, count) {
    group.forEach((i) => {
      let cell = row.getElementsByTagName("td")[i];
      if (cell) {
        cell.rowSpan = count;
        cell.classList.add("rowspan-cell");
      }
    });
  }

  // Fonction pour masquer les cellules des lignes suivantes du groupe
  function hideCells(row) {
    group.forEach((i) => {
      let cell = row.getElementsByTagName("td")[i];
      if (cell) cell.style.display = "none";
    });
  }

  // Fonction pour insérer une ligne séparatrice
  function insertSeparator(referenceRow = null) {
    if (!rows || rows.length === 0) return;
    let separatorRow = document.createElement("tr");
    separatorRow.classList.add("separator-row");

    let td = document.createElement("td");
    td.colSpan = referenceRow
      ? referenceRow.cells.length
      : rows[0].cells.length;
    td.classList.add("p-0");

    separatorRow.appendChild(td);
    if (referenceRow !== null) {
      tableBody.insertBefore(separatorRow, referenceRow);
    } else {
      tableBody.append(separatorRow);
    }
  }
}
