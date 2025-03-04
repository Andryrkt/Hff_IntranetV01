import { groupRows } from "./tableHandler.js";
import { fetchServicesForAgence } from "./utils/serviceApiUtils.js";
import { toUppercase, allowOnlyNumbers } from "./utils/inputUtils.js";
import { config } from "./config/selecteurConfig.js";

/** ================================================
 * Configuration dynamique en fonction de la page
 ====================================================*/

// Détecter la configuration de la page
const pageType = document.querySelector("#conteneur").dataset.pageType; // Par exemple: "a_traiter" ou "a_livrer"
console.log(pageType);

// Charger la configuration actuelle
const currentConfig = config[pageType];
if (!currentConfig) {
  console.error("Configuration introuvable pour cette page.");
} else {
  /** ================================================
  * pour le separateur et fusion des numOR 
 ====================================================*/
  // Initialiser la gestion des tableaux
  const tableBody = document.querySelector(currentConfig.tableBody);
  const rows = document.querySelectorAll(`${currentConfig.tableBody} tr`);
  groupRows(rows, tableBody, currentConfig.cellIndices);

  /** =================================================
   * AFFICHER LES SERVICES SELON L'AGENCE SELECTIONNER
   * ===============================================*/
  // Gestion des services
  const agenceInput = document.querySelector(currentConfig.agenceInput);
  const serviceInput = document.querySelector(currentConfig.serviceInput);
  const spinnerService = document.querySelector(currentConfig.spinnerService);
  const serviceContainer = document.querySelector(
    currentConfig.serviceContainer
  );

  agenceInput.addEventListener("change", () => {
    const agence = agenceInput.value.split("-")[0];
    fetchServicesForAgence(
      agence,
      serviceInput,
      spinnerService,
      serviceContainer
    );
  });

  /**============================
   *  MISE EN MAJUSCULE
   * =============================*/
  // Gestion des champs en majuscule
  const numDitInput = document.querySelector(currentConfig.numDitInput);
  const refPieceInput = document.querySelector(currentConfig.refPieceInput);
  numDitInput.addEventListener("input", () => toUppercase(numDitInput));
  refPieceInput.addEventListener("input", () => toUppercase(refPieceInput));

  /**==================================================
 * valider seulement les chiffres
 ===================================================*/
  // Validation des chiffres
  const numOrInput = document.querySelector(currentConfig.numOrInput);
  numOrInput.addEventListener("input", () => allowOnlyNumbers(numOrInput));
}
