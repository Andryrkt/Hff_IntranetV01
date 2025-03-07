import { FetchManager } from '../api/FetchManager';
import { formatDateOrEmpty } from '../planning/utils/date-utils';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

document.addEventListener('DOMContentLoaded', () => {
  /**
   * CALCULE et AFFICHAGE DU NOMBRE DE JOUR
   */
  const dateDebutInput = document.querySelector(
    '#dom_trop_percu_form_dateDebut'
  );
  const dateFinInput = document.querySelector('#dom_trop_percu_form_dateFin');
  const nombreDeJourInput = document.querySelector(
    '#dom_trop_percu_form_nombreJour'
  );
  const oldDateDebutInput = document.querySelector('#oldDateDebut');
  const oldDateFinInput = document.querySelector('#oldDateFin');
  const oldNombreJourInput = document.querySelector('#oldNombreJour');
  const nombreJourTropPercuInput = document.querySelector(
    '#nombreJourTropPercu'
  );

  const errorMessage = document.createElement('div');
  errorMessage.style.color = 'red';
  errorMessage.style.display = 'none';

  if (dateDebutInput && dateFinInput && nombreDeJourInput) {
    dateDebutInput.addEventListener('change', calculateDays);
    dateFinInput.addEventListener('change', calculateDays);
    dateFinInput.parentNode.insertBefore(
      errorMessage,
      dateFinInput.nextSibling
    );
  }

  function getDateWithoutTime(date) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
  }

  function calculateDays() {
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    const dateDebutValue = dateDebutInput.value;
    const dateFinValue = dateFinInput.value;
    const oldDateDebutValue = oldDateDebutInput.value;
    const oldDateFinValue = oldDateFinInput.value;
    const oldNombreJourValue = parseInt(oldNombreJourInput.value);
    const nombreJourTropPercuValue = parseInt(nombreJourTropPercuInput.value);

    if (dateDebutValue && dateFinValue) {
      const dateDebut = getDateWithoutTime(new Date(dateDebutValue));
      const dateFin = getDateWithoutTime(new Date(dateFinValue));
      const oldDateDebut = getDateWithoutTime(new Date(oldDateDebutValue));
      const oldDateFin = getDateWithoutTime(new Date(oldDateFinValue));

      if (dateDebut > dateFin) {
        errorMessage.textContent =
          'La date de début ne peut pas être supérieure à la date de fin.';
        errorMessage.style.display = 'block';
        nombreDeJourInput.value = '';
      } else if (dateDebut < oldDateDebut) {
        errorMessage.textContent = `La date de début ne peut pas être inférieur à la date de début (${formatDateOrEmpty(
          oldDateDebut
        )}) de l'ancien DOM.`;
        errorMessage.style.display = 'block';
        nombreDeJourInput.value = '';
      } else if (dateFin > oldDateFin) {
        errorMessage.textContent = `La date de fin ne peut pas être supérieur à la date de fin (${formatDateOrEmpty(
          oldDateFin
        )}) de l'ancien DOM.`;
        errorMessage.style.display = 'block';
        nombreDeJourInput.value = '';
      } else {
        const timeDifference = dateFin - dateDebut;
        const dayDifference = timeDifference / (1000 * 3600 * 24);
        nombreDeJourInput.value = dayDifference + 1;
        if (
          oldNombreJourValue <
          nombreJourTropPercuValue + parseInt(nombreDeJourInput.value)
        ) {
          errorMessage.textContent =
            'Le nombre de jour est trop grande, veuillez le changer.';
          errorMessage.style.display = 'block';
        } else {
          errorMessage.textContent = '';
          errorMessage.style.display = 'none';

          updateTotalIndemnity();

          //ajout d'une nouvelle evenement qui sera utiliser en bas
          const event = new Event('valueAdded');
          nombreDeJourInput.dispatchEvent(event);
        }
      }
    }
  }

  /** Blocage de Formulaire */
  const formTropPercu = document.querySelector('form');
  formTropPercu.addEventListener('submit', function (e) {
    if (errorMessage.textContent !== '') {
      e.preventDefault();
      dateFinInput.focus();
    }
  });

  /**
   * CALCULE et AFFICHAGE total indemnité de déplacement
   */
  const totalIdemniteDeplacementInput = document.querySelector(
    '#dom_trop_percu_form_totalIndemniteDeplacement'
  );
  const idemnityDeplInput = document.querySelector(
    '#dom_trop_percu_form_idemnityDepl'
  );

  function updateTotalIndemnity() {
    const nombreDeJour = parseInt(nombreDeJourInput.value);
    const indemnityDepl = parseInt(
      idemnityDeplInput.value.replace(/[^\d]/g, '')
    );

    if (!isNaN(nombreDeJour) && !isNaN(indemnityDepl)) {
      const totalIndemnity = nombreDeJour * indemnityDepl;

      totalIdemniteDeplacementInput.value = formatNumberInt(totalIndemnity);

      const event = new Event('valueAdded');
      totalIdemniteDeplacementInput.dispatchEvent(event);
    } else {
      totalIdemniteDeplacementInput.value = '';
    }
  }

  if (idemnityDeplInput) {
    idemnityDeplInput?.addEventListener('input', () => {
      idemnityDeplInput.value = formatNumberInt(idemnityDeplInput.value);
      updateTotalIndemnity();
    });
  }

  /** PERMET DE FORMTER UN NOMBRE (utilisation du bibliothème numeral.js)*/
  // Définir une locale personnalisée
  numeral.register('locale', 'fr-custom', {
    delimiters: {
      thousands: '.',
      decimal: ',',
    },
    abbreviations: {
      thousand: 'k',
      million: 'm',
      billion: 'b',
      trillion: 't',
    },
    ordinal: function (number) {
      return number === 1 ? 'er' : 'ème';
    },
    currency: {
      symbol: 'Ar',
    },
  });

  // Utiliser la locale personnalisée
  numeral.locale('fr-custom');

  function formatNumberInt(value) {
    return numeral(value).format(0, 0);
  }

  /** AFFICHAGE DE l'INDEMNITE FORFAITAIRE JOURNALIERE selon le site */
  const indemniteForfaitaireJournaliereInput = document.querySelector(
    '#dom_trop_percu_form_indemniteForfaitaire'
  );

  /** CALCULE DU TOTAL INDEMNITE FORFAITAIRE */
  const supplementJournalierInput = document.querySelector(
    '#dom_trop_percu_form_supplementJournaliere'
  );
  const totalindemniteForfaitaireInput = document.querySelector(
    '#dom_trop_percu_form_totalIndemniteForfaitaire'
  );

  nombreDeJourInput.addEventListener('valueAdded', calculTotalForfaitaire);

  function calculTotalForfaitaire() {
    if (
      supplementJournalierInput.value === '' &&
      indemniteForfaitaireJournaliereInput.value !== ''
    ) {
      const nombreDeJour = parseInt(nombreDeJourInput.value);
      const indemniteForfaitaireJournaliere = parseInt(
        indemniteForfaitaireJournaliereInput.value.replace(/[^\d]/g, '')
      );

      totalindemniteForfaitaireInput.value = formatNumberInt(
        nombreDeJour * indemniteForfaitaireJournaliere
      );
    } else if (
      supplementJournalierInput.value !== '' &&
      indemniteForfaitaireJournaliereInput.value !== ''
    ) {
      const supplementJournalier = parseInt(
        supplementJournalierInput.value.replace(/[^\d]/g, '')
      );
      const nombreDeJour = parseInt(nombreDeJourInput.value);
      const indemniteForfaitaireJournaliere = parseInt(
        indemniteForfaitaireJournaliereInput.value.replace(/[^\d]/g, '')
      );

      totalindemniteForfaitaireInput.value = formatNumberInt(
        nombreDeJour * (indemniteForfaitaireJournaliere + supplementJournalier)
      );
    } else if (supplementJournalierInput.value !== '') {
      const supplementJournalier = parseInt(
        supplementJournalierInput.value.replace(/[^\d]/g, '')
      );
      const nombreDeJour = parseInt(nombreDeJourInput.value);

      totalindemniteForfaitaireInput.value = formatNumberInt(
        nombreDeJour * supplementJournalier
      );
    }

    const event = new Event('valueAdded');
    totalindemniteForfaitaireInput.dispatchEvent(event);
  }

  /** si l'utilisateur saisie une suplement journalier */
  supplementJournalierInput.addEventListener(
    'input',
    calculTotalForfaitaireAvecSupplement
  );

  function calculTotalForfaitaireAvecSupplement() {
    supplementJournalierInput.value = formatNumberInt(
      supplementJournalierInput.value
    );
    calculTotalForfaitaire();
  }

  /** si l'utilisateur saisie l'indemnite forfatitaire Journaliere */
  indemniteForfaitaireJournaliereInput.addEventListener(
    'input',
    calculTotalForfaitaireIdemniteSaisie
  );

  function calculTotalForfaitaireIdemniteSaisie() {
    indemniteForfaitaireJournaliereInput.value = formatNumberInt(
      indemniteForfaitaireJournaliereInput.value
    );
    calculTotalForfaitaire();
  }

  /** CALCUL TOTAL MONTANT AUTRES DEPENSE */
  const autreDepenseInput_1 = document.querySelector(
    '#dom_trop_percu_form_autresDepense1'
  );
  const autreDepenseInput_2 = document.querySelector(
    '#dom_trop_percu_form_autresDepense2'
  );
  const autreDepenseInput_3 = document.querySelector(
    '#dom_trop_percu_form_autresDepense3'
  );
  const totaAutreDepenseInput = document.querySelector(
    '#dom_trop_percu_form_totalAutresDepenses'
  );

  autreDepenseInput_1.addEventListener('input', () => {
    autreDepenseInput_1.value = formatNumberInt(autreDepenseInput_1.value);
    calculTotalAutreDepense();
  });
  autreDepenseInput_2.addEventListener('input', () => {
    autreDepenseInput_2.value = formatNumberInt(autreDepenseInput_2.value);
    calculTotalAutreDepense();
  });
  autreDepenseInput_3.addEventListener('input', () => {
    autreDepenseInput_3.value = formatNumberInt(autreDepenseInput_3.value);
    calculTotalAutreDepense();
  });

  function calculTotalAutreDepense() {
    console.log('Function calculTotal() called');

    const autreDepense_1 =
      parseInt(autreDepenseInput_1.value.replace(/[^\d]/g, '')) || 0;
    const autreDepense_2 =
      parseInt(autreDepenseInput_2.value.replace(/[^\d]/g, '')) || 0;
    const autreDepense_3 =
      parseInt(autreDepenseInput_3.value.replace(/[^\d]/g, '')) || 0;

    console.log(
      'Values before sum:',
      autreDepense_1,
      autreDepense_2,
      autreDepense_3
    );
    let totaAutreDepense = autreDepense_1 + autreDepense_2 + autreDepense_3;

    console.log('Total:', totaAutreDepense);
    totaAutreDepenseInput.value = formatNumberInt(totaAutreDepense);

    //creation d'une evement personaliser
    const event = new Event('valueAdded');
    totaAutreDepenseInput.dispatchEvent(event);
  }

  /** CALCUL  MONTANT TOTAL */
  const montantTotalInput = document.querySelector(
    '#dom_trop_percu_form_totalGeneralPayer'
  );
  totalIdemniteDeplacementInput.addEventListener('valueAdded', calculTotal);
  totalindemniteForfaitaireInput.addEventListener('valueAdded', calculTotal);
  totaAutreDepenseInput.addEventListener('valueAdded', calculTotal);

  function calculTotal() {
    const totaAutreDepense =
      parseInt(totaAutreDepenseInput.value.replace(/[^\d]/g, '')) || 0;
    const totalIdemniteDeplacement =
      parseInt(totalIdemniteDeplacementInput.value.replace(/[^\d]/g, '')) || 0;
    const totalindemniteForfaitaire =
      parseInt(totalindemniteForfaitaireInput.value.replace(/[^\d]/g, '')) || 0;

    let montantTotal =
      totalindemniteForfaitaire + totaAutreDepense - totalIdemniteDeplacement;

    montantTotalInput.value = '-' + formatNumberInt(montantTotal);
  }

  /** CHANGEMENT DE LABEL MODE DE PAIEMENT */
  const modePayementInput = document.querySelector(
    '#dom_trop_percu_form_modePayement'
  );
  const modeInput = document.querySelector('#dom_trop_percu_form_mode');
  const labelMode = modeInput.previousElementSibling;
  const matriculeInput_2 = document.querySelector(
    '#dom_trop_percu_form_matricule'
  );
  modePayementInput.addEventListener('change', infoPersonnel);
  modeInput.addEventListener('input', () => {
    modeInput.setAttribute('maxlength', 10);
  });
  function infoPersonnel() {
    const matricule = matriculeInput_2.value;
    let url = `personnel-fetch/${matricule}`;
    fetchManager
      .get(url)
      .then((personne) => {
        console.log(personne);
        console.log(modePayementInput.value);
        if (modePayementInput.value === 'VIREMENT BANCAIRE') {
          modeInput.readOnly = true;
          modeInput.value = personne.compteBancaire;
          modeInput.required = false;
        } else if (modePayementInput.value === 'MOBILE MONEY') {
          modeInput.readOnly = false;
          modeInput.value = personne.telephone;
          modeInput.required = true;
        } else {
          modeInput.readOnly = false;
          modeInput.value = '';
          modeInput.required = false;
        }
        labelMode.innerHTML = modePayementInput.value;
      })
      .catch((error) => console.error('Error:', error));
  }

  /**
   * CHAMP MISE EN MAJUSCULE
   */
  //MOTIF DE DEPLACEMNET
  const motifDeplacementInput = document.querySelector(
    '#dom_trop_percu_form_motifDeplacement'
  );
  motifDeplacementInput.addEventListener('input', () => {
    motifDeplacementInput.value = motifDeplacementInput.value
      .toUpperCase()
      .slice(0, 60);
  });

  //NOM CLIENT
  const nomClientInput = document.querySelector('#dom_trop_percu_form_client');
  nomClientInput.addEventListener('input', () => {
    nomClientInput.value = nomClientInput.value.toUpperCase().slice(0, 29);
  });

  //LIEU D'INTERVENTION
  const lieuInterventionInput = document.querySelector(
    '#dom_trop_percu_form_lieuIntervention'
  );

  lieuInterventionInput.addEventListener('input', () => {
    lieuInterventionInput.value = lieuInterventionInput.value
      .toUpperCase()
      .slice(0, 60);
  });

  //MOTIF AUTRE DEPENSE
  //1
  const motifAutreDepense1Input = document.querySelector(
    '#dom_trop_percu_form_motifAutresDepense1'
  );
  motifAutreDepense1Input.addEventListener('input', () => {
    motifAutreDepense1Input.value = motifAutreDepense1Input.value
      .toUpperCase()
      .slice(0, 29);
  });

  //2
  const motifAutreDepense2Input = document.querySelector(
    '#dom_trop_percu_form_motifAutresDepense2'
  );
  motifAutreDepense2Input.addEventListener('input', () => {
    motifAutreDepense2Input.value = motifAutreDepense2Input.value
      .toUpperCase()
      .slice(0, 29);
  });

  //3
  const motifAutreDepense3Input = document.querySelector(
    '#dom_trop_percu_form_motifAutresDepense3'
  );
  motifAutreDepense3Input.addEventListener('input', () => {
    motifAutreDepense3Input.value = motifAutreDepense3Input.value
      .toUpperCase()
      .slice(0, 29);
  });
});
