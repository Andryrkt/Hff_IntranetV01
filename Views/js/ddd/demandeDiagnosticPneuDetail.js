import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

document.addEventListener("DOMContentLoaded", function () {
  const buttonEnregistrer = document.getElementById(
    "bouton-enregistrer-diagnostic-pneu",
  );

  if (!buttonEnregistrer) {
    return;
  }

  const form = buttonEnregistrer.closest("form");

  if (!form) {
    return;
  }

  buttonEnregistrer.addEventListener("click", function (e) {
    e.preventDefault();

    Swal.fire({
      title: "Confirmation",
      text: "Veuillez vérifier attentivement toutes les informations saisies avant de confirmer l'enregistrement de ces diagnostics.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Oui, enregistrer",
      cancelButtonText: "Annuler",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, "Enregistrement du diagnostic en cours...");

        form.submit();
      }
    });
  });
});

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
