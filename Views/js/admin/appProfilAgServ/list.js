import { mergeCellsRecursiveTable } from "../../utils/tableHandler";

document.addEventListener("DOMContentLoaded", function () {
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0, 1, 2, 3, 4], insertSeparator: true },
    { pivotIndex: 5, columns: [5, 6], insertSeparator: true },
  ]);
});
