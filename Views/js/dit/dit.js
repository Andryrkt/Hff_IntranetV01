/**recupère l'idMateriel et afficher les information du matériel */
const idMaterielInput = document.querySelector(
  '#demande_intervention_idMateriel'
);
const numParcInput = document.querySelector('#demande_intervention_numParc');

const numSerieInput = document.querySelector('#demande_intervention_numSerie');

const constructeurInput = document.querySelector('#constructeur');
const designationInput = document.querySelector('#designation');
const modelInput = document.querySelector('#model');
const casierInput = document.querySelector('#casier');
const kmInput = document.querySelector('#km');
const heuresInput = document.querySelector('#heures');
const coutAcquisitionInput = document.querySelector('#coutAcquisition');
const amortissementInput = document.querySelector('#amortissement');
const vncInput = document.querySelector('#vnc');
const caInput = document.querySelector('#ca');
const chargeLocativeInput = document.querySelector('#chargeLocative');
const chargeEntretienInput = document.querySelector('#chargeEntretien');
const resultatExploitationInput = document.querySelector(
  '#resultatExploitation'
);
const erreur = document.querySelector('#erreur');
const containerInfoMateriel = document.querySelector('#containerInfoMateriel');

document.addEventListener('DOMContentLoaded', (event) => {
  let timeouts = {}; // Objets de timeouts pour chaque champ

  function debounceInput(callback, delay, inputId) {
    return (...args) => {
      clearTimeout(timeouts[inputId]); // Réinitialise le timer pour ce champ spécifique
      timeouts[inputId] = setTimeout(() => {
        callback(...args); // Appelle la fonction après le délai
      }, delay);
    };
  }

  idMaterielInput.addEventListener(
    'input',
    debounceInput(
      () => {
        handleInputChange(idMaterielInput);
      },
      2000,
      'idMateriel'
    )
  );

  numParcInput.addEventListener(
    'input',
    debounceInput(
      () => {
        handleInputChange(numParcInput);
      },
      2000,
      'numParc'
    )
  );

  numSerieInput.addEventListener(
    'input',
    debounceInput(
      () => {
        handleInputChange(numSerieInput);
      },
      2000,
      'numSerie'
    )
  );

  function handleInputChange(inputElement) {
    InfoMateriel(); // Appeler votre fonction principale
    clearAndToggleRequired(inputElement); // Nettoyer et définir les règles requises
  }

  function clearAndToggleRequired(excludeInput) {
    if (excludeInput !== idMaterielInput) {
      idMaterielInput.value = '';
      idMaterielInput.removeAttribute('required');
    } else {
      idMaterielInput.setAttribute('required', 'required');
    }

    if (excludeInput !== numParcInput) {
      numParcInput.value = '';
      numParcInput.removeAttribute('required');
    } else {
      numParcInput.setAttribute('required', 'required');
    }

    if (excludeInput !== numSerieInput) {
      numSerieInput.value = '';
      numSerieInput.removeAttribute('required');
    } else {
      numSerieInput.setAttribute('required', 'required');
    }
  }

  function buildUrl(base, idMateriel = 0, numParc = 0, numSerie = 0) {
    return `${base}/${idMateriel || 0}/${numParc || 0}/${numSerie || 0}`;
  }

  function resetInfoMateriel(message) {
    containerInfoMateriel.innerHTML = '';
    idMaterielInput.value = '';
    numParcInput.value = '';
    numSerieInput.value = '';

    erreur.classList.add('text-danger');
    erreur.innerHTML = message;
  }

  function showSpinner(container) {
    container.innerHTML = `
    <div class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
    </div>
  `;
  }

  function createMaterielInfoDisplay(container, data) {
    if (!container) {
      console.error(`Container not found.`);
      return;
    }

    if (!data || !Array.isArray(data) || data.length === 0) {
      console.error('Invalid or empty data provided.');
      container.innerHTML =
        '<p class="text-danger">Aucune donnée disponible.</p>';
      return;
    }

    const fields = [
      { label: 'Constructeur', key: 'constructeur' },
      { label: 'Désignation', key: 'designation' },
      { label: 'KM', key: 'km' },
      { label: 'N° Parc', key: 'num_parc' },

      { label: 'Modèle', key: 'modele' },
      { label: 'Casier', key: 'casier_emetteur' },
      { label: 'Heures', key: 'heure' },
      { label: 'N° Serie', key: 'num_serie' },
      { label: 'Id Materiel', key: 'num_matricule' },
    ];

    const createFieldHtml = (label, value) => `
  <li class="fw-bold">
    ${label} :
    <div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle">
      ${value || "<span class='text-danger'>Non disponible</span>"}
    </div>
  </li>
`;

    container.innerHTML = `
    <ul class="list-unstyled">
      <div class="row">
        <div class="col-12 col-md-6">
          ${fields
            .slice(0, 4)
            .map((field) => createFieldHtml(field.label, data[0][field.key]))
            .join('')}
        </div>
        <div class="col-12 col-md-6">
          ${fields
            .slice(4)
            .map((field) => createFieldHtml(field.label, data[0][field.key]))
            .join('')}
        </div>
      </div>
    </ul>
  `;
  }

  function isValidInput(value) {
    return value && value.trim().length > 0;
  }

  function InfoMateriel() {
    const idMateriel = idMaterielInput.value;
    const numParc = numParcInput.value;
    const numSerie = numSerieInput.value;

    if (
      !isValidInput(idMateriel) &&
      !isValidInput(numParc) &&
      !isValidInput(numSerie)
    ) {
      resetInfoMateriel("Veuillez compléter l'un des champs.");
      return;
    }

    const hasValidInput =
      idMateriel !== '' || numParc !== '' || numSerie !== '';

    if (hasValidInput) {
      erreur.innerHTML = '';
      const url = buildUrl(
        '/Hffintranet/fetch-materiel',
        idMateriel,
        numParc,
        numSerie
      );

      // Afficher le spinner dans le container
      showSpinner(containerInfoMateriel);

      fetch(url)
        .then((response) => {
          if (!response.ok) {
            throw new Error('Erreur lors de la récupération des données.');
          }
          return response.json();
        })
        .then((data) => {
          console.log(data);

          erreur.innerHTML = '';

          // Effacer le spinner et afficher les données
          containerInfoMateriel.innerHTML = '';
          createMaterielInfoDisplay(containerInfoMateriel, data);
        })
        .catch((error) => {
          if (error instanceof SyntaxError) {
            resetInfoMateriel(
              "Erreur : l'information du matériel n'est pas dans la base de données."
            );
          } else {
            console.error('Error:', error);
            erreur.innerHTML = 'Erreur : ' + error.message;
          }
        });
    } else {
      resetInfoMateriel("veuillez completer l'un des champs ");
    }
  }

  /**
   * EMPECHE LA SOUMISSION DU FORMULAIRE lorsqu'on appuis sur la touche entrer
   */
  const inputNoEntrers = document.querySelectorAll('.noEntrer');
  inputNoEntrers.forEach((inputNoEntrer) => {
    inputNoEntrer.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault(); // Empêche le rechargement de la page
        console.log(
          'La touche Entrée a été pressée dans le champ :',
          inputNoEntrer.placeholder
        );
      }
    });
  });
});

/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector('.agenceDebiteur');
const serviceDebiteurInput = document.querySelector('.serviceDebiteur');
const spinnerService = document.getElementById('spinner-service');
const serviceContainer = document.getElementById('service-container');
agenceDebiteurInput.addEventListener('change', selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  toggleSpinner(true);
  fetch(url)
    .then((response) => response.json())
    .then((services) => {
      console.log(services);
      updateServiceOptions(services);
    })
    .catch((error) => console.error('Error:', error))
    .finally(() => toggleSpinner(false));
}

function toggleSpinner(show) {
  spinnerService.style.display = show ? 'inline-block' : 'none';
  serviceContainer.style.display = show ? 'none' : 'block';
}

function updateServiceOptions(services) {
  // Supprimer toutes les options existantes
  while (serviceDebiteurInput.options.length > 0) {
    serviceDebiteurInput.remove(0);
  }

  // Ajouter les nouvelles options à partir du tableau services
  for (var i = 0; i < services.length; i++) {
    var option = document.createElement('option');
    option.value = services[i].value;
    option.text = services[i].text;
    serviceDebiteurInput.add(option);
  }

  //Afficher les nouvelles valeurs et textes des options
  for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
    var option = serviceDebiteurInput.options[i];
    console.log('Value: ' + option.value + ', Text: ' + option.text);
  }
}

/**
 * CHAMP CLIENT MISE EN MAJUSCULE
 */
const nomClientInput = document.querySelector(
  '#demande_intervention_nomClient'
);
nomClientInput.addEventListener('input', MiseMajuscule);
function MiseMajuscule() {
  nomClientInput.value = nomClientInput.value.toUpperCase();
}

/**
 * INTERNE - EXTERNE (champ )
 */
const interneExterneInput = document.querySelector('.interneExterne');
const numTelInput = document.querySelector('.numTel');
const clientSousContratInput = document.querySelector('.clientSousContrat');
const demandeDevisInput = document.querySelector(
  '#demande_intervention_demandeDevis'
);
const erreurClient = document.querySelector('#erreurClient');
const numClientInput = document.querySelector(
  '#demande_intervention_numeroClient'
);

if (interneExterneInput.value === 'INTERNE') {
  nomClientInput.setAttribute('disabled', true);
  numClientInput.setAttribute('disabled', true);
  numTelInput.setAttribute('disabled', true);
  clientSousContratInput.setAttribute('disabled', true);
}

interneExterneInput.addEventListener('change', interneExterne);

function interneExterne() {
  console.log(interneExterneInput.value);
  if (interneExterneInput.value === 'EXTERNE') {
    nomClientInput.removeAttribute('disabled');
    numClientInput.removeAttribute('disabled');
    numTelInput.removeAttribute('disabled');
    clientSousContratInput.removeAttribute('disabled');
    demandeDevisInput.removeAttribute('disabled');
    agenceDebiteurInput.setAttribute('disabled', true);
    serviceDebiteurInput.setAttribute('disabled', true);
  } else {
    nomClientInput.setAttribute('disabled', true);
    numClientInput.setAttribute('disabled', true);
    numTelInput.setAttribute('disabled', true);
    demandeDevisInput.setAttribute('disabled', true);
    clientSousContratInput.setAttribute('disabled', true);
    agenceDebiteurInput.removeAttribute('disabled');
    serviceDebiteurInput.removeAttribute('disabled');
  }
}

/** LIMITATION DE CARACTERE DU TELEPHONE */
function limitInputCharacters(inputElement, maxLength) {
  inputElement.addEventListener('input', () => {
    if (inputElement.value.length > maxLength) {
      inputElement.value = inputElement.value.substring(0, maxLength);
    }
  });
}
limitInputCharacters(numTelInput, 10);

/** LES CARACTES CHIFFRE SEULEMENT */
function allowOnlyNumbers(inputElement) {
  inputElement.addEventListener('input', () => {
    inputElement.value = inputElement.value.replace(/[^0-9]/g, '');
  });
}
allowOnlyNumbers(numTelInput);

/** FORM */
const myForm = document.querySelector('#myForm');

myForm.addEventListener('submit', intExtEnvoier);
function intExtEnvoier() {
  agenceDebiteurInput.removeAttribute('disabled');
  serviceDebiteurInput.removeAttribute('disabled');
}

/**
 * permet de formater le nombre en limitant 2 chiffre après la virgule et séparer les millier par un point
 */
function formatNumber(input) {
  let number = parseFloat(input);
  if (!isNaN(number)) {
    // Formater le nombre en utilisant la locale fr-FR
    let formatted = number.toLocaleString('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    // Remplacer les espaces par des points pour les séparateurs de milliers
    formatted = formatted.replace(/\s/g, '.');
    return formatted;
  }
}

/**
 * VALIDATION DU DETAIL DEMANDE (ne peut pas plus de 3 ligne et plus de 86 caractère par ligne)
 */
const textarea = document.querySelector('.detailDemande');

textarea.addEventListener('input', function () {
  var lines = textarea.value.split('\n');

  // Limiter chaque ligne à 86 caractères
  for (var i = 0; i < lines.length; i++) {
    if (lines[i].length > 86) {
      lines[i] = lines[i].substring(0, 86);
    }
  }

  // Limiter le nombre de lignes à 3
  if (lines.length > 3) {
    textarea.value = lines.slice(0, 3).join('\n');
  } else {
    textarea.value = lines.join('\n');
  }
});

/**
 * AUTOCOMPLETE NOM et NUMERO CLient
 */
document.addEventListener('DOMContentLoaded', function () {
  let preloadedData = []; // Variable pour stocker les données préchargées

  // Précharger les données dès le début
  const url = document
    .querySelector('.autocomplete')
    ?.getAttribute('data-autocomplete-url');
  if (url) {
    preloadData(url);
  }

  // Fonction pour afficher les suggestions
  function showSuggestions(field, suggestionsContainer, data) {
    suggestionsContainer.innerHTML = ''; // Efface les suggestions existantes
    data.forEach((item) => {
      const suggestion = document.createElement('div');
      suggestion.textContent = item.label; // Affiche le label
      suggestion.addEventListener('click', () => {
        let label = field === 'numero' ? item.label : item.value;
        let value = field === 'numero' ? item.value : item.label;
        updateClientFields(label, value); // Remplit le champ avec la sélection
        suggestionsContainer.innerHTML = ''; // Efface les suggestions
      });
      suggestionsContainer.appendChild(suggestion);
    });
  }

  function updateClientFields(label, value) {
    const numeroClient = document.getElementById(
      'demande_intervention_numeroClient'
    );
    const nomClient = document.getElementById('demande_intervention_nomClient');

    // Vérification si les éléments sont présents dans le DOM
    if (numeroClient && nomClient) {
      numeroClient.value = label; // Assigner le label à 'numeroClient'
      nomClient.value = value; // Assigner la value à 'nomClient'
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  // Fonction pour charger les données au début
  async function preloadData(url) {
    try {
      const response = await fetch(url);
      preloadedData = await response.json(); // Stocke les données
      console.log(preloadedData);
    } catch (error) {
      console.error('Erreur lors du préchargement des données :', error);
    }
  }

  // Fonction pour gérer l'autocomplétion
  function setupAutocomplete(input) {
    // const url = input.getAttribute('data-autocomplete-url');
    const suggestionsContainer = document.getElementById(
      `${input.id}_suggestions`
    );

    input.addEventListener('input', async function () {
      const query = input.value.trim();

      if (query.length > 0) {
        try {
          // Filtre les données préchargées
          let filteredData = [];
          let field = '';
          if (input.id === 'demande_intervention_numeroClient') {
            field = 'numero';
            filteredData = preloadedData.filter((item) =>
              item.label.toLowerCase().includes(query.toLowerCase())
            );
          } else {
            field = 'nom';
            filteredData = preloadedData.filter((item) =>
              item.value.toLowerCase().includes(query.toLowerCase())
            );
            filteredData = filteredData.map((item) => {
              return { label: item.value, value: item.label };
            });
          }
          showSuggestions(field, suggestionsContainer, filteredData);
        } catch (error) {
          console.error(
            'Erreur lors de la récupération des suggestions :',
            error
          );
        }
      } else {
        suggestionsContainer.innerHTML = ''; // Efface les suggestions si la saisie est trop courte
      }
    });

    // Cacher les suggestions si on clique ailleurs
    document.addEventListener('click', (e) => {
      if (
        !input.contains(e.target) &&
        !suggestionsContainer.contains(e.target)
      ) {
        suggestionsContainer.innerHTML = '';
      }
    });
  }

  // Appliquer l'autocomplétion à tous les champs avec la classe "autocomplete"
  document.querySelectorAll('.autocomplete').forEach(setupAutocomplete);
});

/**
 * GRISER LE BOUTTON APRES UNE CLICK
 */
// const boutonInput = document.querySelector("#formDit");

// boutonInput.addEventListener("click", griserBoutton);

// function griserBoutton() {
//   boutonInput.style.display = "none";
// }
