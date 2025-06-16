import { displayOverlay } from '../../utils/spinnerUtils';
import { mergeCellsTable } from './tableHandler';
import { configAgenceService } from '../../dit/config/listDitConfig.js';
import { handleAgenceChange } from '../../dit/fonctionUtils/fonctionListDit.js';
import { allowOnlyNumbers } from '../../magasin/utils/inputUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  const designations = document.querySelectorAll('.designation-btn');
  designations.forEach((designation) => {
    designation.addEventListener('click', function () {
      let numeroLigne = this.getAttribute('data-numero-ligne');
      localStorage.setItem('currentTab', numeroLigne);
    });
  });
  mergeCellsTable(1); // fusionne le tableau en fonction de la colonne DA

  /**===========================================================================
   * Configuration des agences et services
   *============================================================================*/

  // Attachement des événements pour les agences
  configAgenceService.emetteur.agenceInput.addEventListener('change', () =>
    handleAgenceChange('emetteur')
  );

  configAgenceService.debiteur.agenceInput.addEventListener('change', () =>
    handleAgenceChange('debiteur')
  );

  /**==================================================
   * valider seulement les chiffres
   *===================================================*/

  const idMaterielInput = document.querySelector('#da_search_idMateriel');
  idMaterielInput.addEventListener('input', () =>
    allowOnlyNumbers(idMaterielInput)
  );

  /**==================================================
   * Configuration sur le modal et le form dans le modal
   *===================================================*/
  const deverouillageModal = document.getElementById(
    'demandeDeverouillageModal'
  );
  const modal = new bootstrap.Modal(deverouillageModal);

  deverouillageModal.addEventListener('show.bs.modal', function (event) {
    const form = deverouillageModal.querySelector('form');
    form.querySelector('textarea').value = ''; // Réinitialiser le champ de texte
  });

  deverouillageModal
    .querySelector('form')
    .addEventListener('submit', function (event) {
      event.preventDefault(); // Empêche l'envoi du formulaire par défaut
      const motif = this.querySelector('textarea').value;

      if (motif) {
        // Logique pour traiter le formulaire
        console.log(
          `Demande de déverrouillage pour la DA avec le motif: ${motif}`
        );
        modal.hide(); // Ferme le modal après traitement
        this.submit(); // Soumet le formulaire si nécessaire
      }
    });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
