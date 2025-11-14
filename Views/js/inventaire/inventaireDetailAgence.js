import { FetchManager } from "../api/FetchManager";
// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();
const config = {
  elements: {
    agenceInput: "#detail_inventaire_search_agence",
    inventaireDispo: "#detail_inventaire_search_InventaireDispo",
    dateD: "#detail_inventaire_search_dateDebut",
    dateF: "#detail_inventaire_search_dateFin",
    selectAllCheckbox: "#detailInventaire_search_service_all",
    searchForm: "#detailInvenatire_search_form",
  },
  urls: {
    inventaireFetch: (agence, dateD, dateF) =>
      `listeInventaireDispo-fetch/${agence}/${dateD}/${dateF}`,
  },
};

const agenceInput = document.querySelector(config.elements.agenceInput);
const inventaireDispo = document.querySelector(config.elements.inventaireDispo);
const dateD = document.querySelector(config.elements.dateD);
const dateF = document.querySelector(config.elements.dateF);
const checkAll = document.getElementById("detailInventaire_search_service_all");
const allInputCheckbox = document.querySelectorAll(".form-check-input");
const buttonSend = document.getElementById('btn_search');

buttonSend.addEventListener("click",()=>{
  verifierCheckboxes()
});

checkAll.addEventListener("click", (afficherTous));
dateD.addEventListener("change", () => {
  dataInventaireDispo();
});
dateF.addEventListener("change", () => {
  dataInventaireDispo();
});
agenceInput.addEventListener("change",()=>{
  dataInventaireDispo();
})

function dataInventaireDispo() {
  const agence = agenceInput.value;
  const dateDebut = dateD.value;
  const dateFin = dateF.value;

  console.log(agence);
  console.log(dateDebut);
  console.log(dateFin);
  
  inventaireDispo.innerHTML = "";
  if (agence === "" || dateDebut === "" || dateFin === "" ) return;
  
  const spinner = createSpinner();
  inventaireDispo.parentElement.appendChild(spinner);
  console.log(agence);
  console.log(dateDebut);
  console.log(dateFin);
  const url = config.urls.inventaireFetch(agence, dateDebut, dateFin);
  fetchManager
    .get(url)
    .then((inventDispo) => {
      console.log(inventDispo);
      console.log(inventDispo.length !== 0);

      if (inventDispo.length !== 0) {
        let Html = "";
        inventDispo.forEach((el) => {
          Html += `<div class = 'form-check'> 
        <input type="checkbox" id="detail_inventaire_search_InventaireDispo_${el.id}" name="detail_inventaire_search[InventaireDispo][]" class="form-check-input" value="${el.value}">
            <label class="form-check-label" for="detail_inventaire_search_InventaireDispo_${el.id}">${el.label} </label>
            </div>`;
        });
        console.log(Html);

        inventaireDispo.innerHTML = Html;

        const allInputCheckbox = document.querySelectorAll(".form-check-input");
        checkAll.addEventListener("click", () =>
          checkAllCheckbox(allInputCheckbox)
        );
      }
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => {
      // Suppression du spinner
      spinner.remove();
    });
    // checkAll.checked = false
}

/// Fonction pour créer le spinner HTML avec CSS intégré
function createSpinner() {
  // Conteneur du spinner
  const spinnerContainer = document.createElement("div");
  spinnerContainer.id = "serviceSpinner";
  spinnerContainer.style.display = "flex";
  spinnerContainer.style.justifyContent = "center";
  spinnerContainer.style.alignItems = "center";
  spinnerContainer.style.margin = "20px 0";

  // Spinner
  const spinner = document.createElement("div");
  spinner.className = "spinner-border";
  spinner.role = "status";
  spinner.style.width = "3rem";
  spinner.style.height = "3rem";
  spinner.style.border = "0.25em solid #ccc";
  spinner.style.borderTop = "0.25em solid #000";
  spinner.style.borderRadius = "50%";
  spinner.style.animation = "spin 0.8s linear infinite";

  // Texte pour les lecteurs d'écran (optionnel)
  const spinnerText = document.createElement("span");
  spinnerText.className = "sr-only";
  spinnerText.textContent = "Chargement...";

  spinner.appendChild(spinnerText);
  spinnerContainer.appendChild(spinner);

  // Ajout des styles d'animation au document (si nécessaire)
  const style = document.createElement("style");
  style.textContent = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
  document.head.appendChild(style);

  return spinnerContainer;
}

function afficherTous() {
  // console.log(allInputCheckbox);

  let afficherTous = true;
  for (const inputCheckbox of allInputCheckbox) {
    if (inputCheckbox.checked) {
      afficherTous = false;
      break;
    }
  }

  if (afficherTous) {
    checkAllCheckbox(allInputCheckbox, true);
  }

  checkAll.addEventListener("click", () =>
    checkAllCheckbox(allInputCheckbox, false)
  );
 
}
function checkAllCheckbox(allCheckboxes, checked = false) {
  allCheckboxes.forEach((inputCheckbox) => {
    checkAll.checked = checked ? true : checkAll.checked;
    inputCheckbox.checked = checkAll.checked;
  });
}
function verifierCheckboxes() {
  let auMoinsUneCochee = false;

  allInputCheckbox.forEach((checkbox) => {
    if (checkbox.checked) {
      auMoinsUneCochee = true;
    }
  });

  if (!auMoinsUneCochee) {
    Swal.fire("Merci de cocher un inventaire au moins!");
  } 
}
