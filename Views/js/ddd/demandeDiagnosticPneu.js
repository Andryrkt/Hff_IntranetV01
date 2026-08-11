import { FetchManager } from "../api/FetchManager.js";
import { AutoComplete } from "../utils/AutoComplete.js";
import { allowOnlyNumbers } from "../utils/inputUtils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

// ---- DOM references for material fields ----
const materielSearchInput = document.querySelector(
  "#demande_diagnostic_pneu_materiel_search",
);

const idMaterielInput = document.querySelector(
  "#demande_diagnostic_pneu_id_materiel",
);
const numParcInput = document.querySelector(
  "#demande_diagnostic_pneu_numero_parc_materiel",
);

/**
 * obliger d'ecrire des chiffre dans le champ id materiel
 */
allowOnlyNumbers(idMaterielInput);

/** ===================================================================
 * recupère l'idMateriel et afficher les information du matériel
 * ==================================================================*/

const fetchManager = new FetchManager();

let lastSelectedItem = null;
async function fetchMateriels() {
  return await fetchManager.get(`api/fetch-all-available-materiel`);
}

function displayMateriel(item) {
  return `Id: ${item.num_matricule} - Parc: ${item.num_parc} - S/N: ${item.num_serie} - Marque: ${item.constructeur} - Type: ${item.modele} `;
}
// Met à jour les champs et la fiche
function onSelectMateriels(item) {
  lastSelectedItem = item;

  idMaterielInput.value = item.num_matricule;
  numParcInput.value = item.num_parc;

  // Display fields
  const marqueDisplay = document.getElementById("marqueMaterielDisplay");
  const typeDisplay = document.getElementById("typeMaterielDisplay");
  const designationDisplay = document.getElementById(
    "designationMaterielDisplay",
  );

  if (marqueDisplay) marqueDisplay.textContent = item.constructeur || "-";
  if (typeDisplay) typeDisplay.textContent = item.modele || "-";
  if (designationDisplay)
    designationDisplay.textContent = item.designation || "-";

  // Form fields
  const marqueInput = document.getElementById(
    "demande_diagnostic_pneu_marqueMateriel",
  );
  const typeInput = document.getElementById(
    "demande_diagnostic_pneu_typeMateriel",
  );
  const designationInput = document.getElementById(
    "demande_diagnostic_pneu_designationMateriel",
  );

  if (marqueInput) marqueInput.value = item.constructeur || "";
  if (typeInput) typeInput.value = item.modele || "";
  if (designationInput) designationInput.value = item.designation || "";
}
// Vérifie si la valeur tapée correspond à un item connu
async function validateInput(input, keyToCompare) {
  const data = await fetchMateriels();
  const match = data.find((item) => item[keyToCompare] === input.value);

  if (!match) {
    containerInfoMateriel.innerHTML = `
      <div class="text-danger fw-bold">Aucun matériel trouvé pour "${input.value}". Veuillez choisir un élément dans la liste.</div>
    `;
    lastSelectedItem = null;
  }
}

// Écouteurs de perte de focus pour chaque champ
idMaterielInput.addEventListener("blur", () =>
  validateInput(idMaterielInput, "num_matricule"),
);

new AutoComplete({
  inputElement: materielSearchInput,
  suggestionContainer: document.querySelector("#suggestion-materiel"),
  loaderElement: document.querySelector("#loader-materiel"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchMateriels,
  displayItemCallback: displayMateriel,
  onSelectCallback: onSelectMateriels,
  itemToStringCallback: (item) =>
    `${item.num_matricule} - ${item.num_parc} - ${item.num_serie} - ${item.constructeur}`,
});

document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("pneu-diagnostic-container");
  // Adjust the ID to match your form: "demande_diagnostic_pneu_nbPneuADiagnostiquer"
  const nbPneuInput = document.getElementById(
    "demande_diagnostic_pneu_nbPneuADiagnostiquer",
  );
  const prototypeRow = container.dataset.prototypeRow;

  if (!container || !nbPneuInput || !prototypeRow) {
    console.warn("Missing required elements for dynamic pneu rows.");
    return;
  }

  // Function to create a new row with the given index
  function createRow(index) {
    const html = prototypeRow.replace(/__name__/g, index);
    const tr = document.createElement("tr");
    tr.innerHTML = html;

    // Add event listener to the remove button
    const removeBtn = tr.querySelector(".remove-pneu");
    if (removeBtn) {
      removeBtn.addEventListener("click", function (e) {
        e.preventDefault();
        tr.remove();
        updateRowNumbers();
        // Update the nbPneuInput value after removal
        const remaining = container.querySelectorAll("tr:not(.no-data)").length;
        nbPneuInput.value = remaining;
        // Trigger change event to notify other listeners
        nbPneuInput.dispatchEvent(new Event("change"));
      });
    }

    return tr;
  }

  // Update row numbers (first column)
  function updateRowNumbers() {
    const rows = container.querySelectorAll("tr:not(.no-data)");
    rows.forEach((row, idx) => {
      const td = row.querySelector("td:first-child");
      if (td) td.textContent = idx + 1;
    });
  }

  // Synchronise the number of rows with the input value
  function updateRows(count) {
    // Remove any existing "no data" row
    const noDataRow = container.querySelector(".no-data");
    if (noDataRow) noDataRow.remove();

    let currentRows = container.querySelectorAll("tr:not(.no-data)").length;

    if (count === 0) {
      // Remove all rows and show "no data"
      container
        .querySelectorAll("tr:not(.no-data)")
        .forEach((row) => row.remove());
      const tr = document.createElement("tr");
      tr.className = "no-data";
      tr.innerHTML = '<td colspan="6">Aucun pneu saisi</td>';
      container.appendChild(tr);
      return;
    }

    // Add rows if needed
    while (currentRows < count) {
      const newRow = createRow(currentRows);
      container.appendChild(newRow);
      currentRows++;
    }

    // Remove extra rows if needed
    while (currentRows > count) {
      const lastRow = container.querySelector("tr:not(.no-data):last-child");
      if (lastRow) lastRow.remove();
      currentRows--;
    }

    updateRowNumbers();
  }

  // Listen to changes on the number input
  nbPneuInput.addEventListener("change", function () {
    let val = parseInt(this.value);
    if (isNaN(val) || val < 0) val = 0;
    updateRows(val);
  });

  // Initial synchronisation
  const initialVal = parseInt(nbPneuInput.value) || 0;
  // If there are already rows (e.g., in edit mode), we keep them and adjust if needed
  const existingRows = container.querySelectorAll("tr:not(.no-data)").length;
  if (existingRows === 0) {
    updateRows(initialVal);
  } else {
    // If existing rows count differs from input, we sync
    if (existingRows !== initialVal) {
      updateRows(initialVal);
    } else {
      // Just update numbering
      updateRowNumbers();
    }
  }
});

// Popup ou Modal de confirmation Demande Diag. Pneu  avec chargement

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("diagnostic-pneu-form");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    confirmation(form);
  });
});

function confirmation(form) {
  const submitBtn =
    document.getElementById("bouton-diagnostic-pneu") ||
    form.querySelector('button[type="submit"]');

  // Let the browser display HTML5 validation errors
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  Swal.fire({
    title: "Confirmation",
    text: "Voulez-vous vraiment créer cette demande de diagnostic ?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Oui",
    cancelButtonText: "Annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      if (submitBtn) {
        displayOverlay(true, "Création de la demande en cours...");
        submitBtn.disabled = true;
      }

      form.submit();
    }
  });
}
