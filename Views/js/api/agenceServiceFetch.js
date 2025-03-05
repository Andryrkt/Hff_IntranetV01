import { FetchManager } from './FetchManager.js';
import { updateServiceOptions } from '../utils/ui/uiAgenceServiceUtils.js';
import { toggleSpinner } from '../utils/ui/uiSpinnerUtils.js';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

/**
 * Fonction pour récupérer les données de l'agence et mettre à jour les options de service.
 * @param {string} agence - L'identifiant de l'agence.
 * @param {HTMLElement} serviceInput - L'élément d'entrée des services.
 * @param {HTMLElement} spinner - L'élément spinner.
 * @param {HTMLElement} container - Le conteneur parent du spinner.
 */
export function fetchDataAgenceService(
  agence,
  serviceInput,
  spinner,
  container
) {
  const url = `agence-fetch/${agence}`;
  toggleSpinner(spinner, container, true);

  fetchManager
    .get(url)
    .then((services) => {
      console.log(services);
      updateServiceOptions(services, serviceInput);
    })
    .catch((error) => console.error('Error:', error))
    .finally(() => toggleSpinner(spinner, container, false));
}
