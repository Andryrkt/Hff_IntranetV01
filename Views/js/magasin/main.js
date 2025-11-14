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
  if (
    pageType === "liste_cde_fnr_non_genere" ||
    pageType === "liste_cde_fnr_non_place"
  ) {
    // groupRows(rows, tableBody, currentConfig.cellIndices, false);
  } else {
    groupRows(rows, tableBody, currentConfig.cellIndices);
  }

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
    // const agence = agenceInput.value.split("-")[0];
    const agence = agenceInput.value;
    console.log(agence);

    fetchServicesForAgence(
      agence,
      serviceInput,
      spinnerService,
      serviceContainer
    );
  });

  //pour liste commande fournisseur non généré
  if (pageType === "liste_cde_fnr_non_genere") {
    const agenceEmetteurInput = document.querySelector(
      currentConfig.agenceEmetteurInput
    );
    const serviceEmetteurInput = document.querySelector(
      currentConfig.serviceEmetteurInput
    );
    const spinnerServiceEmetteur = document.querySelector(
      currentConfig.spinnerServiceEmetteur
    );
    const serviceContainerEmetteur = document.querySelector(
      currentConfig.serviceContainerEmetteur
    );

    agenceEmetteurInput.addEventListener("change", () => {
      const agence = agenceEmetteurInput.value.split("-")[0];
      fetchServicesForAgence(
        agence,
        serviceEmetteurInput,
        spinnerServiceEmetteur,
        serviceContainerEmetteur
      );
    });
  }

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

  if (pageType === "liste_cde_fnr_non_genere") {
    const numDocInput = document.querySelector(currentConfig.numDocInput);
    numDocInput.addEventListener("input", () => allowOnlyNumbers(numDocInput));
  } else {
    const numOrInput = document.querySelector(currentConfig.numOrInput);
    numOrInput.addEventListener("input", () => allowOnlyNumbers(numOrInput));
  }
}
