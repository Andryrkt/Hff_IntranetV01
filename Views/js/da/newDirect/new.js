import { displayOverlay } from "../../utils/ui/overlay";
import { ajouterUneLigne } from "./dal";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";

document.addEventListener("DOMContentLoaded", function () {
  localStorage.setItem("daDirectLineCounter", 0); // initialiser le nombre de ligne à 0

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

/**===========================================================================
 * Configuration des agences et services
 *============================================================================*/

// Attachement des événements pour les agences
document
  .getElementById("demande_appro_direct_form_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
