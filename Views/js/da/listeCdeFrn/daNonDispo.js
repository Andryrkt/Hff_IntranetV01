document.addEventListener("DOMContentLoaded", function () {
  let lastCheckedDaId = 0;
  const checkboxes = document.querySelectorAll(".modern-checkbox");

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      // Tous les checkbox cochés
      const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);
      const daId = checkbox.dataset.daDemandeApproId;

      console.log(lastCheckedDaId);

      if (lastCheckedDaId === 0 || daId === lastCheckedDaId) {
        updateRowState(checkbox, checkbox.checked);
        lastCheckedDaId = checkbox.checked ? daId : 0;
      } else {
        confirmCheck(checkbox, daId, checkedBoxes);
      }
    });
  });

  function updateRowState(checkbox, isChecked) {
    let cell = checkbox.closest("td");
    while (cell) {
      cell.classList.toggle("td-active", isChecked);
      cell = cell.nextElementSibling;
    }
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
        checkedBoxes.forEach((c) => {
          c.checked = false;
          updateRowState(c, false);
        });
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
