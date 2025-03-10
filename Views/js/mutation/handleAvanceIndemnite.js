const dateDebutLabel = document.querySelector(
  "label[for='mutation_form_dateDebut']"
);
const allRequiredField = [
  'mutation_form_dateFin',
  'mutation_form_indemniteForfaitaire',
  'mutation_form_nombreJourAvance',
  'mutation_form_totalIndemniteForfaitaire',
];
const allNotRequiredField = ['mutation_form_supplementJournaliere'];

export function handleAvance(avance) {
  avance === 'OUI' ? acceptAvance() : declineAvance();
}

export function acceptAvance() {
  dateDebutLabel.textContent =
    "Date début affectation / Frais d'installation";
  allRequiredField.forEach((fieldId) => toggleField(fieldId));
  allNotRequiredField.forEach((fieldId) => toggleField(fieldId, true, false));
}

export function declineAvance() {
  dateDebutLabel.textContent = 'Date de début de mutation';
  allRequiredField.forEach((fieldId) => toggleField(fieldId, false));
  allNotRequiredField.forEach((fieldId) => toggleField(fieldId, false, false));
}

export function toggleField(fieldId, accept = true, required = true) {
  let field = document.getElementById(fieldId);
  if (accept) {
    field.classList.remove('disabled');
    field.required = required;
  } else {
    field.value = null;
    field.classList.add('disabled');
    field.required = false;
  }
}
