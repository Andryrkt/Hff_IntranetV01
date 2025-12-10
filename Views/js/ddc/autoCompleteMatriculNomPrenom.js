import { MultiSelectAutoComplete } from "../utils/AutoComplete";
import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();

async function fetchClient() {
  return await fetchManager.get(
    "rh/demande-de-conge/api/personnel-matricule-nom-prenoms"
  );
}
function displayClient(item) {
  return `${item.matricule} - ${item.nomPrenoms}`;
}

const hiddenMatriculeInput = document.querySelector("#demande_conge_matricule"); // Le champ caché
const searchInput = document.querySelector("#matricule-search-input");
const tagsContainer = document.querySelector("#matricule-multi-select-container");


new MultiSelectAutoComplete({
  // L'input visible pour la recherche
  inputElement: searchInput,
  // Le conteneur des suggestions
  suggestionContainer: document.querySelector("#suggestion-matricule-nom-prenom"),
  // L'icône de chargement
  loaderElement: document.querySelector("#loader-matricule-nom-prenom"),
  
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
