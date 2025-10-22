import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { FetchManager } from "../../api/FetchManager";
import { displayOverlay } from "../../utils/ui/overlay";
import { showSwal, getConfirmConfig } from "./ui/swalUtils";
import {
  updateRowState,
  toggleCheckbox,
  resetAllChecks,
} from "./utils/tableUtils";

document.addEventListener("DOMContentLoaded", () => {
  let lastCheckedNumDa = "";
  const fetchManager = new FetchManager();
  const tableBody = document.querySelector("#tableBody"); // sélecteur pour le tBody
  const checkboxes = tableBody.querySelectorAll(".modern-checkbox"); // tous les checkbox
  const select = document.getElementById("action_non_dispo"); // liste déroulante de choix de redirection

  const ACTION_ENDPOINTS = {
    delete: API_ENDPOINTS.DELETE_ARTICLES_DA,
    create: API_ENDPOINTS.CREATE_ARTICLES_DA,
  };

  async function handleSelectChange() {
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked); // Filtrer les checkbox qui sont cochées
    if (!checkedBoxes.length) {
      await showSwal({
        icon: "error",
        title: "Aucun article sélectionné",
        html: `Vous n'avez sélectionné aucun article. Veuillez choisir au moins un article avant de cliquer sur les choix d'action.`,
        confirmButtonText: "OK",
      });
      select.value = "";
      return;
    }

    const selectedIds = checkedBoxes.map((cb) => cb.value); // Récupérer les IDs sélectionnés
    const selectedLignes = checkedBoxes.map((cb) => cb.dataset.numeroLigne); // Récupérer les numeroLigne sélectionnés
    const numeroDemandeAppro = checkedBoxes[0].dataset.numeroDemandeAppro; // Récupérer le numeroDemandeAppro du premier coché (ou undefined si aucune)
    const actionType = select.value;
    select.value = "";

    const confirmConfig = getConfirmConfig(actionType, selectedIds.length);

    try {
      const confirmation = await showSwal({
        ...confirmConfig,
        showCancelButton: true,
      });

      if (confirmation.isConfirmed) {
        displayOverlay(true);
        const result = await fetchManager.post(ACTION_ENDPOINTS[actionType], {
          ids: selectedIds,
          lines: selectedLignes,
          numDa: numeroDemandeAppro,
        });
        displayOverlay(false);

        await showSwal({
          icon: result.status,
          title: result.title,
          html: result.message,
        });

        if (actionType === "delete" && result.status === "success") {
          const scrollPosition = window.scrollY;
          displayOverlay(true);
          window.location.reload();
          window.scrollTo(0, scrollPosition);
        }
      } else {
        console.log("Action annulée par l'utilisateur");
      }
    } catch (error) {
      displayOverlay(false);
      console.error(error);
      await showSwal({
        icon: "error",
        title: "Erreur",
        html: "Une erreur est survenue lors de l'envoi des données.",
      });
    }
  }

  function handleCheckboxChange(checkbox) {
    const numDa = checkbox.dataset.numeroDemandeAppro;
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);

    if (!lastCheckedNumDa || numDa === lastCheckedNumDa) {
      updateRowState(checkbox, checkbox.checked);
      lastCheckedNumDa = checkbox.checked ? numDa : "";
    } else {
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
        cancelButtonText: "Annuler",
        customClass: { htmlContainer: "swal-text-left" },
      }).then((result) => {
        if (result.isConfirmed) {
          resetAllChecks(checkedBoxes);
          toggleCheckbox(checkbox, true);
          lastCheckedNumDa = numDa;
        } else {
          toggleCheckbox(checkbox, false);
        }
      });
    }
  }

  select.addEventListener("change", handleSelectChange);

  tableBody.addEventListener("click", (e) => {
    if (!e.target.matches("td.clickable-td")) return;
    const row = e.target.closest("tr");
    const checkbox = row.querySelector(".modern-checkbox");
    if (checkbox) toggleCheckbox(checkbox, !checkbox.checked);
  });

  tableBody.addEventListener("change", (e) => {
    if (!e.target.classList.contains("modern-checkbox")) return;
    handleCheckboxChange(e.target);
  });
});
