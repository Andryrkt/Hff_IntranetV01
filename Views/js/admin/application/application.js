$(document).ready(function () {
  $("#application_pages").select2({
    placeholder: "-- Choisir page(s) associée(s) --",
    allowClear: true,
    theme: "bootstrap",
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const nom = document.querySelector("#application_nom");
  const codeApp = document.querySelector("#application_codeApp");

  nom.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 255);
  });

  codeApp.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });
});
