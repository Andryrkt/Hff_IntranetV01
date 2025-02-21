export function calculateDaysAvance() {
  const dateDebutInput = document.getElementById('mutation_form_dateDebut');
  const dateFinInput = document.getElementById('mutation_form_dateFin');
  const nombreJourAvance = document.getElementById(
    'mutation_form_nombreJourAvance'
  );
  const errorMessage = document.querySelector('.error-message');

  if (dateDebutInput.value && dateFinInput.value) {
    const dateDebut = new Date(dateDebutInput.value);
    const dateFin = new Date(dateFinInput.value);

    if (dateDebut > dateFin) {
      errorMessage.textContent =
        'La date de début ne peut pas être supérieure à la date de fin.';
      errorMessage.classList.remove('d-none');
      nombreJourAvance.value = '';
    } else {
      errorMessage.classList.add('d-none');
      const timeDifference = dateFin - dateDebut;
      const dayDifference = timeDifference / (1000 * 3600 * 24);
      nombreJourAvance.value = dayDifference + 1;

      // updateTotalIndemnity();

      // ajout d'une nouvelle evenement qui sera utiliser plus tard
      const event = new Event('valueAdded');
      nombreJourAvance.dispatchEvent(event);
    }
  }
}
