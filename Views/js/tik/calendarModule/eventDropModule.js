import { postData } from '../../utils/fetchUtils';

/**
 * Fonction pour gérer le cas où la replanification est acceptée
 *
 * @param {HTMLElement} spinner élément correspondant au spinner
 * @param {string} url url pour la méthode POST de l'api
 * @param {object} data objet de données à envoyer en POST à l'api
 */
export async function acceptReplanification(spinner, url, data) {
  try {
    spinner.classList.remove('d-none');
    return await postData(url, data);
  } catch (error) {
    console.error('Erreur lors de la replanification:', error);
  } finally {
    spinner.classList.add('d-none');
  }
}
