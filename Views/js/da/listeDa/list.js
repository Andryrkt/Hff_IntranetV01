import { displayOverlay } from '../../utils/spinnerUtils';

document.addEventListener('DOMContentLoaded', function () {
  const designations = document.querySelectorAll('.designation-btn');
  designations.forEach((designation) => {
    designation.addEventListener('click', function () {
      let numeroLigne = this.getAttribute('data-numero-ligne');
      localStorage.setItem('currentTab', numeroLigne);
    });
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
