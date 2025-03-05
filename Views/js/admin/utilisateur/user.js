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
