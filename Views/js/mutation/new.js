import { handleAvance } from './handleAvanceIndemnite';
import { handleService } from './agenceService';
import { formatFieldsToUppercase } from './formatField';
import { calculTotalIndemnite, updateIndemnite } from './depense';
import { calculateDaysAvance } from './handleDate';

document.addEventListener('DOMContentLoaded', function () {
  const avance = document.getElementById('mutation_form_avanceSurIndemnite');
  const site = document.getElementById('mutation_form_site');
  const dateDebutInput = document.getElementById('mutation_form_dateDebut');
  const dateFinInput = document.getElementById('mutation_form_dateFin');
  const nombreJourAvance = document.getElementById(
    'mutation_form_nombreJourAvance'
  );

  /** Agence et service */
  handleService();

  /** Avance sur indemnité de chantier */
  avance.addEventListener('change', function () {
    handleAvance(this.value);
  });

  /** Calcul de la date de différence entre Date Début et Date Fin */
  dateDebutInput.addEventListener('change', calculateDaysAvance);
  dateFinInput.addEventListener('change', calculateDaysAvance);

  /** Calcul de l'indemnité forfaitaire journalière */
  site.addEventListener('change', function () {
    if (this.value && avance.value === 'OUI') {
      updateIndemnite(this.value);
    }
  });

  /** Formater des données en majuscule */
  formatFieldsToUppercase();

  /** Ajout de l'évènement personnalisé pour caluler le total de l'indemnité forfaitaire */
  nombreJourAvance.addEventListener('valueAdded', calculTotalIndemnite);
});
