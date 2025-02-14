/**
 * Methode pour enlever une partie d'une texte qu'on renseigne dans le tableau motsExactes, commencePAr et contient
 * @param {string} texte
 * @param {array} motsExactes
 * @param {array} commencePar
 * @param {array} contient
 * @returns
 */
export function enleverPartiesTexte(texte, motsExactes = [], commencePar = []) {
  // Supprimer les morceaux exacts
  motsExactes.forEach((mot) => {
    const regexMot = new RegExp(mot.replace(/\\/g, "\\\\"), "g"); // On échappe les antislashs pour la regex
    texte = texte.replace(regexMot, "");
  });

  // Supprimer les dossiers qui commencent par certains motifs
  commencePar.forEach((debut) => {
    const regexDebut = new RegExp(
      `${debut.replace(/\\/g, "\\\\")}[^\\\\]*`,
      "g"
    ); // Prend le dossier complet
    texte = texte.replace(regexDebut, "");
  });

  // Nettoyer les doubles slashs devenus inutiles
  texte = texte.replace(/\\+/g, "\\");

  // Retourner seulement le nom du fichier
  const segments = texte.split("\\");
  return segments[segments.length - 1]; // Dernier segment après split
}
