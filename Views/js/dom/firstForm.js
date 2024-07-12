document.addEventListener("DOMContentLoaded", (event) => {
  const nom = document.querySelector("#dom_form1_nom");
  const prenom = document.querySelector("#dom_form1_prenom");
  const cin = document.querySelector("#dom_form1_cin");
  const salarier = document.querySelector("#dom_form1_salarie");

  function toggleFields() {
    if (salarier.value === "TEMPORAIRE") {
      nom.parentElement.style.display = "block";
      prenom.parentElement.style.display = "block";
      cin.parentElement.style.display = "block";
    } else {
      nom.parentElement.style.display = "none";
      prenom.parentElement.style.display = "none";
      cin.parentElement.style.display = "none";
    }
  }

  salarier.addEventListener("change", toggleFields);
  toggleFields(); // Initial call to set the correct state
});
