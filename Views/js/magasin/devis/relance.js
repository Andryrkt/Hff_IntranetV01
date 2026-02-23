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

    const action = isNowChecked ? "arrêter" : "réactiver";

    Swal.fire({
      title: "Confirmation",
      text:
        "Voulez-vous vraiment " +
        action +
        " la relance pour le devis " +
        numeroDevis +
        " ?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Oui, valider",
      cancelButtonText: "Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        const overlay = document.getElementById("loading-overlays");
        if (overlay) overlay.classList.add("active");

        const endpoint = "api/stop-relance/" + numeroDevis;

        fetchManager
          .post(endpoint, {})
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
                timer: 3000,
                showConfirmButton: false,
              });
            } else {
              // Revert state on failure
              checkbox.checked = !isNowChecked;
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
            console.error("Error:", error);
            Swal.fire({
              title: "Erreur",
              text: "Une erreur est survenue lors de la communication avec le serveur.",
              icon: "error",
            });
          });
      } else {
        // Revert state if cancelled
        checkbox.checked = !isNowChecked;
      }
    });
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
