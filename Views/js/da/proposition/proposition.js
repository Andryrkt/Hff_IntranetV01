import { displayOverlay } from '../../utils/spinnerUtils';
import { changeTab, showTab } from './pageNavigation';

document.addEventListener('DOMContentLoaded', function () {
  const prevBtns = document.querySelectorAll('.prevBtn'); // Bouton Précédent
  const nextBtns = document.querySelectorAll('.nextBtn'); // Bouton Suivant

  showTab();
  prevBtns.forEach((prevBtn) => {
    prevBtn.addEventListener('click', () => changeTab('prev'));
  });
  nextBtns.forEach((nextBtn) => {
    nextBtn.addEventListener('click', () => changeTab('next'));
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
