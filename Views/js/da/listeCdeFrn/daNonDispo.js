import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { FetchManager } from "../../api/FetchManager";
import { displayOverlay } from "../../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
  let lastCheckedDaId = "";
  const fetchManager = new FetchManager(); // ton objet FetchManager
  const tableBody = document.querySelector("#tableBody"); // sélecteur pour le tBody
  const checkboxes = tableBody.querySelectorAll(".modern-checkbox"); // tous les checkbox
  const select = document.getElementById("action_non_dispo"); // liste déroulante de choix de redirection

  // On écoute le changement de valeur
  select.addEventListener("change", async function () {
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);

    if (checkedBoxes.length === 0) {
      Swal.fire({
        icon: "error",
        title: "Aucun article sélectionné",
        html: `Vous n'avez sélectionné aucun article. Veuillez choisir au moins un article avant de cliquer sur les choix d'action.`,
        confirmButtonText: "OK",
        customClass: {
          htmlContainer: "swal-text-left",
        },
      });
      this.value = ""; // réinitialisation
      return;
    }

    // Récupérer les IDs sélectionnés
    const selectedIds = checkedBoxes.map((cb) => cb.value);

    try {
      let endpoint = "";

      if (this.value === "delete") endpoint = API_ENDPOINTS.DELETE_ARTICLES_DA;
      if (this.value === "create") endpoint = API_ENDPOINTS.CREATE_ARTICLES_DA;

      // Réinitialiser le select après
      this.value = "";

      console.log(selectedIds);

      displayOverlay(true);

      const result = await fetchManager.post(endpoint, {
        articles: selectedIds,
      });
      console.log("Résultat du serveur :", result);

      displayOverlay(false);

      Swal.fire({
        icon: "success",
        title: "Action effectuée",
        html: result.message,
        customClass: {
          htmlContainer: "swal-text-left",
        },
      }).then(() => {
        console.log("this.value = " + this.value);

        // Seulement si c'est suppression de lignes
        if (endpoint === API_ENDPOINTS.DELETE_ARTICLES_DA) {
          const scrollPosition = window.scrollY;
          // Redirection / reload après confirmation de l'alerte
          displayOverlay(true);
          window.location.reload();
          // puis après le reload
          window.scrollTo(0, scrollPosition);
        }
      });
    } catch (error) {
      displayOverlay(false);
      console.error(error);
      Swal.fire({
        icon: "error",
        title: "Erreur",
        html: "Une erreur est survenue lors de l'envoi des données.",
        customClass: {
          htmlContainer: "swal-text-left",
        },
      });
    }
  });

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
