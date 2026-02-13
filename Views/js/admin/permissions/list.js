import { mergeCellsRecursiveTable } from "../../utils/tableHandler";

document.addEventListener("DOMContentLoaded", function () {
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0, 1, 2, 3, 4, 5], insertSeparator: true },
    { pivotIndex: 6, columns: [6, 7], insertSeparator: true },
  ]);
});
