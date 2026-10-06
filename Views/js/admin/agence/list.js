import { mergeCellsTable } from "../../utils/tableHandler";

document.addEventListener("DOMContentLoaded", function () {
  mergeCellsTable("#tableBody", [0, 1, 2, 3, 4], 1);
});
