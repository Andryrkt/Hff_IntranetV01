import { mergeCellsRecursiveTable } from "../listeCdeFrn/tableHandler";

/** =============================================================
 * fusion des lignes
 *==============================================================*/
document.addEventListener("DOMContentLoaded", function () {
  /*  1ᵉʳ appel : colonnes 0-3 selon le pivot que vous aviez déjà.
   *  2ᵉ appel : colonnes 4-5 selon la colonne 4.
   */
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0], insertSeparator: true },
    { pivotIndex: 1, columns: [1], insertSeparator: true },
    { pivotIndex: 2, columns: [2], insertSeparator: true },
    { pivotIndex: 3, columns: [3], insertSeparator: true },
  ]);
});
