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
  input.value = input.value.replace(/[^0-9]/g, "");
}
