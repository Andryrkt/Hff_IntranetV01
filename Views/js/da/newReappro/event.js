import { formaterNombre } from "../../utils/formatNumberUtils";

export function handleQteInputEvents(allQteInputs) {
  allQteInputs.forEach((qteInput) => {
    qteInput.addEventListener("input", () => {
      // Nettoyage de la saisie : chiffres uniquement
      qteInput.value = qteInput.value.replace(/\D+/g, "");

      const cellQte = qteInput.closest("td");
      const row = cellQte?.parentElement;
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
      const divMontantTotal = row.lastElementChild?.firstElementChild;
      if (divMontantTotal) {
        divMontantTotal.textContent = qteDem
          ? formaterNombre(qteDem * PU)
          : "-";
      }

      // Surligne si la quantité dépasse la quantité validée
      qteInput.classList.toggle("text-danger", qteDem > qteValide);
    });
  });
}
