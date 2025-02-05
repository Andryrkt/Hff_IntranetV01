/**
 * Methode qui permet de formater un nombre
 * @param {*} nombre
 * @param {string} separateurMillier
 * @param {string} separateurEntierDecimal
 * @returns
 */
export function formaterNombre(
  nombre,
  separateurMillier = ".",
  separateurEntierDecimal = ","
) {
  // Vérification du type
  if (typeof nombre !== "number") {
    // console.error("La valeur n'est pas un nombre :", nombre);
    // Tentative de conversion en nombre
    nombre = Number(nombre);
  }

  // Si la conversion échoue, Number() renverra NaN
  if (isNaN(nombre)) {
    console.error("Impossible de convertir la valeur en nombre :", nombre);
    return "";
  }

  // On fixe deux décimales
  const arrondi = nombre.toFixed(2); // Renvoie une chaîne, ex: "1234567.89"

  // Séparer la partie entière et la partie décimale
  let [entier, decimals] = arrondi.split(".");

  // Ajouter les séparateurs de milliers (en utilisant la regex)
  entier = entier.replace(/\B(?=(\d{3})+(?!\d))/g, separateurMillier);

  return entier + separateurEntierDecimal + decimals;
}
