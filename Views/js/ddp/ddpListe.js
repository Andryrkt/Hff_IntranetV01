import { AutoComplete } from "../utils/AutoComplete.js";
import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();
/** ========================================================================
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 *============================================================================*/
const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
const spinnerService = document.getElementById("spinner-service");
const serviceContainer = document.getElementById("service-container");
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  const url = `api/agence-fetch/${agenceDebiteur}`;
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

/**===================================================
 * Autocomplete champ FOURNISSEUR
 *====================================================*/
const fournisseurInput = document.querySelector("#ddp_search_fournisseur");

async function fetchFournisseurs() {
  return await fetchManager.get("api/numero-libelle-fournisseur");
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

function onSelectFournisseur(item) {
  fournisseurInput.value = `${item.num_fournisseur}-${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: fournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-fournisseur"),
  loaderElement: document.querySelector("#loader-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectFournisseur,
});
