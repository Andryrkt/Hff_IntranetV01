import { postData } from '../../utils/fetchUtils';
import { afficherToast } from '../../utils/toastUtils';

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
    const donnees = await postData(url, data);
    if (donnees.status === 'success') {
      afficherToast(
        'success',
        `<strong>Opération effectuée.</strong> La <strong>replanification</strong> du ticket a été effectuée avec succès.`
      );
    } else {
      afficherToast(
        'erreur',
        `<strong>Opération rejetée.</strong> Erreur lors de la demande de <strong>replanification</strong>.`
      );
      throw new Error(donnees.message);
    }
    console.log(donnees);
  } catch (error) {
    console.error('Erreur lors de la replanification:', error.message);
  } finally {
    spinner.classList.add('d-none');
  }
}

/**
 * Fonction pour gérer le cas où la replanification est refuséé
 */
export function declineReplanification(info) {
  info.revert();
  afficherToast(
    'annulation',
    `<strong>Annulation effectuée.</strong> La demande de <strong>replanification</strong> a bien été annulée.`
  );
}
