import { handleAllField } from './handleField';

const dateDebutLabel = document.querySelector(
  "label[for='mutation_form_dateDebut']"
);
const allRequiredField = [
  'mutation_form_dateFin',
  'mutation_form_indemniteForfaitaire',
  'mutation_form_nombreJourAvance',
  'mutation_form_totalIndemniteForfaitaire',
  'mutation_form_site',
];
const allNotRequiredField = ['mutation_form_supplementJournaliere'];

export function handleAvance(avance) {
  avance === 'OUI' ? acceptAvance() : declineAvance();
  handleAllField(avance);
}

export function acceptAvance() {
  dateDebutLabel.textContent = "Date début affectation / Frais d'installation";
  allRequiredField.forEach((fieldId) => toggleField(fieldId));
  allNotRequiredField.forEach((fieldId) => toggleField(fieldId, true, false));
}

export function declineAvance() {
  dateDebutLabel.textContent = 'Date de début de mutation';
  allRequiredField.forEach((fieldId) => toggleField(fieldId, false));
  allNotRequiredField.forEach((fieldId) => toggleField(fieldId, false, false));
  document.getElementById('mutation_form_totalGeneralPayer').value = null;
  document.querySelectorAll('.error-message')[1].textContent = null;
}

export function toggleField(fieldId, accept = true, required = true) {
  let field = document.getElementById(fieldId);
  if (accept) {
    field.classList.remove('disabled');
    field.required = required;
  } else {
    field.value = '';
    field.classList.add('disabled');
    field.required = false;
  }
}
