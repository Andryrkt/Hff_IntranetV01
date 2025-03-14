// Fonction pour convertir le mois numérique en français
export function getFrenchMonth(month) {
  const months = [
    'Janvier',
    'Février',
    'Mars',
    'Avril',
    'Mai',
    'Juin',
    'Juillet',
    'Août',
    'Septembre',
    'Octobre',
    'Novembre',
    'Décembre',
  ];
  // Convertir le mois (string ou number) en index (0-11) et retourner le mois
  return months[parseInt(month, 10) - 1];
}
