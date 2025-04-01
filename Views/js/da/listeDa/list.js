import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsTable } from "./tableHandler";
import { fetchServicesForAgence } from "../../utils/serviceApiUtils.js";
import { config } from "./config.js";

document.addEventListener("DOMContentLoaded", function () {
  const designations = document.querySelectorAll(".designation-btn");
  designations.forEach((designation) => {
    designation.addEventListener("click", function () {
      let numeroLigne = this.getAttribute("data-numero-ligne");
      localStorage.setItem("currentTab", numeroLigne);
    });
  });
  mergeCellsTable(0);
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

/** =================================================
 * AFFICHER LES SERVICES SELON L'AGENCE SELECTIONNER
 * ===============================================*/
// Gestion des services
const agenceInput = document.querySelector(config.agenceInput);
const serviceInput = document.querySelector(config.serviceInput);
const spinnerService = document.querySelector(config.spinnerService);
const serviceContainer = document.querySelector(config.serviceContainer);

agenceInput.addEventListener("change", () => {
  const agence = agenceInput.value.split(" ")[0];
  fetchServicesForAgence(
    agence,
    serviceInput,
    spinnerService,
    serviceContainer
  );
});

//pour liste commande fournisseur non généré

const agenceEmetteurInput = document.querySelector(config.agenceEmetteurInput);
const serviceEmetteurInput = document.querySelector(
  config.serviceEmetteurInput
);
const spinnerServiceEmetteur = document.querySelector(
  config.spinnerServiceEmetteur
);
const serviceContainerEmetteur = document.querySelector(
  config.serviceContainerEmetteur
);

agenceEmetteurInput.addEventListener("change", () => {
  const agence = agenceEmetteurInput.value.split(" ")[0];
  fetchServicesForAgence(
    agence,
    serviceEmetteurInput,
    spinnerServiceEmetteur,
    serviceContainerEmetteur
  );
});
