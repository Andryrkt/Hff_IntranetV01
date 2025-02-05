/**
 * Fonction pour afficher la date de replanification du ticket
 *
 * @param {Date} dateDebut Date de début pour la replanification
 * @param {Date} dateFin Date de fin pour la replanification
 */
export function afficherDateReplanification(dateDebut, dateFin) {
  if (formatDatePartielDate(dateDebut) === formatDatePartielDate(dateFin)) {
    return `<strong>${formatDatePartielDate(
      dateDebut
    )}</strong>, dans le créneau horaire de <strong>${formatDatePartielHeure(
      dateDebut
    )}</strong> à <strong>${formatDatePartielHeure(dateFin)}</strong>`;
  } else {
    return `<strong>${formatDateComplet(
      dateDebut
    )}</strong> jusqu'au <strong>${formatDateComplet(dateFin)}</strong>`;
  }
}

/**
 * Fonction pour formater une date en d/m/Y H:i
 *
 * @param {Date} date Date à formater
 */
export function formatDateComplet(date) {
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });
}

/**
 * Fonction pour formater une date en d/m/Y
 *
 * @param {Date} date Date à formater
 */
export function formatDatePartielDate(date) {
  return date.toLocaleDateString('fr-FR');
}

/**
 * Fonction pour formater une date en H:i
 *
 * @param {Date} date Date à formater
 */
export function formatDatePartielHeure(date) {
  return date.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });
}
