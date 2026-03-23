import { toggleSpinner } from "./spinnerUtils.js";
import { populateServiceOptions, contenuInfoMateriel } from "./uiUtils.js";
import { FetchManager } from "../../api/FetchManager.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

export function fetchNumMatMarqueCasier(numOr, rectangle) {
  const url = `api/numMat-marq-casier/${numOr}`;
  fetchManager
    .get(url)
    .then((data) => {
      // Ajouter le contenu au rectangle
      contenuInfoMateriel(data, rectangle);
    })
    .catch((error) => {
      console.error("Erreur :", error);
      rectangle.textContent = "Erreur de chargement";
    });
}

export function fetchServicesForAgence(
  agence,
  serviceInput,
  spinnerService,
  serviceContainer
) {
  const url = `api/service-informix-fetch/${agence}`;
  toggleSpinner(spinnerService, serviceContainer, true);

  fetchManager
    .get(url)
    .then((services) => {
      populateServiceOptions(services, serviceInput);
    })
    .catch((error) => console.error("Erreur :", error))
    .finally(() => toggleSpinner(spinnerService, serviceContainer, false));
}
