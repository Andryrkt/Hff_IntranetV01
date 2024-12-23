import { toggleSpinner } from "./spinnerUtils.js";
import { populateServiceOptions } from "./uiUtils.js";

export function fetchNumMatMarqueCasier(numOr, rectangle) {
  const url = `/Hffintranet/api/numMat-marq-casier/${numOr}`;
  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des données");
      }
      return response.json();
    })
    .then((data) => {
      const contenu = `${data.numMat} | ${data.marque} | ${data.casier}`;
      rectangle.textContent = contenu || "N/A";
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
  const url = `/Hffintranet/service-informix-fetch/${agence}`;
  toggleSpinner(spinnerService, serviceContainer, true);

  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des services");
      }
      return response.json();
    })
    .then((services) => {
      populateServiceOptions(services, serviceInput);
    })
    .catch((error) => console.error("Erreur :", error))
    .finally(() => toggleSpinner(spinnerService, serviceContainer, false));
}
