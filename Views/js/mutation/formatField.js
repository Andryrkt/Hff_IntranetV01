/**
 * CHAMP A METTRE EN MAJUSCULE
 */
const allToUpperCaseFieldId = [
  'mutation_form_lieuMutation',
  'mutation_form_client',
  'mutation_form_motifMutation',
  'mutation_form_motifAutresDepense1',
  'mutation_form_motifAutresDepense2',
];

export function formatFieldsToUppercase() {
  allToUpperCaseFieldId.forEach((fieldId) => {
    let field = document.getElementById(fieldId);
    field.addEventListener('input', function () {
      this.value = this.value.toUpperCase();
    });
  });
}
