import { AutoComplete } from "../utils/AutoComplete.js";
import { FetchManager } from "../api/FetchManager.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { allowOnlyNumbers } from "../utils/inputUtils.js";

const idMaterielInput = document.querySelector(
  "#demande_intervention_idMateriel"
);
const numParcInput = document.querySelector("#demande_intervention_numParc");
const numSerieInput = document.querySelector("#demande_intervention_numSerie");
const numClientInput = document.querySelector(
  "#demande_intervention_numeroClient"
);
const nomClientInput = document.querySelector(
  "#demande_intervention_nomClient"
);

const containerInfoMateriel = document.querySelector("#containerInfoMateriel");

const interneExterneInput = document.querySelector(".interneExterne");
const numTelInput = document.querySelector(".numTel");
const clientSousContratInput = document.querySelector(".clientSousContrat");
const mailClientInput = document.querySelector(".mailClient");
const demandeDevisInput = document.querySelector(
  "#demande_intervention_demandeDevis"
);
const erreurClient = document.querySelector("#erreurClient");

/**
 * obliger d'ecrire des chiffre dans le champ id materiel
 */
allowOnlyNumbers(idMaterielInput);

/** ===================================================================
 * recupère l'idMateriel et afficher les information du matériel
 * ==================================================================*/

const fetchManager = new FetchManager();

let lastSelectedItem = null;

async function fetchMateriels() {
  return await fetchManager.get(`api/fetch-all-materiel`);
}

function displayMateriel(item) {
  return `Id: ${item.num_matricule} - Parc: ${item.num_parc} - S/N: ${item.num_serie}`;
}

// Met à jour les champs et la fiche
function onSelectMateriels(item) {
  lastSelectedItem = item;

  idMaterielInput.value = item.num_matricule;
  numParcInput.value = item.num_parc;
  numSerieInput.value = item.num_serie;

  createMaterielInfoDisplay(containerInfoMateriel, item);
}

// Vérifie si la valeur tapée correspond à un item connu
async function validateInput(input, keyToCompare) {
  const data = await fetchMateriels();
  const match = data.find((item) => item[keyToCompare] === input.value);

  if (!match) {
    containerInfoMateriel.innerHTML = `
      <div class="text-danger fw-bold">Aucun matériel trouvé pour "${input.value}". Veuillez choisir un élément dans la liste.</div>
    `;
    lastSelectedItem = null;
  }
}

// Écouteurs de perte de focus pour chaque champ
idMaterielInput.addEventListener("blur", () =>
  validateInput(idMaterielInput, "num_matricule")
);
numParcInput.addEventListener("blur", () =>
  validateInput(numParcInput, "num_parc")
);
numSerieInput.addEventListener("blur", () =>
  validateInput(numSerieInput, "num_serie")
);

//Activation sur le champ Id Matériel
new AutoComplete({
  inputElement: idMaterielInput,
  suggestionContainer: document.querySelector("#suggestion-idMateriel"),
  loaderElement: document.querySelector("#loader-idMateriel"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchMateriels,
  displayItemCallback: displayMateriel,
  onSelectCallback: onSelectMateriels,
  itemToStringCallback: (item) =>
    `${item.num_matricule} - ${item.num_parc} - ${item.num_serie}`,
});

//Activation sur le champ numSerie
new AutoComplete({
  inputElement: numSerieInput,
  suggestionContainer: document.querySelector("#suggestion-numSerie"),
  loaderElement: document.querySelector("#loader-numSerie"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchMateriels,
  displayItemCallback: displayMateriel,
  onSelectCallback: onSelectMateriels,
  itemToStringCallback: (item) =>
    `${item.num_matricule} - ${item.num_parc} - ${item.num_serie}`,
});

//Activation sur le champ numParc
new AutoComplete({
  inputElement: numParcInput,
  suggestionContainer: document.querySelector("#suggestion-numParc"),
  loaderElement: document.querySelector("#loader-numParc"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchMateriels,
  displayItemCallback: displayMateriel,
  onSelectCallback: onSelectMateriels,
  itemToStringCallback: (item) =>
    `${item.num_matricule} - ${item.num_parc} - ${item.num_serie}`,
});

function createMaterielInfoDisplay(container, data) {
  if (!container) {
    console.error("Container not found.");
    return;
  }

  if (!hasValidData(data)) {
    showNoDataMessage(container);
    return;
  }

  const fields = getMaterielFields();
  container.innerHTML = buildMaterielHtml(fields, data);
}

// Vérifie si les données sont valides
function hasValidData(data) {
  return data && Object.keys(data).length > 0;
}

// Affiche un message d'absence de données
function showNoDataMessage(container) {
  container.innerHTML = `<div class="text-danger fw-bold">Aucune information disponible pour ce matériel.</div>`;
}

// Retourne la liste des champs à afficher
function getMaterielFields() {
  return [
    { label: "Constructeur", key: "constructeur" },
    { label: "Désignation", key: "designation" },
    { label: "KM", key: "km" },
    { label: "N° Parc", key: "num_parc" },
    { label: "Modèle", key: "modele" },
    { label: "Casier", key: "casier_emetteur" },
    { label: "Heures", key: "heure" },
    { label: "N° Serie", key: "num_serie" },
    { label: "Id Materiel", key: "num_matricule" },
  ];
}

// Construit le HTML complet à injecter dans le container
function buildMaterielHtml(fields, data) {
  const createFieldHtml = (label, value) => `
    <li class="fw-bold">
      ${label} :
      <div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle">
        ${value || "<span class='text-danger'>Non disponible</span>"}
      </div>
    </li>
  `;

  const leftColumn = fields
    .slice(0, 4)
    .map((field) => createFieldHtml(field.label, data[field.key]))
    .join("");

  const rightColumn = fields
    .slice(4)
    .map((field) => createFieldHtml(field.label, data[field.key]))
    .join("");

  return `
    <ul class="list-unstyled">
      <div class="row">
        <div class="col-12 col-md-6">
          ${leftColumn}
        </div>
        <div class="col-12 col-md-6">
          ${rightColumn}
        </div>
      </div>
    </ul>
  `;
}

/**========================================
 * AUTOCOMPLETE NOM et NUMERO CLient
 *===========================================*/

async function fetchClients() {
  const url = numClientInput.getAttribute("data-autocomplete-url");
  const result = await fetchManager.get(url);
  return result;
}

function displayClients(item) {
  return `${item.num_client} - ${item.nom_client}`;
}

function onSelectClients(item) {
  numClientInput.value = item.num_client;
  nomClientInput.value = item.nom_client;
}

//Activation sur le champ numero client
new AutoComplete({
  inputElement: numClientInput,
  suggestionContainer: document.querySelector("#suggestion-numClient"),
  loaderElement: document.querySelector("#loader-numClient"),
  debounceDelay: 300,
  fetchDataCallback: fetchClients,
  displayItemCallback: displayClients,
  onSelectCallback: onSelectClients,
  itemToStringCallback: (item) => `${item.num_client} - ${item.nom_client}`,
});

//Activation sur le champ nom client
new AutoComplete({
  inputElement: nomClientInput,
  suggestionContainer: document.querySelector("#suggestion-nomClient"),
  loaderElement: document.querySelector("#loader-nomClient"),
  debounceDelay: 300,
  fetchDataCallback: fetchClients,
  displayItemCallback: displayClients,
  onSelectCallback: onSelectClients,
  itemToStringCallback: (item) => `${item.num_client} - ${item.nom_client}`,
});

/** ==========================================================================
 * EMPECHE LA SOUMISSION DU FORMULAIRE lorsqu'on appuis sur la touche entrer
 *=============================================================================*/
const inputNoEntrers = document.querySelectorAll(".noEntrer");
inputNoEntrers.forEach((inputNoEntrer) => {
  inputNoEntrer.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault(); // Empêche le rechargement de la page
      console.log(
        "La touche Entrée a été pressée dans le champ :",
        inputNoEntrer.placeholder
      );
    }
  });
});

/** =========================================================================
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 *==========================================================================*/
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
const spinnerService = document.getElementById("spinner-service");
const serviceContainer = document.getElementById("service-container");
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  let url = `api/agence-fetch/${agenceDebiteur}`;
  toggleSpinner(true);
  fetchManager
    .get(url)
    .then((services) => {
      console.log(services);
      updateServiceOptions(services);
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => toggleSpinner(false));
}

function toggleSpinner(show) {
  spinnerService.style.display = show ? "inline-block" : "none";
  serviceContainer.style.display = show ? "none" : "block";
}

function updateServiceOptions(services) {
  // Supprimer toutes les options existantes
  while (serviceDebiteurInput.options.length > 0) {
    serviceDebiteurInput.remove(0);
  }

  // Ajouter les nouvelles options à partir du tableau services
  for (var i = 0; i < services.length; i++) {
    var option = document.createElement("option");
    option.value = services[i].value;
    option.text = services[i].text;
    serviceDebiteurInput.add(option);
  }

  //Afficher les nouvelles valeurs et textes des options
  for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
    var option = serviceDebiteurInput.options[i];
    console.log("Value: " + option.value + ", Text: " + option.text);
  }
}

/** ===============================
 * CHAMP CLIENT MISE EN MAJUSCULE
 =================================*/

nomClientInput.addEventListener("input", MiseMajuscule);
function MiseMajuscule() {
  nomClientInput.value = nomClientInput.value.toUpperCase();
}

/**================================
 * INTERNE - EXTERNE (champ )
 ================================*/

if (interneExterneInput.value === "INTERNE") {
  nomClientInput.setAttribute("disabled", true);
  numClientInput.setAttribute("disabled", true);
  numTelInput.setAttribute("disabled", true);
  clientSousContratInput.setAttribute("disabled", true);
  mailClientInput.setAttribute("disabled", true);
}

interneExterneInput.addEventListener("change", interneExterne);

function interneExterne() {
  console.log(interneExterneInput.value);
  const dataInformations = interneExterneInput.dataset.informations;
  const parsedData = JSON.parse(dataInformations);

  if (interneExterneInput.value === "EXTERNE") {
    nomClientInput.removeAttribute("disabled");
    nomClientInput.setAttribute("required", true);
    numClientInput.removeAttribute("disabled");
    numClientInput.setAttribute("required", true);
    numTelInput.removeAttribute("disabled");
    numTelInput.setAttribute("required", true);
    clientSousContratInput.removeAttribute("disabled");
    clientSousContratInput.setAttribute("required", true);
    mailClientInput.removeAttribute("disabled");
    mailClientInput.setAttribute("required", true);
    demandeDevisInput.removeAttribute("disabled");
    demandeDevisInput.value = "OUI";
    agenceDebiteurInput.setAttribute("disabled", true);
    serviceDebiteurInput.setAttribute("disabled", true);
    agenceDebiteurInput.value = "";
    serviceDebiteurInput.value = "";
  } else {
    nomClientInput.setAttribute("disabled", true);
    nomClientInput.removeAttribute("required");
    numClientInput.setAttribute("disabled", true);
    numClientInput.removeAttribute("required");
    numTelInput.setAttribute("disabled", true);
    numTelInput.removeAttribute("required");
    demandeDevisInput.setAttribute("disabled", true);
    demandeDevisInput.value = "NON";
    clientSousContratInput.setAttribute("disabled", true);
    mailClientInput.setAttribute("disabled", true);
    mailClientInput.removeAttribute("required");
    agenceDebiteurInput.removeAttribute("disabled");
    serviceDebiteurInput.removeAttribute("disabled");
    agenceDebiteurInput.value = parsedData.agenceId;
    serviceDebiteurInput.value = parsedData.serviceId;
  }
}

/** ===========================================
 * LIMITATION DE CARACTERE DU TELEPHONE
 *==========================================*/
function limitInputCharacters(inputElement, maxLength) {
  inputElement.addEventListener("input", () => {
    if (inputElement.value.length > maxLength) {
      inputElement.value = inputElement.value.substring(0, maxLength);
    }
  });
}
limitInputCharacters(numTelInput, 10);

/** LES CARACTES CHIFFRE SEULEMENT */
allowOnlyNumbers(numTelInput);

/** FORM */
const ditForm = document.querySelector("#dit-form");

ditForm.addEventListener("submit", intExtEnvoier);
function intExtEnvoier() {
  agenceDebiteurInput.removeAttribute("disabled");
  serviceDebiteurInput.removeAttribute("disabled");
}

/**=========================================================================================================
 * permet de formater le nombre en limitant 2 chiffre après la virgule et séparer les millier par un point
 ============================================================================================================*/
function formatNumber(input) {
  let number = parseFloat(input);
  if (!isNaN(number)) {
    // Formater le nombre en utilisant la locale fr-FR
    let formatted = number.toLocaleString("fr-FR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    // Remplacer les espaces par des points pour les séparateurs de milliers
    formatted = formatted.replace(/\s/g, ".");
    return formatted;
  }
}

/**=========================================================================
 * VALIDATION DE OBJET DEMANDE (ne peut pas contenir plus de 86 caractère)
 =========================================================================*/
const objetDemande = document.querySelector(".noEntrer");

objetDemande.addEventListener("input", function () {
  objetDemande.value = objetDemande.value.substring(0, 86);
});

/**===================
 * BOUTON ENREGISTRER
 *====================*/
document.addEventListener("DOMContentLoaded", function () {
  setupConfirmationButtons();
});

/**==============
 * champt detail
 ===============*/
const textarea = document.querySelector(".detailDemande");
const charCount = document.getElementById("charCount");
const MAX_CHARACTERS = 1800;

// Initialisation du compteur
charCount.textContent = `Vous avez ${MAX_CHARACTERS} caractères.`;
charCount.style.color = "black";

textarea.addEventListener("input", function (event) {
  let text = textarea.value;
  let lineBreaks = (text.match(/\n/g) || []).length;
  let adjustedLength = text.length + lineBreaks * 130;

  // Bloquer l'ajout de texte si la limite est atteinte
  if (adjustedLength > MAX_CHARACTERS) {
    let excessCharacters = adjustedLength - MAX_CHARACTERS;

    while (excessCharacters > 0 && text.length > 0) {
      let lastChar = text[text.length - 1];

      // Si c'est un saut de ligne, retirer 130 caractères
      if (lastChar === "\n") {
        excessCharacters -= 130;
      } else {
        excessCharacters -= 1;
      }

      text = text.substring(0, text.length - 1);
    }

    textarea.value = text; // Mettre à jour la valeur bloquée
    adjustedLength = MAX_CHARACTERS; // Fixer la longueur max
  }

  let remainingCharacters = MAX_CHARACTERS - adjustedLength;

  // Mettre à jour l'affichage du compteur
  charCount.textContent = `Il vous reste ${
    remainingCharacters >= 0 ? remainingCharacters : 0
  } caractères.`;
  charCount.style.color = remainingCharacters <= 0 ? "red" : "black";
});

/** ===============================================================================
 * réparation réalisé par ATE TANA et ATE POL TANA
 *===============================================================================*/
const reparationRealiseSelect = document.querySelector(
  "#demande_intervention_reparationRealise"
);
const atePolTanaContainer = document.querySelector("#ate_pol_tana_container");
const atePolTanaInput = document.querySelector(
  "#demande_intervention_estAtePolTana"
);
const valuesAutorisees = ["ATE TANA", "ATE MAS", "ATE STAR"]; // valeurs autorisées pour la réparation réalisé afin de créer deux DIT

if (reparationRealiseSelect) {
  // permet d'afficher et cacher le champ intervention pneumatique (ate pol tana)
  reparationRealiseSelect.addEventListener("change", function () {
    if (atePolTanaContainer) {
      if (valuesAutorisees.includes(reparationRealiseSelect.value)) {
        atePolTanaContainer.style.display = "block";
      } else {
        atePolTanaContainer.style.display = "none";
      }
    }
  });

  //Blockage de la soumission si ATE POL TANA
  // mais type de document autre que maintenace curative
  // et catégorie autre que REPARATION
  reparationRealiseSelect.addEventListener("change", function () {
    if (reparationRealiseSelect.value === "ATE POL TANA") {
      Swal.fire({
        title: "Attention !",
        html: `Le type de document doit être "<b>Maintenance curative</b>" et le catégorie de demande est "<b>REPARATION</b>"`,
        icon: "warning",
        showCancelButton: false,
        confirmButtonColor: "#fbbb01",
        confirmButtonText: "OUI",
      });
    }
  });
}

if (atePolTanaInput) {
  // affichage d'une confirmation si la cage ate pol tana est coché
  atePolTanaInput.addEventListener("change", function () {
    if (atePolTanaInput.checked === true) {
      Swal.fire({
        title: "êtes vous sure?",
        html: `Les travaux seront réalisés par l'${reparationRealiseSelect.value} en solicitant également l'ATE POL TANA, une deuxième DIT sera créée automatiquement.<br>
    <b>Cliquer sur oui pour confirmer et non pour abandonner.</b>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#fbbb01",
        cancelButtonColor: "#d33",
        confirmButtonText: "OUI",
        cancelButtonText: "NON",
        allowOutsideClick: false, // Permet de ne pas fermer en cliquant à l'extérieur
        allowEscapeKey: false, // Permet de ne pas fermer en tapant sur echape
      }).then((result) => {
        if (result.isConfirmed) {
          //il faut que le cage est coher
          atePolTanaInput.checked = true;
        } else {
          //il faut que le cage est decocher
          atePolTanaInput.checked = false;
        }
      });
    }
  });
}
