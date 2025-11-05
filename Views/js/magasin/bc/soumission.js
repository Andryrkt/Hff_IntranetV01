import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";
import {
  registerLocale,
  setLocale,
  formatNumberSpecial,
} from "../../utils/formatNumberUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

document.addEventListener("DOMContentLoaded", function () {
  const fileInput1 = document.querySelector("#bc_magasin_pieceJoint01");
  if (fileInput1) {
    initializeFileHandlersNouveau("1", fileInput1);
  }

  const fileInput2 = document.querySelector("#bc_magasin_pieceJoint2");
  if (fileInput2) {
    initializeFileHandlersMultiple("2", fileInput2);
  }

  // Gestion de la validation du formulaire
  const form = document.querySelector("#upload-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      // Vérifier si les fichiers requis sont présents
      const fileInput1 = document.querySelector("#bc_magasin_pieceJoint01");
      if (fileInput1 && fileInput1.files.length === 0) {
        e.preventDefault();
        alert("Veuillez sélectionner un fichier devis.");
        return false;
      }
    });
  }

 
/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();

/** ======================================================
 * validation du donnée pour le champ montant bc
 *=========================================================*/
const montantBcInput = document.querySelector("#bc_magasin_montantBc");
registerLocale("fr-custom", { delimiters: { thousands: " ", decimal: "," } }); // Enregistrer une locale personnalisée "fr-custom"
setLocale("fr-custom"); // Utiliser la locale personnalisée
if (montantBcInput) {
  montantBcInput.addEventListener("input", (e) => {
    montantBcInput.value = formatNumberSpecial(montantBcInput.value);
  });
}

// Fonction pour récupérer les lignes sélectionnées
function getLignesSelectionnees() {
  const lignesRAS = [];
  const lignesModifier = [];
  const lignesSupprimer = [];

  document.querySelectorAll(".ras-checkbox:checked").forEach((checkbox) => {
    lignesRAS.push(checkbox.dataset.numeroLigne);
  });

  document.querySelectorAll(".qty-checkbox:checked").forEach((checkbox) => {
    lignesModifier.push(checkbox.dataset.numeroLigne);
  });

  document.querySelectorAll(".delete-checkbox:checked").forEach((checkbox) => {
    lignesSupprimer.push(checkbox.dataset.numeroLigne);
  });

  return {
    ras: lignesRAS,
    modifier: lignesModifier,
    supprimer: lignesSupprimer,
  };
}

 // --- Checkbox Logic ---

  // Make RAS checkbox read-only for user clicks
  document.querySelectorAll(".ras-checkbox").forEach((checkbox) => {
    checkbox.addEventListener("click", (e) => e.preventDefault());
  });

  // Add event listeners for action checkboxes
  document
    .querySelectorAll(".qty-checkbox, .delete-checkbox")
    .forEach((checkbox) => {
      checkbox.addEventListener("change", (event) => {
        handleCheckboxInteraction(event.target);
      });

      // Add event listeners for quantity input fields
      document.querySelectorAll(".nouvelle-qte-input").forEach((input) => {
        input.addEventListener("input", (event) => {
          const numeroLigne = event.target
            .closest("tr")
            .id.split("-")[1];
          const qtyStatus = document.getElementById(`qty-status-${numeroLigne}`);
          if (event.target.value) {
            qtyStatus.textContent = `Qté: ${event.target.value}`;
          } else {
            qtyStatus.textContent = "Qté:";
          }
        });
      });
    });

  // Initialize rows to set default state UI
  document.querySelectorAll(".styled-table tbody tr").forEach((row) => {
    const numeroLigne = row.id.split("-")[1];
    if (numeroLigne) {
      updateRowUI(numeroLigne);
    }
  });
});

function handleCheckboxInteraction(changedCheckbox) {
  const numeroLigne = changedCheckbox.dataset.numeroLigne;
  const row = document.getElementById(`row-${numeroLigne}`);
  const nouvelleQteInput = row.querySelector(".nouvelle-qte-input");

  const rasCheckbox = row.querySelector(".ras-checkbox");
  const qtyCheckbox = row.querySelector(".qty-checkbox");
  const deleteCheckbox = row.querySelector(".delete-checkbox");

  // Handle Qty checkbox change
  if (changedCheckbox === qtyCheckbox) {
    if (qtyCheckbox.checked) {
      deleteCheckbox.checked = false;
      rasCheckbox.checked = false;
    } else {
      nouvelleQteInput.value = ""; // Clear value when unchecked
    }
  }

  // Handle Delete checkbox change
  if (changedCheckbox === deleteCheckbox && deleteCheckbox.checked) {
    qtyCheckbox.checked = false;
    rasCheckbox.checked = false;
    nouvelleQteInput.value = ""; // Clear value
  }

  // If both are unchecked, check RAS
  if (!qtyCheckbox.checked && !deleteCheckbox.checked) {
    rasCheckbox.checked = true;
  }

  updateRowUI(numeroLigne);
}

function updateRowUI(numeroLigne) {
  const row = document.getElementById(`row-${numeroLigne}`);
  if (!row) return;

  const rasCheckbox = row.querySelector(".ras-checkbox");
  const qtyCheckbox = row.querySelector(".qty-checkbox");
  const deleteCheckbox = row.querySelector(".delete-checkbox");
  const nouvelleQteInput = row.querySelector(".nouvelle-qte-input");

  const qtyStatus = document.getElementById(`qty-status-${numeroLigne}`);

  // Reset UI
  row.classList.remove("ras-checked", "checked-row", "delete-checked");
  qtyStatus.style.display = "none";
  nouvelleQteInput.style.display = "none";

  // Apply new UI based on state
  if (rasCheckbox.checked) {
    row.classList.add("ras-checked");
  } else if (qtyCheckbox.checked) {
    row.classList.add("checked-row");
    qtyStatus.style.display = "block";
    nouvelleQteInput.style.display = "inline-block";
    // Update badge text based on input value
    if (nouvelleQteInput.value) {
      qtyStatus.textContent = `Qté: ${nouvelleQteInput.value}`;
    } else {
      qtyStatus.textContent = "Qté:";
    }
  } else if (deleteCheckbox.checked) {
    row.classList.add("delete-checked");
  }
}

