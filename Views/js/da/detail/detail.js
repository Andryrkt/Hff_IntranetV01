import { formaterNombre } from "../../utils/formatNumberUtils";
import { displayOverlay } from "../../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
  const allMontantTd = document.querySelectorAll("td.format-mtt");
  allMontantTd.forEach((mtt) => {
    mtt.innerText = formaterNombre(mtt.innerText);
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
