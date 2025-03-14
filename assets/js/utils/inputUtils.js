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
