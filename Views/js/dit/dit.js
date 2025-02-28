import { AutoComplete } from "../utils/AutoComplete.js";
import { FetchManager } from "../api/FetchManager.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";

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
const constructeurInput = document.querySelector("#constructeur");
const designationInput = document.querySelector("#designation");
const modelInput = document.querySelector("#model");
const casierInput = document.querySelector("#casier");
const kmInput = document.querySelector("#km");
const heuresInput = document.querySelector("#heures");
const coutAcquisitionInput = document.querySelector("#coutAcquisition");
const amortissementInput = document.querySelector("#amortissement");
const vncInput = document.querySelector("#vnc");
const caInput = document.querySelector("#ca");
const chargeLocativeInput = document.querySelector("#chargeLocative");
const chargeEntretienInput = document.querySelector("#chargeEntretien");
const resultatExploitationInput = document.querySelector(
  "#resultatExploitation"
);
const erreur = document.querySelector("#erreur");
const containerInfoMateriel = document.querySelector("#containerInfoMateriel");

/** ===================================================================
 * recupère l'idMateriel et afficher les information du matériel
 * ==================================================================*/

const fetchManager = new FetchManager("/Hffintranet");

async function fetchMateriels() {
  return await fetchManager.get("api/fetch-materiel");
}

function displayMateriel(item) {
  return `Id: ${item.num_matricule} - Parc: ${item.num_parc} - S/N: ${item.num_serie}`;
}

function onSelectMateriels(item) {
  idMaterielInput.value = item.num_matricule;
  numParcInput.value = item.num_parc;
  numSerieInput.value = item.num_serie;

  createMaterielInfoDisplay(containerInfoMateriel, item);
}

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
    console.error(`Container not found.`);
    return;
  }

  const fields = [
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
            .map((field) => createFieldHtml(field.label, data[field.key]))
            .join("")}
        </div>
        <div class="col-12 col-md-6">
          ${fields
            .slice(4)
            .map((field) => createFieldHtml(field.label, data[field.key]))
            .join("")}
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
  let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
  toggleSpinner(true);
  fetch(url)
    .then((response) => response.json())
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

/**
 * CHAMP CLIENT MISE EN MAJUSCULE
 */

nomClientInput.addEventListener("input", MiseMajuscule);
function MiseMajuscule() {
  nomClientInput.value = nomClientInput.value.toUpperCase();
}

/**
 * INTERNE - EXTERNE (champ )
 */
const interneExterneInput = document.querySelector(".interneExterne");
const numTelInput = document.querySelector(".numTel");
const clientSousContratInput = document.querySelector(".clientSousContrat");
const mailClientInput = document.querySelector(".mailClient");
const demandeDevisInput = document.querySelector(
  "#demande_intervention_demandeDevis"
);
const erreurClient = document.querySelector("#erreurClient");

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
    numClientInput.removeAttribute("disabled");
    numTelInput.removeAttribute("disabled");
    clientSousContratInput.removeAttribute("disabled");
    mailClientInput.removeAttribute("disabled");
    demandeDevisInput.removeAttribute("disabled");
    demandeDevisInput.value = "OUI";
    agenceDebiteurInput.setAttribute("disabled", true);
    serviceDebiteurInput.setAttribute("disabled", true);
    agenceDebiteurInput.value = "";
    serviceDebiteurInput.value = "";
  } else {
    nomClientInput.setAttribute("disabled", true);
    numClientInput.setAttribute("disabled", true);
    numTelInput.setAttribute("disabled", true);
    demandeDevisInput.setAttribute("disabled", true);
    demandeDevisInput.value = "NON";
    clientSousContratInput.setAttribute("disabled", true);
    mailClientInput.setAttribute("disabled", true);
    agenceDebiteurInput.removeAttribute("disabled");
    serviceDebiteurInput.removeAttribute("disabled");
    agenceDebiteurInput.value = parsedData.agenceId;
    serviceDebiteurInput.value = parsedData.serviceId;
  }
}

/** LIMITATION DE CARACTERE DU TELEPHONE */
function limitInputCharacters(inputElement, maxLength) {
  inputElement.addEventListener("input", () => {
    if (inputElement.value.length > maxLength) {
      inputElement.value = inputElement.value.substring(0, maxLength);
    }
  });
}
limitInputCharacters(numTelInput, 10);

/** LES CARACTES CHIFFRE SEULEMENT */
function allowOnlyNumbers(inputElement) {
  inputElement.addEventListener("input", () => {
    inputElement.value = inputElement.value.replace(/[^0-9]/g, "");
  });
}
allowOnlyNumbers(numTelInput);

/** FORM */
const myForm = document.querySelector("#myForm");

myForm.addEventListener("submit", intExtEnvoier);
function intExtEnvoier() {
  agenceDebiteurInput.removeAttribute("disabled");
  serviceDebiteurInput.removeAttribute("disabled");
}

/**
 * permet de formater le nombre en limitant 2 chiffre après la virgule et séparer les millier par un point
 */
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

/**
 * VALIDATION DE OBJET DEMANDE (ne peut pas contenir plus de 86 caractère)
 */
const objetDemande = document.querySelector(".noEntrer");

objetDemande.addEventListener("input", function () {
  objetDemande.value = objetDemande.value.substring(0, 86);
});

/**===================
 * BOUTON ENREGISTRER
 *====================*/
setupConfirmationButtons();

/**
 * VALIDATION DU DETAIL DEMANDE (ne peut pas plus de 3 ligne et plus de 86 caractère par ligne)
 */
// const textarea = document.querySelector(".detailDemande");

// textarea.addEventListener("input", function () {
//   var lines = textarea.value.split("\n");

//   // Limiter chaque ligne à 86 caractères
//   for (var i = 0; i < lines.length; i++) {
//     if (lines[i].length > 86) {
//       lines[i] = lines[i].substring(0, 86);
//     }
//   }

//   // Limiter le nombre de lignes à 3
//   if (lines.length > 3) {
//     textarea.value = lines.slice(0, 3).join("\n");
//   } else {
//     textarea.value = lines.join("\n");
//   }
// });

const textarea = document.querySelector(".detailDemande");
const charCount = document.getElementById("charCount");
const MAX_CHARACTERS = 1877;

// Afficher le message initial
charCount.textContent = `Vous avez ${MAX_CHARACTERS} caractères.`;
charCount.style.color = "black"; // Couleur initiale

textarea.addEventListener("input", function () {
  const remainingCharacters = MAX_CHARACTERS - textarea.value.length;

  if (remainingCharacters < 0) {
    textarea.value = textarea.value.substring(0, MAX_CHARACTERS);
  }

  // Mettre à jour le nombre restant et la couleur
  if (textarea.value.length === 0) {
    charCount.textContent = `Vous avez ${MAX_CHARACTERS} caractères.`;
    charCount.style.color = "black";
  } else {
    charCount.textContent = `Il vous reste ${
      remainingCharacters >= 0 ? remainingCharacters : 0
    } caractères.`;
    charCount.style.color = "#000";
    // charCount.style.background = "red"; // Change la couleur lorsqu'on commence à écrire
  }
});

/**
 * GRISER LE BOUTTON APRES UNE CLICK
 */
setupConfirmationButtons();
// const boutonInput = document.querySelector("#formDit");

// boutonInput.addEventListener("click", griserBoutton);

// function griserBoutton() {
//   boutonInput.style.display = "none";
// }
