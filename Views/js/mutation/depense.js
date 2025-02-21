import { fetchData } from '../utils/fetchUtils';
import { toggleSpinner } from '../utils/spinnerUtils';
import { formatMontant } from '../utils/formatUtils';

const indemniteInput = document.getElementById(
  'mutation_form_indemniteForfaitaire'
);
const nombreJourAvance = document.getElementById(
  'mutation_form_nombreJourAvance'
);
const totalIndemniteInput = document.getElementById(
  'mutation_form_totalIndemniteForfaitaire'
);

export async function updateIndemnite(siteId) {
  const spinnerElement = document.getElementById(
    'spinner-indemnite-forfaitaire'
  );
  const containerElement = document.getElementById(
    'indemnite-forfaitaire-container'
  );

  try {
    // Affiche le spinner avant de lancer le fetch
    toggleSpinner(spinnerElement, containerElement, true);
    const data = await fetchData(
      `/Hffintranet/site-idemnite-fetch/${siteId}/5/5/1`
    );
    indemniteInput.value = data.montant;
    calculTotalIndemnite();
  } catch (error) {
    console.error("Erreur lors de la mise à jour de l'indemnité:", error);
  } finally {
    // Désactive le spinner une fois le traitement terminé
    toggleSpinner(spinnerElement, containerElement, false);
  }
}

export function calculTotalIndemnite() {
  if (nombreJourAvance.value !== '' && indemniteInput.value !== '') {
    let nombreJour = parseInt(nombreJourAvance.value);
    let indemniteForfaitaire = parseInt(
      indemniteInput.value.replace(/[^\d]/g, '')
    ); // remplace tous qui est différent de chiffre (\d) en ''
    totalIndemniteInput.value = formatMontant(
      nombreJour * indemniteForfaitaire
    );
  }
}
