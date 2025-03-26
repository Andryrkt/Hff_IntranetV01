import { displayOverlay } from '../../utils/spinnerUtils';
import { mergeCellsTable } from './tableHandler';

document.addEventListener('DOMContentLoaded', function () {
  const designations = document.querySelectorAll('.designation-btn');
  designations.forEach((designation) => {
    designation.addEventListener('click', function () {
      let numeroLigne = this.getAttribute('data-numero-ligne');
      localStorage.setItem('currentTab', numeroLigne);
    });
  });
  mergeCellsTable(0);
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
