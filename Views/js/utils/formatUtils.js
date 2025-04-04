/** PERMET DE FORMATER UN NOMBRE (utilisation du bibliothème numeral.js)*/
// Définir une locale personnalisée
numeral.register('locale', 'fr-custom', {
  delimiters: {
    thousands: '.',
    decimal: ',',
  },
  abbreviations: {
    thousand: 'k',
    million: 'm',
    billion: 'b',
    trillion: 't',
  },
});

// Utiliser la locale personnalisée
numeral.locale('fr-custom');

export function formatMontant(montant) {
  let result = numeral(montant).format(0, 0);
  return result == 0 ? '' : result;
}

export function parseMontant(montantStr) {
  return parseFloat(montantStr.replace(/\./g, '').replace(',', '.'));
}
