import { FetchManager } from "../../api/FetchManager.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager();
  var pointageRelanceModal = document.getElementById("pointageRelanceModal");
  var loadingOverlay = document.getElementById("loading-overlays");

  function showLoadingOverlay() {
    if (loadingOverlay) {
      loadingOverlay.style.display = "flex"; // Utilise flex pour centrer le contenu
    }
  }

  function hideLoadingOverlay() {
    if (loadingOverlay) {
      loadingOverlay.style.display = "none";
    }
  }

  if (pointageRelanceModal) {
    pointageRelanceModal.addEventListener("show.bs.modal", function (event) {
      var button = event.relatedTarget;
      var numeroDevis = button.getAttribute("data-bs-numero-devis");

      var modalBody = pointageRelanceModal.querySelector(".modal-body");
      var submitButton = pointageRelanceModal.querySelector(
        'button[form="pointageRelanceForm"][type="submit"]',
      );

      if (submitButton) {
        submitButton.disabled = true;
      }

      modalBody.innerHTML = `
        <div class="d-flex justify-content-center p-5">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Chargement...</span>
          </div>
        </div>
      `;

      const endpoint = `magasin/dematerialisation/pointage-relance-form/${numeroDevis}`;

      fetchManager
        .get(endpoint, "text")
        .then((html) => {
          modalBody.innerHTML = html;
          if (submitButton) {
            submitButton.disabled = false;
          }
        })
        .catch((error) => {
          console.error("Error loading the form:", error);
          modalBody.innerHTML =
            '<p class="text-danger">Erreur lors du chargement du formulaire.</p>';
        });
    });
  }

  // Écouteur pour la soumission du formulaire
  document.body.addEventListener("submit", function (event) {
    if (event.target && event.target.id === "pointageRelanceForm") {
      event.preventDefault();

      var form = event.target;
      var submitButton = pointageRelanceModal.querySelector(
        'button[form="pointageRelanceForm"][type="submit"]',
      );
      var originalButtonText = submitButton
        ? submitButton.textContent
        : "Soumettre";

      if (submitButton) {
        submitButton.dataset.originalText = originalButtonText;
      }

      // Fonction pour basculer l'état du bouton
      function toggleSubmitButton(disable) {
        if (submitButton) {
          submitButton.disabled = disable;
          if (disable) {
            submitButton.textContent = "Soumission en cours...";
          } else {
            submitButton.textContent =
              submitButton.dataset.originalText || "Soumettre";
          }
        }
      }

      var formData = new FormData(form);
      var data = Object.fromEntries(formData.entries());

      // Validation côté client pour dateDeRelance
      const dateDeRelance = data["dateDeRelance"];

      if (!dateDeRelance) {
        Swal.fire({
          icon: "error",
          title: "Erreur de validation",
          text: "La date de relance est obligatoire.",
        });
        return;
      }

      toggleSubmitButton(true);

      var modal = bootstrap.Modal.getInstance(
        document.getElementById("pointageRelanceModal"),
      );

      const submitEndpoint =
        "magasin/dematerialisation/pointage-relance-submit";

      fetchManager
        .post(submitEndpoint, data)
        .then((response) => {
          if (response.success) {
            modal.hide();
            Swal.fire({
              icon: "success",
              title: "Succès",
              text: response.message,
            }).then(() => {
              showLoadingOverlay(); // Affiche l'overlay après confirmation de la popup
              location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Erreur de validation",
              text: response.message,
              footer: response.errors
                ? `<pre style="text-align: left;">${response.errors}</pre>`
                : "",
            });
          }
        })
        .catch((error) => {
          console.error("Error submitting the form:", error);
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Une erreur inattendue est survenue lors de la soumission du formulaire.",
          });
        })
        .finally(() => {
          toggleSubmitButton(false);
        });
    }
  });

  // Réactiver le bouton lorsque le modal est fermé
  if (pointageRelanceModal) {
    pointageRelanceModal.addEventListener("hidden.bs.modal", function () {
      var form = document.getElementById("pointageRelanceForm");
      if (form) {
        var submitButton = pointageRelanceModal.querySelector(
          'button[form="pointageRelanceForm"][type="submit"]',
        );
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent =
            submitButton.dataset.originalText || "Soumettre";
        }
      }
    });
  }
});
