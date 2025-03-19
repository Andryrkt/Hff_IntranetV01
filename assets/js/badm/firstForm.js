/** griser le champ n°Parc */
const typeMouvementInput = document.querySelector("#badm_form1_typeMouvement");
const numParcInput = document.querySelector("#badm_form1_numParc");

typeMouvementInput.addEventListener("change", grsierNumParc);

function grsierNumParc() {
  const typeMouvement = typeMouvementInput.value;
  if (typeMouvement === "1") {
    numParcInput.readOnly = true;
  } else {
    numParcInput.readOnly = false;
  }
}
