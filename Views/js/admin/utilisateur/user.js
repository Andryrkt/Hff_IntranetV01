import { initSelect2WithSelectAll } from "../../utils/select2SelectAll";

document.addEventListener("DOMContentLoaded", function () {
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

  initSelect2WithSelectAll(".selectProfils", {
    placeholder: "-- Choisir profil(s) --",
  });
});
