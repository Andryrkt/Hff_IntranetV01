import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { FetchManager } from "../../api/FetchManager";
import { displayOverlay } from "../../utils/ui/overlay";

document.addEventListener("DOMContentLoaded", function () {
  let lastCheckedNumDa = "";
  const fetchManager = new FetchManager(); // ton objet FetchManager
  const tableBody = document.querySelector("#tableBody"); // sélecteur pour le tBody
  const checkboxes = tableBody.querySelectorAll(".modern-checkbox"); // tous les checkbox
  const select = document.getElementById("action_non_dispo"); // liste déroulante de choix de redirection

  // On écoute le changement de valeur
  select.addEventListener("change", async function () {
    // Filtrer les checkbox qui sont cochées
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

    // Récupérer les numeroLigne sélectionnés
    const selectedLignes = checkedBoxes.map((cb) => cb.dataset.numeroLigne);

    // Récupérer le numeroDemandeAppro du premier coché (ou undefined si aucune)
    const numeroDemandeAppro = checkedBoxes[0].dataset.numeroDemandeAppro;

    try {
      let endpoint = "";
      let actionType = this.value; // garder l'action avant de réinitialiser
      this.value = "";

      if (actionType === "delete") endpoint = API_ENDPOINTS.DELETE_ARTICLES_DA;
      if (actionType === "create") endpoint = API_ENDPOINTS.CREATE_ARTICLES_DA;

      // Configuration dynamique selon l'action
      let confirmConfig = {};

      if (actionType === "delete") {
        confirmConfig = {
          title: "Confirmer la suppression",
          text: `Voulez-vous vraiment supprimer ${selectedIds.length} article(s) ?`,
          icon: "warning",
          confirmButtonText: "Oui, supprimer",
          confirmButtonColor: "#d33", // rouge
          cancelButtonText: "Annuler",
        };
      } else if (actionType === "create") {
        confirmConfig = {
          title: "Confirmer la création",
          text: `Voulez-vous vraiment créer ${selectedLignes.length} article(s) ?`,
          icon: "question",
          confirmButtonText: "Oui, créer",
          confirmButtonColor: "#198754", // vert
          cancelButtonText: "Annuler",
        };
      }

      // Affichage de la confirmation avant l'action
      const confirmation = await Swal.fire({
        title: confirmConfig.title,
        html: confirmConfig.text,
        icon: confirmConfig.icon,
        showCancelButton: true,
        confirmButtonText: confirmConfig.confirmButtonText,
        confirmButtonColor: confirmConfig.confirmButtonColor,
        cancelButtonText: confirmConfig.cancelButtonText,
        customClass: {
          htmlContainer: "swal-text-left",
        },
      });

      if (confirmation.isConfirmed) {
        displayOverlay(true);

        const result = await fetchManager.post(endpoint, {
          ids: selectedIds,
          lines: selectedLignes,
          numDa: numeroDemandeAppro,
        });

        displayOverlay(false);

        Swal.fire({
          icon: result.status,
          title: result.title,
          html: result.message,
          customClass: {
            htmlContainer: "swal-text-left",
          },
        }).then(() => {
          if (actionType === "delete" && result.status === "success") {
            const scrollPosition = window.scrollY;
            displayOverlay(true);
            window.location.reload();
            window.scrollTo(0, scrollPosition);
          }
        });
      } else {
        console.log("Action annulée par l'utilisateur");
      }
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

    const numDa = checkbox.dataset.numeroDemandeAppro;
    const checkedBoxes = [...checkboxes].filter((cb) => cb.checked);

    if (!lastCheckedNumDa || numDa === lastCheckedNumDa) {
      updateRowState(checkbox, checkbox.checked);
      lastCheckedNumDa = checkbox.checked ? numDa : "";
    } else {
      confirmCheck(checkbox, numDa, checkedBoxes);
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

  function confirmCheck(checkbox, numDa, checkedBoxes) {
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
        lastCheckedNumDa = numDa;
      } else {
        checkbox.checked = false;
        updateRowState(checkbox, false);
      }
    });
  }
});
