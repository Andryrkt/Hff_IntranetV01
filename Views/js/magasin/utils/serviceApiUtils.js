import { toggleSpinner } from "./spinnerUtils.js";
import { populateServiceOptions } from "./uiUtils.js";
import { FetchManager } from "../../api/FetchManager.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

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
