import { fetchData } from '../utils/fetchUtils';
import { toggleSpinner } from '../utils/spinnerUtils';
import { formatMontant, parseMontant } from '../utils/formatUtils';

const indemniteInput = document.getElementById(
  'mutation_form_indemniteForfaitaire'
);
const modePaiementValueInput = document.getElementById(
  'mutation_form_modePaiementValue'
);
const modePaiementLabelInput = document.getElementById(
  'mutation_form_modePaiementLabel'
);
const supplementJournalier = document.getElementById(
  'mutation_form_supplementJournaliere'
);
const nombreJourAvance = document.getElementById(
  'mutation_form_nombreJourAvance'
);
const totalIndemniteInput = document.getElementById(
  'mutation_form_totalIndemniteForfaitaire'
);
const autreDepenseInput1 = document.getElementById(
  'mutation_form_autresDepense1'
);
const autreDepenseInput2 = document.getElementById(
  'mutation_form_autresDepense2'
);
const totaAutreDepenseInput = document.getElementById(
  'mutation_form_totalAutresDepenses'
);
const montantTotalInput = document.getElementById(
  'mutation_form_totalGeneralPayer'
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
    const data = await fetchData(`site-idemnite-fetch/${siteId}/5/5/1`);
    indemniteInput.value = data.montant;
    calculTotalIndemnite();
  } catch (error) {
    console.error("Erreur lors de la mise à jour de l'indemnité:", error);
  } finally {
    // Désactive le spinner une fois le traitement terminé
    toggleSpinner(spinnerElement, containerElement, false);
  }
}

export async function updateModePaiement(personnelId) {
  const labelMode = document.querySelector(
    "label[for='mutation_form_modePaiementValue']"
  );
  const spinnerElement = document.getElementById('spinner-mode-value');
  const containerElement = document.getElementById('mode-value-container');
  try {
    // Affiche le spinner avant de lancer le fetch
    toggleSpinner(spinnerElement, containerElement, true);
    const personne = await fetchData(`personnel-fetch-id/${personnelId}`);
    if (modePaiementLabelInput.value === 'VIREMENT BANCAIRE') {
      labelMode.textContent = 'CPT';
      modePaiementValueInput.classList.add('readonly');
      modePaiementValueInput.value = personne.compteBancaire;
      modePaiementValueInput.required = false;
    } else {
      labelMode.textContent = 'TEL';
      modePaiementValueInput.classList.remove('readonly');
      modePaiementValueInput.value = personne.telephone;
      modePaiementValueInput.required = true;
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du mode de paiement:', error);
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
    if (supplementJournalier.value !== '') {
      indemniteForfaitaire += parseMontant(supplementJournalier.value);
    }
    totalIndemniteInput.value = formatMontant(
      nombreJour * indemniteForfaitaire
    );
    calculTotal(); // calculer le total général
  }
}

export function calculTotalAutreDepense() {
  let autreDepense1 =
    parseInt(autreDepenseInput1.value.replace(/[^\d]/g, '')) || 0;
  let autreDepense2 =
    parseInt(autreDepenseInput2.value.replace(/[^\d]/g, '')) || 0;
  let totaAutreDepense = autreDepense1 + autreDepense2;

  totaAutreDepenseInput.value = formatMontant(totaAutreDepense);

  //creation d'une evement personaliser
  const event = new Event('valueAdded');
  totaAutreDepenseInput.dispatchEvent(event);
}

export function calculTotal() {
  let totaAutreDepense =
    parseInt(totaAutreDepenseInput.value.replace(/[^\d]/g, '')) || 0;
  let totalindemnite =
    parseInt(totalIndemniteInput.value.replace(/[^\d]/g, '')) || 0;

  let montantTotal = totalindemnite + totaAutreDepense;

  montantTotalInput.value = formatMontant(montantTotal);

  if (montantTotal > 500000) {
    montantTotalInput.classList.remove(
      'border',
      'border-2',
      'border-success',
      'border-opacity-75'
    );
    montantTotalInput.classList.add(
      'border',
      'border-2',
      'border-danger',
      'border-opacity-75'
    );
  } else {
    montantTotalInput.classList.remove(
      'border',
      'border-2',
      'border-danger',
      'border-opacity-75'
    );
    montantTotalInput.classList.add(
      'border',
      'border-2',
      'border-success',
      'border-opacity-75'
    );
  }
}
