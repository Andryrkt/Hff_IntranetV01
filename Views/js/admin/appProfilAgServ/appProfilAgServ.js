$(document).ready(function () {
  $("#application_profil_agence_service_applicationProfil").select2({
    placeholder: "-- Choisir une combinaison profil - application --",
    allowClear: true,
    theme: "bootstrap",
  });
  $("#application_profil_agence_service_agenceServiceIds").select2({
    placeholder: "-- Choisir des agences - services --",
    allowClear: true,
    theme: "bootstrap",
  });
});
