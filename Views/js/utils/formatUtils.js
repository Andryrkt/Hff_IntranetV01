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
  return numeral(montant).format(0, 0);
}
