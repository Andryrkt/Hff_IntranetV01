import { initSelect2WithSelectAll } from "../../utils/select2SelectAll.js";

document.addEventListener("DOMContentLoaded", function () {
  // ── Règle : décocher "Voir" désactive toutes les autres permissions de la ligne ──
  function appliquerRegleVoir(row) {
    const caseVoir = row.querySelector('[data-colonne="peutVoir"]');
    if (!caseVoir) return;

    const autresCases = row.querySelectorAll('[data-depend-voir="true"]');

    const mettreAJour = () => {
      autresCases.forEach((c) => {
        if (!caseVoir.checked) {
          c.checked = false;
          c.disabled = true;
        } else {
          c.disabled = false;
        }
      });
    };

    caseVoir.addEventListener("change", mettreAJour);
    mettreAJour(); // Appliquer l'état initial au chargement
  }

  // --- Initialisation du Select2 avec "Tout sélectionner" ---
  initSelect2WithSelectAll("#permissions_agenceServices", {
    placeholder: "-- Choisir des agences - services --",
  });

  document.querySelectorAll(".ligne-page").forEach(appliquerRegleVoir);

  // ── Case "tout cocher / décocher" par colonne ────────────────────────────
  document.querySelectorAll(".toggle-colonne").forEach(function (toggle) {
    toggle.addEventListener("change", function () {
      const colonne = this.dataset.colonne;

      document
        .querySelectorAll(
          `.case-permission[data-colonne="${colonne}"]:not(:disabled)`
        )
        .forEach((c) => {
          c.checked = toggle.checked;
          // Déclencher le changement pour appliquer la règle "Voir"
          c.dispatchEvent(new Event("change"));
        });
    });
  });
});
