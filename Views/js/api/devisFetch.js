import { FetchManager } from "./FetchManager.js";
import { updateServiceOptions } from "../utils/ui/uiAgenceServiceUtils.js";
import { toggleSpinner } from "../utils/ui/uiSpinnerUtils.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

/**
 * Fonction pour mettre à jour les donner dans le select de docSoumis à validation DW
 * @param {string} numDit
 * @param {HTMLElement} spinnerSelect
 * @param {HTMLElement} selectContainer
 * @param {HTMLElement} selecteInput
 */
export function fetchDevis(
  numDit,
  spinnerSelect,
  selectContainer,
  selecteInput
) {
  const url = `constraint-soumission/${numDit}`;
  toggleSpinner(spinnerSelect, selectContainer, true);
  fetchManager
    .get(url)
    .then((docDansDw) => {
      console.log(docDansDw);
      let docASoumettre = valeurDocASoumettre(docDansDw);
      updateServiceOptions(docASoumettre, selecteInput);
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => toggleSpinner(spinnerSelect, selectContainer, false));
}

/**
 * Détermine les documents à soumettre en fonction des conditions.
 * @param {Object} docDansDw - L'objet contenant les informations nécessaires.
 * @returns {Array} - Un tableau d'objets avec `value` et `text`.
 */
function valeurDocASoumettre(docDansDw) {
  let docASoumettre = [];
  // && !docDansDw.numeroOR
  // if (
  //   docDansDw.client === "EXTERNE" &&
  //   (docDansDw.statutDit === "AFFECTEE SECTION" ||
  //     docDansDw.statutDevis !== "CLOTUREE VALIDEE") &&
  //   docDansDw.statutDevis !== "Validé atelier"
  // ) {
  //   docASoumettre = [{ value: "DEVIS", text: "DEVIS" }];
  // } else if (
  //   docDansDw.client === "EXTERNE" &&
  //   docDansDw.statutDevis === "Validé atelier"
  // ) {
  //   docASoumettre = [
  //     { value: "DEVIS", text: "DEVIS" },
  //     { value: "BC", text: "BC" },
  //   ];
  // } else {
  //   docASoumettre = [
  //     { value: "OR", text: "OR" },
  //     { value: "RI", text: "RI" },
  //     { value: "FACTURE", text: "FACTURE" },
  //   ];
  // }

  docASoumettre = [
    { value: "DEVIS-VP", text: "DEVIS - Vérification de prix" },
    { value: "DEVIS-VA", text: "DEVIS - Validation atelier" },
    { value: "BC", text: "BC - BON COMMANDE" },
    { value: "OR", text: "OR - ORDRE DE REPARATION" },
    { value: "RI", text: "RI - RAPPORT D'INTERVENTION" },
    { value: "FACTURE", text: "FACTURE" },
  ];

  return docASoumettre; // Retourne le tableau
}
