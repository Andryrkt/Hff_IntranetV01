import { MultiSelectAutoComplete } from "../utils/AutoComplete";
import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();

async function fetchClient() {
  return await fetchManager.get("rh/demande-de-conge/api/matricule-nom-prenom");
}
function displayClient(item) {
  return `${item.matricule} - ${item.nomPrenoms}`;
}

// Fonction pour initialiser l'autocomplete
function initializeAutoComplete() {
  const hiddenMatriculeInput = document.querySelector(
    "#demande_conge_matricule"
  ); // Le champ caché
  const searchInput = document.querySelector("#matricule-search-input");
  const tagsContainer = document.querySelector(
    "#matricule-multi-select-container"
  );
  const suggestionContainer = document.querySelector(
    "#suggestion-matricule-nom-prenom"
  );
  const loaderElement = document.querySelector("#loader-matricule-nom-prenom");

  // Vérifier que tous les éléments nécessaires existent
  if (
    !hiddenMatriculeInput ||
    !searchInput ||
    !tagsContainer ||
    !suggestionContainer
  ) {
    console.warn(
      "Certains éléments nécessaires à l'autocomplete sont manquants"
    );
    return;
  }

  new MultiSelectAutoComplete({
    // L'input visible pour la recherche
    inputElement: searchInput,
    // Le conteneur des suggestions
    suggestionContainer: suggestionContainer,
    // L'icône de chargement
    loaderElement: loaderElement,

    // -- Options spécifiques à l'affichage par tags --
    // Le conteneur où les tags seront affichés
    tagsContainer: tagsContainer,
    // Le champ caché qui stocke les valeurs pour la soumission du formulaire
    hiddenInputElement: hiddenMatriculeInput,
    // ---------------------------------------------

    debounceDelay: 300,
    fetchDataCallback: fetchClient,
    // Callback pour afficher l'item dans la liste de suggestion
    displayItemCallback: (item) => displayClient(item),
    // Callback pour convertir l'item en string (utilisé pour la valeur et l'unicité)
    itemToStringCallback: (item) => `${item.matricule}`,
  });
}

// Attendre que le DOM soit complètement chargé
document.addEventListener("DOMContentLoaded", function () {
  // Vérifier si les éléments sont déjà disponibles
  if (document.querySelector("#matricule-search-input")) {
    initializeAutoComplete();
  } else {
    // Si les éléments ne sont pas encore disponibles, attendre un peu
    setTimeout(initializeAutoComplete, 100);
  }
});

// Pour gérer les cas où le DOM est modifié dynamiquement
if (document.querySelector("#matricule-search-input")) {
  initializeAutoComplete();
}
