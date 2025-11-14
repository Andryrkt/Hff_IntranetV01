import {
  hideCells,
  applyRowspanAndClass,
  addSeparatorRow,
} from "./utils/uiUtils.js";
import { fetchNumMatMarqueCasier } from "./utils/apiUtils.js";

export function groupRows(rows, tableBody, cellIndices, addInfo = true) {
  let previousValues = Object.keys(cellIndices).reduce((acc, key) => {
    acc[key] = null;
    return acc;
  }, {});

  let rowSpanCount = 0;
  let firstRowInGroup = null;

  rows.forEach((currentRow) => {
    const cells = Array.from(currentRow.getElementsByTagName("td"));

    // Récupérer les valeurs actuelles basées sur cellIndices
    const currentValues = Object.keys(cellIndices).reduce((acc, key) => {
      acc[key] = cells[cellIndices[key]]?.textContent.trim() || "";
      return acc;
    }, {});

    // Vérifier si un changement de groupe est détecté
    const hasGroupChanged = Object.keys(currentValues).some(
      (key) => previousValues[key] !== currentValues[key]
    );

    if (!previousValues.orNumber) {
      // Initialisation pour la première ligne
      firstRowInGroup = currentRow;
      rowSpanCount = 1;
    } else if (hasGroupChanged) {
      // Appliquer rowspan et ajouter un séparateur
      if (firstRowInGroup) {
        applyRowspanAndClass(
          firstRowInGroup,
          rowSpanCount,
          cellIndices,
          fetchNumMatMarqueCasier,
          addInfo
        );
      }
      addSeparatorRow(tableBody, currentRow);
      rowSpanCount = 1;
      firstRowInGroup = currentRow;
    } else {
      // Masquer les cellules en doublon et augmenter rowSpan
      rowSpanCount++;
      hideCells(currentRow, Object.values(cellIndices));
    }

    // Mettre à jour previousValues
    previousValues = currentValues;

    // console.log(
    //   "Fusion de lignes pour :",
    //   previousValues,
    //   "avec",
    //   rowSpanCount,
    //   "lignes"
    // );
  });

  // Appliquer rowspan au dernier groupe
  if (firstRowInGroup) {
    applyRowspanAndClass(
      firstRowInGroup,
      rowSpanCount,
      cellIndices,
      fetchNumMatMarqueCasier,
      addInfo
    );
  }
}
