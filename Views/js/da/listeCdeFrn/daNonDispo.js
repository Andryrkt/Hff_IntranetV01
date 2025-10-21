document.addEventListener("DOMContentLoaded", function () {
  let lastCheckedDaId = "";
  const tableBody = document.querySelector("#tableBody"); // sélecteur pour le tBody
  const checkboxes = tableBody.querySelectorAll(".modern-checkbox"); // tous les checkbox

  // Event delegation
  tableBody.addEventListener("click", (event) => {
    const target = event.target;

    // ✅ Si clic sur un td cliquable → on coche le checkbox de la ligne
    if (target.matches("td.clickable-td")) {
      const row = target.closest("tr");
      const checkbox = row.querySelector(".modern-checkbox");
      if (checkbox) checkbox.click(); // délégué au handler de "change"
      return;
    }
  });

  // ✅ Gestion du changement d’état d’un checkbox
  tableBody.addEventListener("change", (event) => {
    const checkbox = event.target;
    if (!checkbox.classList.contains("modern-checkbox")) return;

    const daId = checkbox.dataset.daDemandeApproId;
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);

    if (!lastCheckedDaId || daId === lastCheckedDaId) {
      updateRowState(checkbox, checkbox.checked);
      lastCheckedDaId = checkbox.checked ? daId : "";
    } else {
      confirmCheck(checkbox, daId, checkedBoxes);
    }
  });

  function updateRowState(checkbox, isChecked) {
    const cell = checkbox.closest("td");
    if (!cell) return;

    // Appliquer td-active uniquement à la cellule du checkbox et aux suivantes
    let currentCell = cell;
    while (currentCell) {
      currentCell.classList.toggle("td-active", isChecked);
      currentCell = currentCell.nextElementSibling;
    }
  }

  function resetAllChecks(checkboxes) {
    checkboxes.forEach((cb) => {
      cb.checked = false;
      updateRowState(cb, false);
    });
  }

  function confirmCheck(checkbox, daId, checkedBoxes) {
    Swal.fire({
      title: "Êtes-vous sûr(e) ?",
      html: `Vous ne pouvez sélectionner que des lignes appartenant à la même DA.<br>
      Si vous voulez quand même sélectionner ces lignes, cliquez sur <b class="text-success">"Continuer"</b> (les lignes précédemment cochées seront décochées).
      Sinon, cliquez sur <b class="text-secondary">"Annuler"</b>.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Oui, Continuer",
      cancelButtonText: "Non, Annuler",
      customClass: {
        htmlContainer: "swal-text-left",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        resetAllChecks(checkedBoxes);
        checkbox.checked = true;
        updateRowState(checkbox, true);
        lastCheckedDaId = daId;
      } else {
        checkbox.checked = false;
        updateRowState(checkbox, false);
      }
    });
  }
});
