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

  erreur.innerHTML = "";
  let url = "/Hffintranet/fetch-materiel";

  if (idMateriel) {
    url += `/${idMateriel}`;
  } else {
    url += "/0"; // Ajoutez un slash pour éviter les erreurs de format d'URL
  }

  if (numParc) {
    url += `/${numParc}`;
  } else if (!idMateriel) {
    url += "/0"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
  }

  if (numSerie) {
    url += `/${numSerie}`;
  } else if (!numParc && !idMateriel) {
    url += "/"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
  }
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
    })
    .catch((error) => {
      if (error instanceof SyntaxError) {
        erreur.innerHTML =
          "Erreur : l'information du matériel n'est pas dans la base de données.";
      } else {
        console.error("Error:", error);
        erreur.innerHTML = "Erreur : " + error.message;
      }
    });
}

function InfoMateriel() {
  const condition =
    (idMateriel !== "" && idMateriel !== null && idMateriel !== undefined) ||
    (numParc !== "" && numParc !== null && numParc !== undefined) ||
    (numSerie !== "" && numSerie !== null && numSerie !== undefined);
  if (condition) {
    erreur.innerHTML = "";
    let url = "/Hffintranet/fetch-materiel";

    if (idMateriel) {
      url += `/${idMateriel}`;
    } else {
      url += "/0"; // Ajoutez un slash pour éviter les erreurs de format d'URL
    }

    if (numParc) {
      url += `/${numParc}`;
    } else if (!idMateriel) {
      url += "/0"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
    }

    if (numSerie) {
      url += `/${numSerie}`;
    } else if (!numParc && !idMateriel) {
      url += "/"; // Ajoutez un slash si aucun idMateriel et numParc n'est fourni
    }
    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
      })
      .catch((error) => {
        if (error instanceof SyntaxError) {
          erreur.innerHTML =
            "Erreur : l'information du matériel n'est pas dans la base de données.";
        } else {
          console.error("Error:", error);
          erreur.innerHTML = "Erreur : " + error.message;
        }
      });
  } else {
    erreur.innerHTML = "veuillez completer l'un des champs ";
  }
}
