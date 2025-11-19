import { displayOverlay } from "../../utils/ui/overlay";
import { ajouterUneLigne } from "./dal";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";

document.addEventListener("DOMContentLoaded", function () {
  buildIndexFromLines(); // initialiser le compteur de ligne pour la création d'une DA directe

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault();

    if (document.getElementById("children-container").childElementCount > 0) {
      Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        html: `Voulez-vous vraiment envoyer la demande?`,
        icon: "warning",
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Oui, Envoyer",
        cancelButtonText: "Non, annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          document.getElementById("child-prototype").remove();
          document.getElementById("myForm").submit();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulé",
            text: "Votre demande n'a pas été envoyée.",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      });
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

function getMaxIndexFromIds() {
  const elements = document.querySelectorAll(
    "div[id^='demande_appro_direct_form_DAL_'].DAL-container"
  );
  return Array.from(elements).reduce((max, el) => {
    const match = el.id.match(/^demande_appro_direct_form_DAL_(\d+)$/);
    if (match) {
      const value = parseInt(match[1], 10);
      return !isNaN(value) && value > max ? value : max;
    }
    return max;
  }, 0);
}

function getMaxLineFromValues() {
  const elements = document.querySelectorAll(
    "[id^='demande_appro_direct_form_DAL_'][id$='_numeroLigne']"
  );
  return Array.from(elements).reduce((max, el) => {
    const value = parseInt(el.value, 10);
    if (isNaN(value)) {
      console.warn("Valeur non numérique trouvée pour numeroLigne:", el.value);
      return max; // ignore les valeurs invalides
    }
    return value > max ? value : max;
  }, 0);
}

function buildIndexFromLines() {
  const maxIndex = getMaxIndexFromIds();
  const maxLine = getMaxLineFromValues();

  // Log et stockage des résultats dans localStorage
  console.log("Numéro de ligne Max:", maxLine);
  localStorage.setItem("daDirectNumLigneMax", maxLine);

  console.log("Max index:", maxIndex);
  localStorage.setItem("daDirectLineCounter", maxIndex);
}

/**===========================================================================
 * Configuration des agences et services
 *============================================================================*/

// Attachement des événements pour les agences
document
  .getElementById("demande_appro_direct_form_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
