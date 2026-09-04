import { FetchManager } from "../api/FetchManager.js";
import { AutoComplete } from "../utils/AutoComplete.js";
import { allowOnlyNumbers } from "../utils/inputUtils.js";
import { displayOverlay } from "../utils/ui/overlay.js";

// ---- Références DOM pour les champs matériel ----
const materielSearchInput = document.querySelector(
  "#demande_diagnostic_pneu_materiel_search",
);
const idMaterielInput = document.querySelector(
  "#demande_diagnostic_pneu_id_materiel",
);
const numParcInput = document.querySelector(
  "#demande_diagnostic_pneu_numero_parc_materiel",
);
const containerInfoMateriel = document.querySelector(
  "#container-info-materiel",
); // CORRECTION: Élément DOM ajouté

// Restriction numérique sur le champ id materiel
if (idMaterielInput) {
  allowOnlyNumbers(idMaterielInput);
}

const fetchManager = new FetchManager();
let cachedMateriels = null;

// Récupère la liste des matériels (avec mise en cache basique pour éviter les requêtes réseau excessives)
async function fetchMateriels() {
  if (!cachedMateriels) {
    cachedMateriels = await fetchManager.get(
      "api/fetch-all-available-materiel",
    );
  }
  return cachedMateriels;
}

function displayMateriel(item) {
  return `Id: ${item.num_matricule} - Parc: ${item.num_parc} - S/N: ${item.num_serie} - Marque: ${item.constructeur} - Type: ${item.modele}`;
}

// Met à jour les champs et la fiche lors de la sélection
function onSelectMateriels(item) {
  if (idMaterielInput) idMaterielInput.value = item.num_matricule;
  if (numParcInput) numParcInput.value = item.num_parc;

  // Affichage texte
  const marqueDisplay = document.getElementById("marqueMaterielDisplay");
  const typeDisplay = document.getElementById("typeMaterielDisplay");
  const designationDisplay = document.getElementById(
    "designationMaterielDisplay",
  );

  if (marqueDisplay) marqueDisplay.textContent = item.constructeur || "-";
  if (typeDisplay) typeDisplay.textContent = item.modele || "-";
  if (designationDisplay)
    designationDisplay.textContent = item.designation || "-";

  // Champs de formulaire
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

  if (materielSearchInput) materielSearchInput.value = "";
  if (containerInfoMateriel) containerInfoMateriel.innerHTML = "";
}

// Vérifie si la valeur tapée correspond à un item connu
async function validateInput(input, keyToCompare) {
  if (!input.value.trim()) return;

  const data = await fetchMateriels();
  const match = data.find((item) => item[keyToCompare] === input.value);

  if (!match && containerInfoMateriel) {
    containerInfoMateriel.innerHTML = `
      <div class="text-danger fw-bold">Aucun matériel trouvé pour "${input.value}". Veuillez choisir un élément dans la liste.</div>
    `;
  }
}

// Écouteur de perte de focus
if (idMaterielInput) {
  idMaterielInput.addEventListener("blur", () =>
    validateInput(idMaterielInput, "num_matricule"),
  );
}

// Auto-complétion
if (materielSearchInput) {
  new AutoComplete({
    inputElement: materielSearchInput,
    suggestionContainer: document.querySelector("#suggestion-materiel"),
    loaderElement: document.querySelector("#loader-materiel"),
    debounceDelay: 300,
    fetchDataCallback: fetchMateriels,
    displayItemCallback: displayMateriel,
    onSelectCallback: onSelectMateriels,
    itemToStringCallback: (item) =>
      `${item.num_matricule} - ${item.num_parc} - ${item.num_serie} - ${item.constructeur}`,
  });
}

// ---- Regroupement de la logique DOMContentLoaded ----
document.addEventListener("DOMContentLoaded", function () {
  // ==========================================
  // 1. Gestion dynamique des lignes Pneu
  // ==========================================
  const container = document.getElementById("pneu-diagnostic-container");
  const nbPneuInput = document.getElementById(
    "demande_diagnostic_pneu_nbPneuADiagnostiquer",
  );

  if (container && nbPneuInput && container.dataset.prototypeRow) {
    const prototypeRow = container.dataset.prototypeRow;

    function createRow(index) {
      const html = prototypeRow.replace(/__name__/g, index);
      const tr = document.createElement("tr");
      tr.innerHTML = html;

      const removeBtn = tr.querySelector(".remove-pneu");
      if (removeBtn) {
        removeBtn.addEventListener("click", function (e) {
          e.preventDefault();
          tr.remove();
          updateRowNumbers();
          const remaining =
            container.querySelectorAll("tr:not(.no-data)").length;
          nbPneuInput.value = remaining;
          nbPneuInput.dispatchEvent(new Event("change"));
        });
      }
      return tr;
    }

    function updateRowNumbers() {
      const rows = container.querySelectorAll("tr:not(.no-data)");
      rows.forEach((row, idx) => {
        const td = row.querySelector("td:first-child");
        if (td) td.textContent = idx + 1;
      });
    }

    function updateRows(count) {
      const noDataRow = container.querySelector(".no-data");
      if (noDataRow) noDataRow.remove();

      let currentRows = container.querySelectorAll("tr:not(.no-data)").length;

      if (count === 0) {
        container
          .querySelectorAll("tr:not(.no-data)")
          .forEach((row) => row.remove());
        const tr = document.createElement("tr");
        tr.className = "no-data";
        tr.innerHTML = '<td colspan="6">Aucun pneu saisi</td>';
        container.appendChild(tr);
        return;
      }

      while (currentRows < count) {
        container.appendChild(createRow(currentRows));
        currentRows++;
      }

      while (currentRows > count) {
        const lastRow = container.querySelector("tr:not(.no-data):last-child");
        if (lastRow) lastRow.remove();
        currentRows--;
      }

      updateRowNumbers();
    }

    nbPneuInput.addEventListener("change", function () {
      let val = parseInt(this.value, 10);
      if (isNaN(val) || val < 0) val = 0;
      updateRows(val);
    });

    // Synchronisation initiale
    const initialVal = parseInt(nbPneuInput.value, 10) || 0;
    const existingRows = container.querySelectorAll("tr:not(.no-data)").length;
    if (existingRows === 0 || existingRows !== initialVal) {
      updateRows(initialVal);
    } else {
      updateRowNumbers();
    }
  }

  // ==========================================
  // 2. Soumission et Validation du Formulaire
  // ==========================================
  const form = document.getElementById("diagnostic-pneu-form");
  if (form) {
    const motifsContainer = document.getElementById("motifs-container");
    const motifsError = document.getElementById("motifs-error");

    function validateMotifs() {
      if (!motifsContainer) return true;

      const motifs = motifsContainer.querySelectorAll('input[type="checkbox"]');
      const hasMotif = Array.from(motifs).some((motif) => motif.checked);

      if (!hasMotif) {
        if (motifsError) motifsError.style.display = "block";
        motifsContainer.scrollIntoView({ behavior: "smooth", block: "center" });
        return false;
      }

      if (motifsError) motifsError.style.display = "none";
      return true;
    }

    if (motifsContainer) {
      motifsContainer
        .querySelectorAll('input[type="checkbox"]')
        .forEach((motif) => {
          motif.addEventListener("change", validateMotifs);
        });
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!validateMotifs() || !form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const submitBtn =
        document.getElementById("bouton-diagnostic-pneu") ||
        form.querySelector('button[type="submit"]');

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
    });
  }

  // ==========================================
  // 3. Gestion des Pièces Jointes
  // ==========================================
  const inputFiles = document.getElementById(
    "demande_diagnostic_pneu_piecesJointes",
  );
  const listContainer = document.getElementById("fichier-list");

  if (inputFiles && listContainer) {
    function updateFileList() {
      listContainer.innerHTML = "";

      Array.from(inputFiles.files).forEach((file, index) => {
        const li = document.createElement("li");
        li.className = "d-flex justify-content-between align-items-center mb-1";

        const fileName = document.createElement("span");
        fileName.textContent = `${file.name} (${(file.size / 1024).toFixed(0)} Ko)`;
        li.appendChild(fileName);

        const deleteBtn = document.createElement("button");
        deleteBtn.type = "button";
        deleteBtn.className = "btn btn-sm btn-danger";
        deleteBtn.textContent = "Supprimer";
        deleteBtn.addEventListener("click", (e) => {
          e.preventDefault();
          removeFile(index);
        });

        li.appendChild(deleteBtn);
        listContainer.appendChild(li);
      });
    }

    function removeFile(index) {
      const dt = new DataTransfer();
      Array.from(inputFiles.files).forEach((file, i) => {
        if (i !== index) dt.items.add(file);
      });
      inputFiles.files = dt.files;
      updateFileList();
    }

    inputFiles.addEventListener("change", updateFileList);
    updateFileList();
  }
});
