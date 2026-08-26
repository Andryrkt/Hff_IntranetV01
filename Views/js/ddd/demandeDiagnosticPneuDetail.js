import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

document.addEventListener("DOMContentLoaded", function () {
  const actionsConfig = {
    enregistrer: {
      config: {
        title: "Confirmation",
        text: "Veuillez vérifier attentivement toutes les informations saisies avant de confirmer l'enregistrement de ces diagnostics.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, enregistrer",
        cancelButtonText: "Annuler",
        reverseButtons: true,
      },
      loaderMessage: "Enregistrement du diagnostic en cours...",
    },
    valider: {
      config: {
        title: "Confirmation de validation",
        text: "Tous les diagnostics sont renseignés. Valider et envoyer l'email de notification ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, valider",
        cancelButtonText: "Annuler",
        reverseButtons: true,
      },
      loaderMessage: "Validation du diagnostic en cours...",
    },
    cloture: {
      config: {
        title: "Confirmation",
        text: "Pour clôturer cette demande, vous allez maintenant être redirigé vers la demande d'intervention.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, clôturer",
        cancelButtonText: "Annuler",
      },
      loaderMessage: "Clôture de la demande en cours...",
    },
  };

  const diagnosticForm = document.getElementById("diagnosticForm");
  diagnosticForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const action = e.submitter.name;

    const actionConfig = actionsConfig[action];

    if (!actionConfig) {
      return;
    }

    Swal.fire(actionConfig.config).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, actionConfig.loaderMessage);

        // ajouter un champ caché avec l’action choisie
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "action";
        hidden.value = action;
        diagnosticForm.appendChild(hidden);

        diagnosticForm.submit();
      }
    });
  });

  const buttonCloture = document.getElementById(
    "bouton-cloturer-diagnostic-pneu",
  );

  if (!buttonCloture) {
    return;
  }

  buttonCloture.addEventListener("click", function (e) {
    e.preventDefault();
    const actionConfig = actionsConfig["cloture"];
    if (!actionConfig) {
      return;
    }
    const url = this.href;

    Swal.fire(actionConfig.config).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, actionConfig.loaderMessage);

        window.location.href = url;
      }
    });
  });
});
