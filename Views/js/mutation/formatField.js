/**
 * CHAMP A METTRE EN MAJUSCULE
 */
const allToUpperCaseFieldId = [
  { sliceEnd: 35, fieldId: 'mutation_form_lieuMutation' },
  { sliceEnd: 70, fieldId: 'mutation_form_client' },
  { sliceEnd: 70, fieldId: 'mutation_form_motifMutation' },
  { sliceEnd: 45, fieldId: 'mutation_form_motifAutresDepense1' },
  { sliceEnd: 45, fieldId: 'mutation_form_motifAutresDepense2' },
];

export function formatFieldsToUppercaseAndSlice() {
  allToUpperCaseFieldId.forEach(({ sliceEnd, fieldId }) => {
    let field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('input', function () {
        this.value = this.value.toUpperCase().slice(0, sliceEnd);
      });
    }
  });
}
