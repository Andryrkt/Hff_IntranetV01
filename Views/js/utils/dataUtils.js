/**
 * Normalise une valeur "vide" en renvoyant un placeholder.
 *
 * Cas considérés comme "vides":
 *  - null ou undefined
 *  - chaîne vide "" ou composée uniquement d'espaces
 *  - tableau [] vide
 *  - objet {} sans clés propres
 *
 * Les autres valeurs sont renvoyées telles quelles.
 *
 * @param {*} value - Valeur à normaliser
 * @param {string} [placeholder="-"] - Valeur retournée pour les éléments considérés vides
 * @returns {*} La valeur normalisée
 */
export function normalizeData(value, placeholder = "-") {
  // null / undefined
  if (value == null) return placeholder;

  // string vide / uniquement espaces
  if (typeof value === "string") {
    return value.trim() === "" ? placeholder : value;
  }

  // tableau vide
  if (Array.isArray(value)) {
    return value.length === 0 ? placeholder : value;
  }

  // objet vide (exclut null grâce au test plus haut)
  if (typeof value === "object") {
    // si c'est un objet "plain" sans clés propres -> vide
    if (Object.keys(value).length === 0) return placeholder;
    return value;
  }

  // nombre, boolean, fonctions, etc. -> renvoyer tel quel
  return value;
}
