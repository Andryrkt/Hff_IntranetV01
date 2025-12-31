$(document).ready(function () {
  $("#profil_applications").select2({
    placeholder: "-- Choisir application(s) autorisée(s) --",
    allowClear: true,
    theme: "bootstrap",
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const designation = document.querySelector("#profil_designation");
  const reference = document.querySelector("#profil_reference");

  designation.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 100);
  });

  reference.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });
});
