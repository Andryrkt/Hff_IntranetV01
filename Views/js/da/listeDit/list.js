import { displayOverlay } from '../../utils/spinnerUtils';

document.addEventListener('DOMContentLoaded', function () {
  const checkboxes = document.querySelectorAll('.checkbox');
  const suivant = document.getElementById('suivant');
  checkboxes.forEach((checkbox) => {
    // Désélectionne les autres checkboxes pour garantir qu'un seul est coché
    checkbox.addEventListener('change', function () {
      checkboxes.forEach((cb) => {
        if (cb !== this) cb.checked = false;
      });
      // Vérifie si au moins un checkbox est coché
    });
  });
  suivant.addEventListener('click', function () {
    let checkedValue = [...checkboxes].find((cb) => cb.checked)?.value || '';
    if (checkedValue === '') {
      alert('Veuillez sélectionner un DIT');
    } else {
      let url = suivant
        .getAttribute('data-uri')
        .replace('__id__', checkedValue);
      window.location.href = url;
    }
  });
});

window.addEventListener('load', () => {
  displayOverlay(false);
});
