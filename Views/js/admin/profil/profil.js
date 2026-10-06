import { initSelect2WithSelectAll } from "../../utils/select2SelectAll.js";

document.addEventListener("DOMContentLoaded", function () {
  const designation = document.querySelector("#profil_designation");
  const reference = document.querySelector("#profil_reference");

  designation.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 100);
  });

  reference.addEventListener("input", function () {
    this.value = this.value.toUpperCase().slice(0, 10);
  });

  initSelect2WithSelectAll("#profil_applications", {
    placeholder: "-- Choisir application(s) autorisée(s) --",
  });
});
