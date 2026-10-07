import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { ApiRequestManager } from "../../api/ApiRequestManager";
import { displayOverlay } from "../../utils/ui/overlay";
import { swalOptions } from "./ui/swalUtils";
import {
  updateRowState,
  toggleCheckbox,
  resetAllChecks,
} from "./utils/tableUtils";

document.addEventListener("DOMContentLoaded", () => {
  let lastCheckedNumDa = "";
  const fetchManager = new ApiRequestManager();
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
      Swal.fire(swalOptions.noArticleSelected);
      select.value = "";
      return;
    }

    const selectedIds = checkedBoxes.map((cb) => cb.value); // Récupérer les IDs sélectionnés
    const countSelectedIds = selectedIds.length;
    const selectedLignes = checkedBoxes.map((cb) => cb.dataset.numeroLigne); // Récupérer les numeroLigne sélectionnés
    const numeroDemandeAppro = checkedBoxes[0].dataset.numeroDemandeAppro; // Récupérer le numeroDemandeAppro du premier coché (ou undefined si aucune)
    const actionType = select.value;
    const payload = {
      // 👇 "..." (spread operator) : déplie les propriétés d'un objet dans un autre objet.
      // 👇 "&&" (ET logique) : retourne le 2e élément seulement si le 1er est vrai, sinon false.
      ...(actionType === "delete" && {
        // 👉 Si actionType vaut "delete", l'expression renvoie cet objet : { ids: selectedIds, lines: selectedLignes, numDa: numeroDemandeAppro }
        // 👉 Sinon, elle renvoie false (et "..." n'ajoute rien).
        ids: selectedIds,
        lines: selectedLignes,
        numDa: numeroDemandeAppro,
      }),
      ...(actionType === "create" && {
        // 👉 Si actionType vaut "create", alors cet objet est injecté : { ids: selectedIds }
        // 👉 Sinon, false est ignoré.
        ids: selectedIds,
      }),
    };
    const labelMessage = countSelectedIds > 1 ? "des articles" : "de l’article";
    const message = {
      pendingAction: {
        delete: `Suppression ${labelMessage} en cours, merci de patienter ...`,
        create: `Création ${labelMessage} en cours, merci de patienter ...`,
      },
    };

    select.value = "";

    try {
      const confirmation = await Swal.fire(
        swalOptions.getConfirmConfig(actionType, countSelectedIds)
      );

      if (confirmation.isConfirmed) {
        displayOverlay(true, message.pendingAction[actionType]);
        const result = await fetchManager.post(
          ACTION_ENDPOINTS[actionType],
          payload
        );
        displayOverlay(false);

        await Swal.fire(swalOptions.genericResponse(result));
        lastCheckedNumDa = ""; // réinitialiser le dernier DA sélectionné
        resetAllChecks(checkedBoxes); // réinitialiser tous les checkbox cochés

        if (result.status === "success") {
          const scrollPosition = window.scrollY;
          displayOverlay(true, "Action réussie ! La page se met à jour ... ");
          window.location.reload();
          window.scrollTo(0, scrollPosition);
        }
      } else {
        lastCheckedNumDa = ""; // réinitialiser le dernier DA sélectionné
        resetAllChecks(checkedBoxes); // réinitialiser tous les checkbox cochés
        Swal.fire(swalOptions.annulationOperation);
      }
    } catch (error) {
      lastCheckedNumDa = ""; // réinitialiser le dernier DA sélectionné
      resetAllChecks(checkedBoxes); // réinitialiser tous les checkbox cochés
      displayOverlay(false);
      console.error(error);
      Swal.fire(swalOptions.errorGeneric(error));
    }
  }

  function handleCheckboxChange(checkbox) {
    const numDa = checkbox.dataset.numeroDemandeAppro;
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);

    if (!lastCheckedNumDa || numDa === lastCheckedNumDa) {
      updateRowState(checkbox, checkbox.checked);
      lastCheckedNumDa = checkbox.checked ? numDa : "";
    } else {
      Swal.fire(swalOptions.confirmSameDa).then((result) => {
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
    if (checkbox) checkbox.click(); // délégué au handler de "change"
  });

  tableBody.addEventListener("change", (e) => {
    if (!e.target.classList.contains("modern-checkbox")) return;
    handleCheckboxChange(e.target);
  });
});
