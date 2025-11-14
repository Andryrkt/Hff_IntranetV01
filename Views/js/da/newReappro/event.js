import { FetchManager } from "../../api/FetchManager";
import { AutoComplete } from "../../utils/AutoComplete";
import { formaterNombre } from "../../utils/formatNumberUtils";

// Fonction principale
export function handleQteInputEvents(allQteInputs) {
  allQteInputs.forEach((qteInput) => {
    // Appliquer l’état initial
    updateRowState(qteInput);

    // Réagir aux modifications
    qteInput.addEventListener("input", () => updateRowState(qteInput));
  });
}

export function handleInputAutoComplete() {
  const fetchManager = new FetchManager();
  const desiCentraleInput = document.getElementById(
    "demande_appro_reappro_form_desiCentrale"
  );
  const codeCentraleInput = document.getElementById(
    "demande_appro_reappro_form_codeCentrale"
  );
  const editIcon = document.getElementById("editIcon");
  const inputDesiCentraleGroup = editIcon.parentElement;
  desiCentraleInput.addEventListener("input", () => {
    desiCentraleInput.value = desiCentraleInput.value.toUpperCase();
  });

  editIcon.addEventListener("click", function () {
    desiCentraleInput.disabled = false;
    inputDesiCentraleGroup.classList.remove("input-group");
    editIcon.classList.add("d-none");
  });

  new AutoComplete({
    inputElement: desiCentraleInput,
    suggestionContainer: document.querySelector("#suggestion-code-centrale"),
    loaderElement: document.querySelector("#loader-code-centrale"),
    debounceDelay: 100, // Délai en ms
    fetchDataCallback: () => fetchManager.get("api/recup-all-code-centrale"),
    displayItemCallback: (item) =>
      `Code: ${item.code} - Désignation: ${item.desi}`,
    itemToStringCallback: (item) => `- ${item.desi}`,
    itemToStringForBlur: (item) => `${item.desi}`,
    onSelectCallback: (item) => {
      codeCentraleInput.value = item.code;
      desiCentraleInput.value = item.desi;
      disableDesiCentraleInput(
        desiCentraleInput,
        inputDesiCentraleGroup,
        editIcon
      );
    },
    onBlurCallback: (found) => {
      if (!found) {
        desiCentraleInput.value = codeCentraleInput.value = "";
        disableDesiCentraleInput(
          desiCentraleInput,
          inputDesiCentraleGroup,
          editIcon
        );
      }
    },
  });
}

function disableDesiCentraleInput(
  desiCentraleInput,
  inputDesiCentraleGroup,
  editIcon
) {
  desiCentraleInput.disabled = true;
  inputDesiCentraleGroup.classList.add("input-group");
  editIcon.classList.remove("d-none");
}

function updateRowState(qteInput) {
  // Nettoyage de la saisie : chiffres uniquement
  qteInput.value = qteInput.value.replace(/\D+/g, "");

  const cellQte = qteInput.closest("td");
  const row = cellQte.parentElement;
  if (!cellQte || !row) return;

  const PU = parseFloat(cellQte.dataset.dalPu) || 0;
  const qteValide = parseInt(cellQte.dataset.dalQteValideApp, 10) || 0;
  const qteDem = parseInt(qteInput.value, 10) || 0;

  // Mise à jour du style de la ligne
  const hasValue = qteInput.value.trim() !== "";
  [...row.cells].forEach((cell) => {
    const el = cell.firstElementChild;
    if (!el) return;
    el.classList.toggle("jaunatre", hasValue);
  });

  // Mise à jour du montant total
  var lastCell = row.lastElementChild;
  if (lastCell && lastCell.firstElementChild) {
    lastCell.firstElementChild.textContent = qteDem
      ? formaterNombre(qteDem * PU)
      : "-";
  }

  // Surligne si la quantité dépasse la quantité validée
  qteInput.classList.toggle("text-danger", qteDem > qteValide);
}
