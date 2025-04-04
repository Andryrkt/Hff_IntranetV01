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

/**===================================================================
 * PERMET DE FORMTER UN NOMBRE (utilisation du bibliothème numeral.js)
 *===================================================================*/
// Fonction pour enregistrer une locale personnalisée
export function registerLocale(name, options = {}) {
  numeral.register("locale", name, {
    delimiters: options.delimiters || { thousands: ".", decimal: "," },
    abbreviations: options.abbreviations || {
      thousand: "k",
      million: "m",
      billion: "b",
      trillion: "t",
    },
    ordinal:
      options.ordinal ||
      function (number) {
        return number === 1 ? "er" : "ème";
      },
    currency: options.currency || { symbol: "Ar" },
  });
}

// Fonction pour changer la locale
export function setLocale(name) {
  numeral.locale(name);
}

// Fonction pour formater un nombre entier
export function formatNumberInt(value) {
  return numeral(value).format("0,0.00"); // "0,0" garde les séparateurs de milliers
}

// Fonction pour formater un nombre flottant avec précision
export function formatNumberFloat(value, decimals = 2) {
  return numeral(value).format(`0,0.${"0".repeat(decimals)}`);
}

// Fonction pour formater un montant monétaire
export function formatCurrency(value, symbol = true) {
  return numeral(value).format(`0,0.00 ${symbol ? " $ " : ""}`);
}

export function formatNumberSpecial(value) {
  // Remplace les virgules par des points pour uniformiser le stockage interne
  value = value.replace(",", ".");

  // Vérifie si l'utilisateur a entré un séparateur décimal
  if (value.includes(".")) {
    let [integerPart, decimalPart] = value.split(".");

    // Formater la partie entière avec un espace pour les milliers
    integerPart = numeral(integerPart.replace(/\s/g, ""))
      .format("0,0")
      .replace(/,/g, " ");

    // Concaténer la partie entière et la partie décimale (remise avec une virgule)
    return integerPart + "," + decimalPart;
  } else {
    // Formater la valeur avec un espace pour les milliers
    return numeral(value.replace(/\s/g, "")).format("0,0").replace(/,/g, " ");
  }
}
