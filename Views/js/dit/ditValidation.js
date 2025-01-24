document.addEventListener("DOMContentLoaded", function () {
  const idServiceIntervenant = document.querySelector(
    "#dit_validation_idServiceIntervenant"
  );
  const codeSection = document.querySelector("#dit_validation_codeSection");
  const validerBtn = document.querySelector("#btn_valider");
  const refuserBtn = document.querySelector("#btn_refuser");

  refuserBtn.addEventListener("click", function () {
    idServiceIntervenant.removeAttribute("required");
    codeSection.removeAttribute("required");
  });

  validerBtn.addEventListener("click", function () {
    idServiceIntervenant.setAttribute("required", "required");
    codeSection.setAttribute("required", "required");
  });
});
