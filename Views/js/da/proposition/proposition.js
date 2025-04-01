import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterReference } from './article';
import { autocompleteTheField } from './autocompletion';
import { changeTab, showTab } from './pageNavigation';

document.addEventListener('DOMContentLoaded', function () {
  showTab(); // afficher la page d'article sélectionné par l'utilisateur

  /** Champs */

  // Tous les champs "Qté Dispo"
  document
    .querySelectorAll('[id*="proposition_qte_dispo_"]')
    .forEach((qtedispo) => {
      qtedispo.addEventListener('input', function () {
        qtedispo.value = qtedispo.value.replace(/[^\d]/g, '');
      });
    });
  // Tous les champs "Référence"
  document
    .querySelectorAll('[id*="proposition_reference_"]')
    .forEach((reference) => {
      reference.addEventListener('input', function () {
        reference.value = reference.value.toUpperCase();
      });
      autocompleteTheField(reference, 'reference');
    });
  // Tous les champs "Fournisseur"
  document
    .querySelectorAll('[id*="proposition_fournisseur_"]')
    .forEach((fournisseur) => {
      fournisseur.addEventListener('input', function () {
        fournisseur.value = fournisseur.value.toUpperCase();
      });
      autocompleteTheField(fournisseur, 'fournisseur');
    });
  // Tous les champs "Désignation"
  document
    .querySelectorAll('[id*="proposition_designation_"]')
    .forEach((designation) => {
      designation.addEventListener('input', function () {
        designation.value = designation.value.toUpperCase();
      });
      autocompleteTheField(designation, 'designation');
    });

  /** Boutons */

  // Tous les boutons "Précédent"
  document.querySelectorAll('.prevBtn').forEach((prevBtn) => {
    prevBtn.addEventListener('click', () => changeTab('prev'));
  });
  // Tous les boutons "Suivant"
  document.querySelectorAll('.nextBtn').forEach((nextBtn) => {
    nextBtn.addEventListener('click', () => changeTab('next'));
  });
  // Tous les boutons "Ajouter la référence"
  document.querySelectorAll('[id*="add_line_"]').forEach((addLine) => {
    addLine.addEventListener('click', () => ajouterReference(addLine.id));
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
