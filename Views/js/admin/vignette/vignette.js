$(document).ready(function () {
  $("#vignette_applications").select2({
    placeholder: "-- Choisir application(s) associée(s) --",
    allowClear: true,
    theme: "bootstrap",
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const nom = document.querySelector("#vignette_nom");
  const reference = document.querySelector("#vignette_reference");

  nom.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 100);
  });

  reference.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });
});
