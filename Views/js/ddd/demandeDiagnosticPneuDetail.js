import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

// Popup ou Modal de confirmation Demande Diag. Pneu  avec chargement

document.addEventListener("DOMContentLoaded", function () {
  const buttonCloture = document.getElementById(
    "bouton-cloturer-diagnostic-pneu",
  );

  if (!buttonCloture) {
    return;
  }

  buttonCloture.addEventListener("click", function (e) {
    e.preventDefault();

    const url = this.href;

    Swal.fire({
      title: "Confirmation",
      text: "Pour clôturer cette demande, vous allez maintenant être redirigé vers la demande d'intervention.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Oui, clôturer",
      cancelButtonText: "Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, "Clôture de la demande en cours...");

        window.location.href = url;
      }
    });
  });
});
