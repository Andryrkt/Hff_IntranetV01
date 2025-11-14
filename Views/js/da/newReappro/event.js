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
