/**
 * SELECTE 2/ permet de faire une recherche sur le select
 */
$(document).ready(function () {
  $(".selectUser").select2({
    placeholder: "-- Choisir nom d'utilisateur --",
    allowClear: true,
    theme: "bootstrap",
  });

  $(".selectPersonnel").select2({
    placeholder: "-- Choisir matricule --",
    allowClear: true,
    theme: "bootstrap",
  });

  $(".selectProfils").select2({
    placeholder: "-- Choisir profil(s) --",
    allowClear: true,
    theme: "bootstrap",
  });
});
