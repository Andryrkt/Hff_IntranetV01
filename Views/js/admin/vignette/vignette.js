import { initSelect2WithSelectAll } from "../../utils/select2SelectAll";

document.addEventListener("DOMContentLoaded", function () {
  const nom = document.querySelector("#vignette_nom");
  const reference = document.querySelector("#vignette_reference");

  nom.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 100);
  });

  reference.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });

  initSelect2WithSelectAll("#vignette_applications", {
    placeholder: "-- Choisir application(s) associée(s) --",
  });
});
