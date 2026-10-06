import { initializeFileHandlers } from "../utils/file_upload_Utils.js";
import { FetchManager } from "../api/FetchManager.js";
import { AutoComplete } from "../utils/AutoComplete.js";
const fetchManager = new FetchManager();
/** ============================================
 * FICHIER BC AC
 *==================================================*/
console.log("Bonjour");
const fileInput = document.querySelector("#ac_soumis_pieceJoint01");
initializeFileHandlers(1, fileInput);

/**=======================================
 * Methode pour l'autocomplet nom client
 *=======================================*/
const nomClientInput = document.querySelector("#ac_soumis_nomClient");
const suggestionContainer = document.querySelector("#suggestion");

async function fetchClient() {
  return await fetchManager.get("api/autocomplete/all-client");
}

function displayClient(item) {
  return `${item.num_client} - ${item.nom_client}`;
}

function onSelectClient(item) {
  nomClientInput.value = item.nom_client.trim();
}

// Activation sur le champ "Numéro Fournisseur"
new AutoComplete({
  inputElement: nomClientInput,
  suggestionContainer: suggestionContainer,
  loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchClient,
  displayItemCallback: displayClient,
  onSelectCallback: onSelectClient,
  itemToStringCallback: (item) => `${item.num_client} - ${item.value}`,
});

// document.addEventListener("DOMContentLoaded", function () {
//   let preloadedData = [];

//   /**
//    * Fonction pour charger tous les données au début (avant l'evenement)
//    */
//   async function preloadData(url) {
//     try {
//       const response = await fetch(url);
//       preloadedData = await response.json(); // Stocke les données
//     } catch (error) {
//       console.error("Erreur lors du préchargement des données :", error);
//     }
//   }

//   const url = "/Hffintranet/api/autocomplete/all-client";
//   preloadData(url); //recupérer les donner à partir de l'url

//   const suggestionContainer = document.querySelector("#suggestion");

//   nomClientInput.addEventListener("input", filtrerLesDonner);

//   /**
//    * Methode permet de filtrer les donner selon les donnée saisi dans l'input
//    */
//   function filtrerLesDonner() {
//     const nomClient = nomClientInput.value.trim();

//     // Si l'input est vide, efface les suggestions et arrête l'exécution
//     if (nomClient === "") {
//       suggestionContainer.innerHTML = ""; // Efface les suggestions
//       return;
//     }

//     // let filteredData = [];

//     const filteredData = preloadedData.filter((item) => {
//       const phrase = item.label + " - " + item.value;
//       return phrase.toLowerCase().includes(nomClient.toLowerCase());
//     });

//     showSuggestions(suggestionContainer, filteredData);
//   }

//   /**
//    * Methode permet d'afficher les donner sur le div du suggestion
//    * @param {HTMLElement} suggestionsContainer
//    * @param {Array} data
//    */
//   function showSuggestions(suggestionsContainer, data) {
//     // Vérifie si le tableau est vide
//     if (data.length === 0) {
//       suggestionsContainer.innerHTML = ""; // Efface les suggestions
//       return; // Arrête l'exécution de la fonction
//     }

//     suggestionsContainer.innerHTML = ""; // Efface les suggestions existantes
//     data.forEach((item) => {
//       const suggestion = document.createElement("div");
//       suggestion.textContent = item.label + " - " + item.value; // Affiche le label
//       suggestion.addEventListener("click", () => {
//         nomClientInput.value = item.value; // Remplit le champ avec la sélection
//         suggestionsContainer.innerHTML = ""; // Efface les suggestions
//       });
//       suggestionsContainer.appendChild(suggestion);
//     });
//   }
// });
