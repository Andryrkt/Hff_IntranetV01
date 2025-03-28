import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterReference } from './article';
import { autocompleteTheField } from './autocompletion';
import { changeTab, showTab } from './pageNavigation';

document.addEventListener('DOMContentLoaded', function () {
  const prevBtns = document.querySelectorAll('.prevBtn'); // Tous les boutons "Précédent"
  const nextBtns = document.querySelectorAll('.nextBtn'); // Tous les boutons "Suivant"
  const addLines = document.querySelectorAll('[id*="add_line_"]'); // Tous les boutons "Ajouter la référence"
  const references = document.querySelectorAll(
    '[id*="proposition_reference_"]'
  ); // Tous les champs "Référence"
  const fournisseurs = document.querySelectorAll(
    '[id*="proposition_fournisseur_"]'
  ); // Tous les champs "Fournisseur"
  const designations = document.querySelectorAll(
    '[id*="proposition_designation_"]'
  ); // Tous les champs "Désignation"
  const qtedispos = document.querySelectorAll('[id*="proposition_qte_dispo_"]'); // Tous les champs "Qté Dispo"

  showTab(); // afficher la page d'article sélectionné par l'utilisateur

  /** Champs */
  qtedispos.forEach((qtedispo) => {
    qtedispo.addEventListener('input', function () {
      qtedispo.value = qtedispo.value.replace(/[^\d]/g, '');
    });
  });
  references.forEach((reference) => {
    reference.addEventListener('input', function () {
      reference.value = reference.value.toUpperCase();
    });
    autocompleteTheField(reference, 'reference');
  });
  fournisseurs.forEach((fournisseur) => {
    fournisseur.addEventListener('input', function () {
      fournisseur.value = fournisseur.value.toUpperCase();
    });
    autocompleteTheField(fournisseur, 'fournisseur');
  });
  designations.forEach((designation) => {
    designation.addEventListener('input', function () {
      designation.value = designation.value.toUpperCase();
    });
    autocompleteTheField(designation, 'designation');
  });

  /** Boutons */
  prevBtns.forEach((prevBtn) => {
    prevBtn.addEventListener('click', () => changeTab('prev'));
  });
  nextBtns.forEach((nextBtn) => {
    nextBtn.addEventListener('click', () => changeTab('next'));
  });
  addLines.forEach((addLine) => {
    addLine.addEventListener('click', () => ajouterReference(addLine.id));
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
