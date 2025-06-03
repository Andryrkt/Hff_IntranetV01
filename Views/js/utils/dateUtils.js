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

/**
 * Cette Methode permet de changer le format d'une date
 * Exemple d'utilisation :
console.log(changerFormatDate("2015-07-01", "DD/MM/YYYY")); // Affichera : 01/07/2015
console.log(changerFormatDate("2015-07-01", "MM-DD-YYYY")); // Affichera : 07-01-2015
console.log(changerFormatDate("2015-07-01", "dddd, DD MMMM YYYY")); // Affichera : mercredi, 01 juillet 2015
 * @param {*} date 
 * @param {string} formatSouhaite 
 * @returns 
 */
export function changerFormatDate(date, formatSouhaite) {
  const dateObj = new Date(date);
  if (isNaN(dateObj)) {
    return 'Format de date invalide';
  }

  const options = {
    'YYYY-MM-DD': dateObj.toISOString().split('T')[0],
    'DD/MM/YYYY':
      dateObj.getDate().toString().padStart(2, '0') +
      '/' +
      (dateObj.getMonth() + 1).toString().padStart(2, '0') +
      '/' +
      dateObj.getFullYear(),
    'MM-DD-YYYY':
      (dateObj.getMonth() + 1).toString().padStart(2, '0') +
      '-' +
      dateObj.getDate().toString().padStart(2, '0') +
      '-' +
      dateObj.getFullYear(),
    'dddd, DD MMMM YYYY': dateObj.toLocaleDateString('fr-FR', {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    }),
  };

  return options[formatSouhaite] || 'Format non pris en charge';
}

export function generateCustomFilename(prefix = 'ft') {
  const now = new Date();
  const pad = (num) => num.toString().padStart(2, '0');
  const year = now.getFullYear();
  const month = pad(now.getMonth() + 1);
  const day = pad(now.getDate());
  const hour = pad(now.getHours());
  const min = pad(now.getMinutes());
  const sec = pad(now.getSeconds());
  return `${prefix}_${year}${month}${day}${hour}${min}${sec}`;
}
