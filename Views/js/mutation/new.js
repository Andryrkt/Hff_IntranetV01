import { handleAvance } from './handleAvanceIndemnite';
import { handleService } from './agenceService';
import { formatFieldsToUppercase } from './formatField';
import {
  calculTotal,
  calculTotalAutreDepense,
  calculTotalIndemnite,
  updateIndemnite,
  updateModePaiement,
} from './depense';
import { calculateDaysAvance } from './handleDate';
import { formatMontant } from '../utils/formatUtils';

document.addEventListener('DOMContentLoaded', function () {
  const avance = document.getElementById('mutation_form_avanceSurIndemnite');
  const site = document.getElementById('mutation_form_site');
  const matricule = document.getElementById('mutation_form_matriculeNomPrenom');
  const modePaiementLabelInput = document.getElementById(
    'mutation_form_modePaiementLabel'
  );
  const dateDebutInput = document.getElementById('mutation_form_dateDebut');
  const dateFinInput = document.getElementById('mutation_form_dateFin');
  const nombreJourAvance = document.getElementById(
    'mutation_form_nombreJourAvance'
  );
  const supplementJournalier = document.getElementById(
    'mutation_form_supplementJournaliere'
  );
  const autreDepense1 = document.getElementById('mutation_form_autresDepense1');
  const autreDepense2 = document.getElementById('mutation_form_autresDepense2');
  const totalIndemniteInput = document.getElementById(
    'mutation_form_totalIndemniteForfaitaire'
  );
  const totaAutreDepenseInput = document.getElementById(
    'mutation_form_totalAutresDepenses'
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

  /** Mode de paiement et valeur */
  matricule.addEventListener('change', function () {
    if (this.value && avance.value === 'OUI') {
      updateModePaiement(this.value);
    }
  });
  modePaiementLabelInput.addEventListener('change', function () {
    if (matricule.value && avance.value === 'OUI') {
      updateModePaiement(matricule.value);
    }
  });

  /** Calculer Montant total Autre dépense et montant total général */
  autreDepense1.addEventListener('input', function () {
    this.value = formatMontant(parseInt(this.value.replace(/[^\d]/g, '')) || 0);
    calculTotalAutreDepense();
  });
  autreDepense2.addEventListener('input', function () {
    this.value = formatMontant(parseInt(this.value.replace(/[^\d]/g, '')) || 0);
    calculTotalAutreDepense();
  });

  /** Formater des données en majuscule */
  formatFieldsToUppercase();

  /** Calcul de l'indemnité total forfaitaire */
  supplementJournalier.addEventListener('input', function () {
    supplementJournalier.value = formatMontant(
      parseInt(this.value.replace(/[^\d]/g, '')) || 0
    );
    calculTotalIndemnite();
  });

  /** Ajout de l'évènement personnalisé pour caluler le total de l'indemnité forfaitaire */
  nombreJourAvance.addEventListener('valueAdded', calculTotalIndemnite);

  /** Ajout de l'évènement personnalisé pour calculer le total général */
  totalIndemniteInput.addEventListener('valueAdded', calculTotal);
  totaAutreDepenseInput.addEventListener('valueAdded', calculTotal);

  /** Evènement sur le formulaire */
  const myForm = document.getElementById('form-mutation');
  myForm.addEventListener('submit', function (event) {
    let montantTotal = document.getElementById(
      'mutation_form_totalGeneralPayer'
    );
    if (montantTotal.value > 500000) {
      event.preventDefault();
      alert('Le montant total général ne peut être supérieur à 500.000 Ariary');
      montantTotal.classList.add(
        'border',
        'border-2',
        'border-danger',
        'border-opacity-75'
      );
    }
  });
});
