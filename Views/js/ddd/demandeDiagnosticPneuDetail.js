import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

// Popup ou Modal de confirmation Demande Diag. Pneu  avec chargement

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("diagnostic-pneu-form");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    confirmation(form);
  });
});

function confirmation(form) {
  const submitBtn =
    document.getElementById("bouton-diagnostic-pneu") ||
    form.querySelector('button[type="submit"]');

  // Let the browser display HTML5 validation errors
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  Swal.fire({
    title: "Confirmation",
    text: "Voulez-vous vraiment créer cette demande de diagnostic ?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Oui",
    cancelButtonText: "Annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      if (submitBtn) {
        displayOverlay(true, "Création de la demande en cours...");
        submitBtn.disabled = true;
      }

      form.submit();
    }
  });
}
