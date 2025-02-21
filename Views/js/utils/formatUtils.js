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

export function formatMontant(montant) {
  return numeral(value).format(0, 0);
}
