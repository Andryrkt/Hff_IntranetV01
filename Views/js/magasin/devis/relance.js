import { FetchManager } from "../../api/FetchManager.js";
document.addEventListener("DOMContentLoaded", function () {
  const checkboxesStopRelance = document.querySelectorAll(
    ".js-checkbox-stop-relance",
  );
  const fetchManager = new FetchManager();

  checkboxesStopRelance.forEach((checkbox) => {
    checkbox.addEventListener("change", stopOuRelance);
  });

  function stopOuRelance(event) {
    const checkbox = event.currentTarget;
    const numeroDevis = checkbox.dataset.numeroDevis;
    const isNowChecked = checkbox.checked;

    if (isNowChecked) {
      // Cas de l'arrêt : on demande le motif via une modal
      const overlay = document.getElementById("loading-overlays");
      if (overlay) overlay.classList.add("active");

      fetchManager
        .get("api/devis/motif-stop-form")
        .then((response) => {
          if (overlay) overlay.classList.remove("active");
          if (response.html) {
            Swal.fire({
              title: "Motif d'arrêt - Devis " + numeroDevis,
              html: response.html,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Valider l'arrêt",
              cancelButtonText: "Annuler",
              preConfirm: () => {
                const form = document.getElementById("form-motif-stop-relance");
                const selectedMotifInput = form.querySelector(
                  'input[name*="[choixMotif]"]:checked',
                );

                if (!selectedMotifInput) {
                  Swal.showValidationMessage("Veuillez sélectionner un motif");
                  return false;
                }

                // Récupérer le label text du motif sélectionné
                const label = selectedMotifInput
                  .closest(".form-check")
                  .querySelector("label").textContent;
                return { motif: label.trim() };
              },
            }).then((result) => {
              if (result.isConfirmed) {
                console.log(result);

                performStopRelance(
                  checkbox,
                  numeroDevis,
                  true,
                  result.value.motif,
                );
              } else {
                checkbox.checked = false;
                updateCheckbox(checkbox, false);
              }
            });
          }
        })
        .catch((error) => {
          if (overlay) overlay.classList.remove("active");
          checkbox.checked = false;
          console.error("Error:", error);
          Swal.fire({
            title: "Erreur",
            text: "Impossible de charger le formulaire de motif.",
            icon: "error",
          });
        });
    } else {
      // Cas de la réactivation : simple confirmation
      Swal.fire({
        title: "Confirmation",
        text:
          "Voulez-vous vraiment réactiver la relance pour le devis " +
          numeroDevis +
          " ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, réactiver",
        cancelButtonText: "Annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          performStopRelance(checkbox, numeroDevis, false);
        } else {
          checkbox.checked = true;
          updateCheckbox(checkbox, true);
        }
      });
    }
  }

  function performStopRelance(
    checkbox,
    numeroDevis,
    isNowChecked,
    motif = null,
  ) {
    const overlay = document.getElementById("loading-overlays");
    if (overlay) overlay.classList.add("active");

    updateCheckbox(checkbox, isNowChecked);

    const endpoint = "api/stop-relance/" + numeroDevis;
    const body = motif ? { motif: motif } : {};

    fetchManager
      .post(endpoint, body)
      .then((data) => {
        if (overlay) overlay.classList.remove("active");
        if (data.success) {
          if (data.statuts) {
            const row = checkbox.closest("tr");
            updateRelanceColumns(row, data.statuts, data.relanceClient);
          }

          Swal.fire({
            title: "Succès !",
            text:
              "Relance " +
              (isNowChecked ? "arrêtée" : "réactivée") +
              " avec succès.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          });
        } else {
          // Revert state on failure
          checkbox.checked = !isNowChecked;
          updateCheckbox(checkbox, !isNowChecked);
          Swal.fire({
            title: "Erreur",
            text:
              "Erreur lors de l'opération : " +
              (data.message || "Erreur inconnue"),
            icon: "error",
          });
        }
      })
      .catch((error) => {
        if (overlay) overlay.classList.remove("active");
        // Revert state on error
        checkbox.checked = !isNowChecked;
        updateCheckbox(checkbox, !isNowChecked);
        console.error("Error:", error);
        Swal.fire({
          title: "Erreur",
          text: "Une erreur est survenue lors de la communication avec le serveur.",
          icon: "error",
        });
      });
  }

  function updateCheckbox(element, isNowChecked) {
    if (!element) return;

    if (isNowChecked) {
      element.classList.remove("bg-secondary-subtle");
      element.classList.add("bg-danger", "border", "border-danger");
    } else {
      element.classList.remove("bg-danger", "border", "border-danger");
      element.classList.add("bg-secondary-subtle");
    }
  }

  function updateRelanceColumns(row, statuts, relanceClient) {
    const relance1 = row.querySelector(".js-relance-1");
    const relance2 = row.querySelector(".js-relance-2");
    const relance3 = row.querySelector(".js-relance-3");
    const checkbox = row.querySelector(".js-checkbox-stop-relance");
    const relanceLink = row.querySelector(".js-link-relance-client");

    updateColumn(relance1, statuts.statut_relance_1);
    updateColumn(relance2, statuts.statut_relance_2);
    updateColumn(relance3, statuts.statut_relance_3);

    if (checkbox) {
      const isRelance3Done =
        statuts.statut_relance_3 && statuts.statut_relance_3 !== "A relancer";
      checkbox.disabled = !!isRelance3Done;
    }

    if (relanceLink) {
      if (relanceClient) {
        relanceLink.classList.remove("d-none");
      } else {
        relanceLink.classList.add("d-none");
      }
    }
  }

  function updateColumn(element, value) {
    if (!element) return;

    element.textContent = value || "";

    // Remove old background classes
    element.classList.remove("bg-warning", "bg-danger", "text-white");

    if (value === "A relancer") {
      element.classList.add("bg-danger", "text-white");
    } else if (value) {
      // It's a date or other status
      element.classList.add("bg-warning");
    }
  }
});
