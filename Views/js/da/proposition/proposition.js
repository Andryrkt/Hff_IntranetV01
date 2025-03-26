import { displayOverlay } from '../../utils/spinnerUtils';
import { ajouterReference } from './article';
import { changeTab, showTab } from './pageNavigation';

document.addEventListener('DOMContentLoaded', function () {
  const prevBtns = document.querySelectorAll('.prevBtn'); // Bouton Précédent
  const nextBtns = document.querySelectorAll('.nextBtn'); // Bouton Suivant
  const addLines = document.querySelectorAll('[id*="add_line_"]');

  showTab(); // afficher la page d'article sélectionné par l'utilisateur
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
