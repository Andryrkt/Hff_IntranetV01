/**
 * Convertit la valeur d'un champ en majuscules.
 * @param {HTMLElement} input - Le champ d'entrée à convertir.
 */
export function toUppercase(input) {
  input.value = input.value.toUpperCase();
}

/**
 * Autorise uniquement les chiffres dans un champ d'entrée.
 * @param {HTMLElement} input - Le champ d'entrée à filtrer.
 */
export function allowOnlyNumbers(input) {
  input.addEventListener("input", function () {
    input.value = input.value.replace(/[^0-9]/g, "");
  });
}

/**
 * Limite le nombre de caractères autorisés dans un champ d'entrée.
 * @param {HTMLElement} input - Le champ d'entrée à limiter.
 * @param {number} maxLength - Le nombre maximum de caractères autorisés.
 */
export function limitInputLength(input, maxLength) {
  input.addEventListener("input", function () {
    if (input.value.length > maxLength) {
      input.value = input.value.slice(0, maxLength);
    }
  });
}

export function populateServiceOptions(services, serviceInput) {
  // Supprimer toutes les options existantes
  while (serviceInput.options.length > 0) {
    serviceInput.remove(0);
  }

  // Ajouter une option par défaut
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.text = " -- Choisir une service -- ";
  serviceInput.add(defaultOption);

  // Ajouter les options à partir des services récupérés
  services.forEach((service) => {
    const option = document.createElement("option");
    option.value = service.value;
    option.text = service.text;
    serviceInput.add(option);
  });

  // Afficher les nouvelles valeurs et textes des options (pour débogage)
  for (let i = 0; i < serviceInput.options.length; i++) {
    const option = serviceInput.options[i];
    console.log("Value:", option.value, "Text:", option.text);
  }
}

/** * Transfère les données d'un tableau de fichiers vers un champ input de type file.
 * @param {File[]} filesArray - Le tableau de fichiers à transférer.
 * @param {HTMLInputElement} inputFile - Le champ input file où les fichiers seront assignés.
 */
export function transfererDonnees(filesArray, inputFile) {
  // Créer un objet DataTransfer pour gérer les fichiers
  const dataTransfer = new DataTransfer();

  // Ajouter chaque fichier à l'objet DataTransfer
  filesArray.forEach((file) => {
    dataTransfer.items.add(file);
  });

  // Assigner les fichiers à l'input file
  inputFile.files = dataTransfer.files;
}
