import { initSelect2WithSelectAll } from "../../utils/select2SelectAll";

document.addEventListener("DOMContentLoaded", function () {
  const nom = document.querySelector("#application_nom");
  const codeApp = document.querySelector("#application_codeApp");

  nom.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 255);
  });

  codeApp.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });

  initSelect2WithSelectAll("#application_pages", {
    placeholder: "-- Choisir page(s) associée(s) --",
  });
});
