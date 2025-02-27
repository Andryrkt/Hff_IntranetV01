/**
 * Ajoute des options à une liste déroulante.
 * @param {Array} optionsArray - Tableau contenant les objets avec les propriétés `value` et `text`.
 * @param {HTMLElement} selectElement - Élément HTML de type <select> où ajouter les options.
 */
export function populateSelect(optionsArray, selectElement) {
  // Ajouter les nouvelles options
  for (var i = 0; i < optionsArray.length; i++) {
    var option = document.createElement("option");
    option.value = optionsArray[i].value;
    option.text = optionsArray[i].text;
    selectElement.add(option);
  }
}

/**
 * Ajoute une option par défaut à un élément <select>.
 * @param {HTMLSelectElement} selectElement - L'élément <select> cible.
 * @param {string} placeholder - Le texte affiché pour l'option par défaut.
 */
export function optionParDefaut(selectElement, placeholder = "") {
  if (!(selectElement instanceof HTMLSelectElement)) {
    throw new Error("Le premier argument doit être un élément <select>.");
  }

  // Vérifier si une option par défaut existe déjà
  if (
    selectElement.options.length === 0 ||
    selectElement.options[0].value !== ""
  ) {
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = placeholder || " -- Choisir une option -- ";
    selectElement.add(defaultOption, 0); // Ajouter en première position
  }
}

/**
 * supprimer les options à une liste déroulante.
 * @param {HTMLElement} selectElement - Élément HTML de type <select> où on supprime les options.
 */
export function supprimLesOptions(selectElement) {
  while (selectElement.options.length > 0) {
    selectElement.remove(0);
  }
}

/**
 *permet d'effacer les option dans le selecteur service
 * @param {string} agenceValue
 * @param {HTMLElement} serviceInput
 * @returns
 */
export function DeleteContentService(agenceValue, serviceInput) {
  if (agenceValue === "") {
    // Supprime toutes les options
    supprimLesOptions(serviceInput);

    // Ajoute l'option par défaut
    optionParDefaut(serviceInput, " -- Choisir une option -- ");

    // Indique qu'il faut sortir de la fonction appelante
    return true;
  } else {
    return false;
  }
}

/**
 * permet d'afficher les valeurs de l'option dynamique dans le console
 * @param {HTMLElement} selectElement
 */
export function affichageValeurConsoleLog(selectElement) {
  for (var i = 0; i < selectElement.options.length; i++) {
    var option = selectElement.options[i];
    console.log("Value: " + option.value + ", Text: " + option.text);
  }
}

/**
 * permet de changer l'option du select
 * @param {array} services
 * @param {HTMLElement} serviceInput
 */
export function updateServiceOptions(services, serviceInput) {
  // Supprimer toutes les options existantes
  supprimLesOptions(serviceInput);

  // Ajoute l'option par défaut
  optionParDefaut(serviceInput, " -- Choisir une option -- ");

  // Ajouter les nouvelles options
  populateSelect(services, serviceInput);

  //Afficher les nouvelles valeurs et textes des options
  affichageValeurConsoleLog(serviceInput);
}