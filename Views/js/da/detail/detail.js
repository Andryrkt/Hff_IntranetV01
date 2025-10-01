import { baseUrl } from "../../utils/config";
import { formaterNombre } from "../../utils/formatNumberUtils";
import { displayOverlay } from "../../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
  const allMontantTd = document.querySelectorAll("td.format-mtt");
  allMontantTd.forEach((mtt) => {
    mtt.innerText = formaterNombre(mtt.innerText);
  });

  /** Toggle button pour le + et - */
  document.querySelectorAll(".toggle-btn").forEach(function (button) {
    button.addEventListener("click", function () {
      const icon = this.querySelector("i");
      const parentRow = this.closest("tr");
      let nextRow = parentRow.nextElementSibling;

      // Toggle les lignes enfants jusqu'à ce qu'on tombe sur une nouvelle ligne parente
      while (nextRow && nextRow.classList.contains("child-row")) {
        nextRow.style.display =
          nextRow.style.display === "none" ? "table-row" : "none";
        nextRow = nextRow.nextElementSibling;
      }

      // Change le bouton de + à - et inversement
      icon.classList.toggle("fa-chevron-down");
      icon.classList.toggle("fa-chevron-up");
    });
  });

  // Bouton global "tout ouvrir / tout fermer"
  const toggleAllBtn = document.querySelector("#toggle-all");
  let allVisible = true; // état global

  toggleAllBtn.addEventListener("click", function () {
    const childRows = document.querySelectorAll(".child-row");
    const icons = document.querySelectorAll(".toggle-btn i");

    childRows.forEach(function (row) {
      row.style.display = allVisible ? "none" : "table-row";
    });

    // Met à jour toutes les icônes des boutons individuels
    icons.forEach(function (icon) {
      icon.classList.toggle("fa-chevron-down", allVisible);
      icon.classList.toggle("fa-chevron-up", !allVisible);
    });

    // Met à jour l’état
    allVisible = !allVisible;

    // Met à jour le tooltip Bootstrap
    this.dataset.bsOriginalTitle = allVisible ? "Tout fermer" : "Tout ouvrir";

    // Si le tooltip est déjà initialisé, on le rafraîchit
    const tooltipInstance = bootstrap.Tooltip.getInstance(this);
    if (tooltipInstance) {
      tooltipInstance.setContent({
        ".tooltip-inner": this.dataset.bsOriginalTitle,
      });
    }
  });

  /**
   * Suppression de ligne de DA
   */
  const deleteLineBtns = document.querySelectorAll(".delete-line-DA");
  deleteLineBtns.forEach((deleteLineBtn) => {
    deleteLineBtn.addEventListener("click", function () {
      deleteLigneDa(this);
    });
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

function deleteLigneDa(button) {
  let deletePath = button.dataset.deletePath;
  Swal.fire({
    title: "Êtes-vous sûr(e) ?",
    html: `Voulez-vous vraiment supprimer cette ligne de demande d’achat?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Non, annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      displayOverlay(true);
      window.location = deletePath;
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      // ❌ Si l'utilisateur annule
      Swal.fire({
        icon: "info",
        title: "Annulé",
        text: "La suppression de la ligne de demande a été annulée.",
        timer: 2000,
        showConfirmButton: false,
      });
    }
  });
}
