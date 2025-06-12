import { allowOnlyNumbers, limitInputLength } from '../utils/inputUtils.js';
/**
 * SELECTE 2/ permet de faire une recherche sur le select
 */
$(document).ready(function () {
  $('.selectUser').select2({
    placeholder: "-- Choisir un nom d'utilisateur --",
    allowClear: true,
    theme: 'bootstrap',
  });

  $('.superieurs').select2({
    placeholder: '-- Choisir une superieur--',
    allowClear: true,
    theme: 'bootstrap',
    width: '100%',
  });
});

/**
 * Controller le donner entrer dans le n° telephone
 */
const numTelInput = document.querySelector('#user_numTel');
numTelInput.addEventListener('input', () => {
  allowOnlyNumbers(numTelInput);
  limitInputLength(numTelInput, 9);
});
