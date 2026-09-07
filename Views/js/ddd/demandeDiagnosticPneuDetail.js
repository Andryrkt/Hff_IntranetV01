import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

document.addEventListener("DOMContentLoaded", function () {
  const actionsConfig = {
    enregistrer: {
      config: {
        title: "Confirmation",
        text: "Veuillez vérifier attentivement toutes les informations saisies avant de confirmer l'enregistrement de ces diagnostics.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, enregistrer",
        cancelButtonText: "Annuler",
        reverseButtons: true,
      },
      loaderMessage: "Enregistrement du diagnostic en cours...",
    },
    valider: {
      config: {
        title: "Confirmation de validation",
        text: "Tous les diagnostics sont renseignés. Valider et envoyer l'email de notification ?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, valider",
        cancelButtonText: "Annuler",
        reverseButtons: true,
      },
      loaderMessage: "Validation du diagnostic en cours...",
    },
    cloture: {
      config: {
        title: "Confirmation",
        text: "Pour clôturer cette demande, vous allez maintenant être redirigé vers la demande d'intervention.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, clôturer",
        cancelButtonText: "Annuler",
      },
      loaderMessage: "Clôture de la demande en cours...",
    },
  };

  const diagnosticForm = document.getElementById("diagnosticForm");
  diagnosticForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const action = e.submitter.name;

    const actionConfig = actionsConfig[action];

    if (!actionConfig) {
      return;
    }

    Swal.fire(actionConfig.config).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(true, actionConfig.loaderMessage);

        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "action";
        hidden.value = action;
        diagnosticForm.appendChild(hidden);

        diagnosticForm.submit();
      }
    });
  });

  const buttonCloture = document.getElementById(
    "bouton-cloturer-diagnostic-pneu",
  );
  if (buttonCloture) {
    buttonCloture.addEventListener("click", function (e) {
      e.preventDefault();
      const actionConfig = actionsConfig["cloture"];
      if (!actionConfig) return;
      const url = this.href;

      Swal.fire(actionConfig.config).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true, actionConfig.loaderMessage);
          window.location.href = url;
        }
      });
    });
  }

  // --- Gestion des Pièces Jointes ---
  const input = document.getElementById("form_piecesJointesAtelier");
  const listContainer = document.getElementById("fichier-list");

  // On s'assure que les deux éléments existent avant d'attacher la logique
  if (input && listContainer) {
    function updateFileList() {
      listContainer.innerHTML = "";

      for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        const li = document.createElement("li");
        li.className = "d-flex justify-content-between align-items-center mb-1";

        const fileName = document.createElement("span");
        fileName.textContent = `${file.name} (${(file.size / 1024).toFixed(0)} Ko)`;
        li.appendChild(fileName);

        const deleteBtn = document.createElement("button");
        deleteBtn.type = "button";
        deleteBtn.className = "btn btn-sm btn-danger";
        deleteBtn.textContent = "Supprimer";
        deleteBtn.dataset.index = i;

        deleteBtn.addEventListener("click", function (e) {
          e.preventDefault();
          removeFile(parseInt(this.dataset.index));
        });

        li.appendChild(deleteBtn);
        listContainer.appendChild(li);
      }
    }

    function removeFile(index) {
      const dt = new DataTransfer();
      for (let i = 0; i < input.files.length; i++) {
        if (i !== index) {
          dt.items.add(input.files[i]);
        }
      }
      input.files = dt.files;
      updateFileList();
    }

    input.addEventListener("change", updateFileList);
    updateFileList();
  }
});
document.addEventListener("click", function (e) {
  const deleteBtn = e.target.closest(".btn-delete-file");
  if (!deleteBtn) return;

  e.preventDefault();

  // Trouve la carte parent et la retire du DOM
  const card = deleteBtn.closest(".piece-card");
  if (card) {
    card.remove();
  }
});
