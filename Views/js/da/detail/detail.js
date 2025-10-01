import { formaterNombre } from "../../utils/formatNumberUtils";
import { displayOverlay } from "../../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
  const allMontantTd = document.querySelectorAll("td.format-mtt");
  allMontantTd.forEach((mtt) => {
    mtt.innerText = formaterNombre(mtt.innerText);
  });

  // Toggle individuel
  document.querySelectorAll(".toggle-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const icon = this.querySelector("i");
      const parentRow = this.closest("tr");
      let nextRow = parentRow.nextElementSibling;
      let delay = 0;
      let show = true;

      // déterminer si on ouvre ou ferme
      if (nextRow && nextRow.style.display !== "none") {
        show = false;
      }

      while (nextRow && nextRow.classList.contains("child-row")) {
        toggleChildRow(nextRow, show, delay);
        delay += 50; // délai en ms pour effet cascade
        nextRow = nextRow.nextElementSibling;
      }

      // Change l’icône
      icon.classList.toggle("fa-chevron-down", !show);
      icon.classList.toggle("fa-chevron-up", show);
    });
  });

  // Toggle global "tout ouvrir / tout fermer"
  const toggleAllBtn = document.querySelector("#toggle-all");
  let allVisible = true;

  toggleAllBtn.addEventListener("click", function () {
    const childRows = Array.from(document.querySelectorAll(".child-row"));
    const icons = document.querySelectorAll(".toggle-btn i");
    let delay = 0;

    childRows.forEach((row) => {
      toggleChildRow(row, !allVisible, delay);
      delay += 50; // cascade globale
    });

    // Met à jour toutes les icônes des boutons individuels
    icons.forEach((icon) => {
      icon.classList.toggle("fa-chevron-down", allVisible);
      icon.classList.toggle("fa-chevron-up", !allVisible);
    });

    // Met à jour l’état global
    allVisible = !allVisible;

    // Tooltip Bootstrap
    this.dataset.bsOriginalTitle = allVisible ? "Tout fermer" : "Tout ouvrir";
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

// Fonction pour toggle une ligne avec animation
function toggleChildRow(row, show, delay = 0) {
  setTimeout(() => {
    if (show) {
      row.classList.remove("hide");
      row.classList.add("show");
      row.style.display = "table-row";
    } else {
      row.classList.remove("show");
      row.classList.add("hide");
      setTimeout(() => {
        row.style.display = "none";
      }, 300); // durée de l'animation
    }
  }, delay);
}
