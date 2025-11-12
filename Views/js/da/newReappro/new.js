import { displayOverlay } from "../../utils/ui/overlay";
import { handleQteInputEvents } from "./event";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";

document.addEventListener("DOMContentLoaded", function () {
  const myForm = document.getElementById("myForm");
  const actionsConfig = {
    enregistrerBrouillon: {
      title: "Confirmer l’enregistrement",
      html: `Souhaitez-vous enregistrer <strong class="text-primary">provisoirement</strong> cette demande ?<br><small class="text-primary"><strong><u>NB</u>: </strong>Elle ne sera pas transmise au service APPRO.</small>`,
      icon: "question",
      confirmButtonText: "Oui, Enregistrer",
      canceledText: "L’enregistrement provisoire a été annulé.",
    },
    soumissionAppro: {
      title: "Confirmer la soumission",
      html: `Êtes-vous sûr de vouloir <strong style="color: #f8bb86;">soumettre</strong> cette demande ?<br><small style="color: #f8bb86;"><strong><u>NB</u>: </strong>Elle sera transmise au service APPRO pour traitement.</small>`,
      icon: "warning",
      confirmButtonText: "Oui, Soumettre",
      canceledText: "La soumission de la demande a été annulée.",
    },
  };
  const allQteInputs = document.querySelectorAll(`[id*="_qteDem"]`);
  handleQteInputEvents(allQteInputs);

  myForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const tousVides = Array.from(allQteInputs).every(
      (input) => input.value === ""
    );

    if (tousVides) {
      Swal.fire({
        icon: "warning",
        title: "Attention !",
        text: "Veuillez saisir au moins une quantité avant d'enregistrer.",
      });
    } else {
      const action = e.submitter.name; // 👉 nom (attribut "name") du bouton qui a déclenché le submit
      const config = actionsConfig[action];
      if (!config) return;

      Swal.fire({
        title: config.title,
        html: config.html,
        icon: config.icon,
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: "Non, Annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);

          // ajouter un champ caché avec l’action choisie
          const hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = action;
          hidden.value = "1";
          myForm.appendChild(hidden);

          myForm.submit(); // n’émule pas le clic sur le bouton d’envoi → donc le name et value du bouton cliqué ne sont pas envoyés.
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulé",
            text: config.canceledText,
            timer: 2000,
            showConfirmButton: false,
          });
        }
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
  .getElementById("demande_appro_reappro_form_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
