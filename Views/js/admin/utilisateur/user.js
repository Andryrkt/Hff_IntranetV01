import { initSelect2WithSelectAll } from "../../utils/select2SelectAll";

document.addEventListener("DOMContentLoaded", function () {
  initSelect2WithSelectAll(".selectUser", {
    placeholder: "-- Choisir nom d'utilisateur --",
  });

  initSelect2WithSelectAll(".selectPersonnel", {
    placeholder: "-- Choisir matricule --",
  });

  initSelect2WithSelectAll(".selectProfils", {
    placeholder: "-- Choisir profil(s) --",
  });
});
