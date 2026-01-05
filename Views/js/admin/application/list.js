import { mergeCellsRecursiveTable } from "../../utils/tableHandler";

document.addEventListener("DOMContentLoaded", function () {
  mergeCellsRecursiveTable([
    { pivotIndex: 1, columns: [0, 1, 2, 4, 5], insertSeparator: true },
    { pivotIndex: 3, columns: [3], insertSeparator: false },
    { pivotIndex: 6, columns: [6], insertSeparator: false },
  ]);
});
