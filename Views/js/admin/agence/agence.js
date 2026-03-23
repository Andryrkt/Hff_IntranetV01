import { initSelect2WithSelectAll } from "../../utils/select2SelectAll.js";

document.addEventListener("DOMContentLoaded", function () {
  initSelect2WithSelectAll("#agence_services", {
    placeholder: "-- Choisir service(s) liée(s) --",
  });
});
