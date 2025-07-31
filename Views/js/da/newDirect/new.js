import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterUneLigne } from "./dal";

document.addEventListener("DOMContentLoaded", function () {
  localStorage.setItem("indexDirect", 0); // initialiser le nombre de ligne à 0

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault();

    if (document.getElementById("children-container").childElementCount > 0) {
      document.getElementById("child-prototype").remove();
      document.getElementById("myForm").submit();
    } else {
      Swal.fire({
        icon: "warning",
        title: "Attention !",
        text: "Veuillez ajouter au moins un article avant d'enregistrer.",
      });
    }
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
