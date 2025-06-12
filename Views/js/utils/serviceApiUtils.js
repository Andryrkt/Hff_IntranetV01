import { toggleSpinners } from "./spinnerUtils.js";
import { populateServiceOptions } from "./inputUtils.js";
import { FetchManager } from "../api/FetchManager.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

export function fetchServicesForAgence(
  agence,
  serviceInput,
  spinnerService,
  serviceContainer
) {
  const url = `service-informix-fetch/${agence}`;
  toggleSpinners(spinnerService, serviceContainer, true);

  fetchManager
    .get(url)
    .then((services) => {
      populateServiceOptions(services, serviceInput);
    })
    .catch((error) => console.error("Erreur :", error))
    .finally(() => toggleSpinners(spinnerService, serviceContainer, false));
}
