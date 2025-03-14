import { fetchData } from './fetchUtils.js';
import { toggleSpinner } from './spinnerUtils.js';

export async function updateMessage(
  modal,
  url,
  modalBodyContent,
  spinnerElement,
  containerElement
) {
  try {
    // Affiche le spinner avant de lancer le fetch
    toggleSpinner(spinnerElement, containerElement, true);
    modal.show();
    const data = await fetchData(url);

    console.log(data);

    if (!data.edit) {
      if (!data.ouvert) {
        modalBodyContent.textContent = `Impossible de modifier ce ticket car il a été déjà validé (refusé).`;
      } else {
        modalBodyContent.textContent = `Vous n'avez pas l'autorisation pour modifier ce ticket.`;
      }
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour des données:', error);
  } finally {
    // Désactive le spinner une fois le traitement terminé
    toggleSpinner(spinnerElement, containerElement, false);
  }
}
