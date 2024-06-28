/**
 * CREATION D'EXCEL
 */

const typeDocumentInput = document.querySelector("#dit_search_typeDocument");
const niveauUrgenceInput = document.querySelector("#dit_search_niveauUrgence");
const statutInput = document.querySelector("#dit_search_statut");
const idMaterielInput = document.querySelector("#dit_search_idMateriel");
const interExternInput = document.querySelector("#dit_search_internetExterne");
const dateDemandeDebutInput = document.querySelector("#dit_search_dateDebut");
const dateDemandeFinInput = document.querySelector("#dit_search_dateFin");
const buttonExcelInput = document.querySelector("#excelDit");
buttonExcelInput.addEventListener("click", recherche);

function recherche() {
  const typeDocument = typeDocumentInput.value;
  const niveauUrgence = niveauUrgenceInput.value;
  const statut = statutInput.value;
  const idMateriel = idMaterielInput.value;
  const interExtern = interExternInput.value;
  const dateDemandeDebut = dateDemandeDebutInput.value;
  const dateDemandeFin = dateDemandeFinInput.value;

  let url = "/Hffintranet/dit-excel";

  const data = {
    idMateriel: idMateriel || null,
    typeDocument: typeDocument || null,
    niveauUrgence: niveauUrgence || null,
    statut: statut || null,
    interExtern: interExtern || null,
    dateDebut: dateDemandeDebut || null,
    dateFin: dateDemandeFin || null,
  };

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}
