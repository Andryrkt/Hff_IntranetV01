/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
// Configuration centralisée

import { FetchManager } from "../api/FetchManager";
// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

const config = {
  elements: {
    agenceDebiteurInput: "#planning_search_agenceDebite",
    serviceDebiteurInput: "#planning_search_serviceDebite",
    selectAllCheckbox: "#planning_search_selectAll",
    searchForm: "#planning_search_form", // Ajout de l'ID du formulaire de recherche
  },
  urls: {
    serviceFetch: (agenceDebiteur) =>
      `api/serviceDebiteurPlanning-fetch/${agenceDebiteur}`,
  },
};

// Sélection des éléments du DOM
const agenceDebiteurInput = document.querySelector(
  config.elements.agenceDebiteurInput
);
const serviceDebiteurInput = document.querySelector(
  config.elements.serviceDebiteurInput
);
const searchForm = document.querySelector(config.elements.searchForm);

// Initialisation des checkbox au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  ensureSelectAllCheckbox();
  attachCheckboxEventListeners();
  selectAllCheckboxByDefault();

  // Ajout d'un écouteur pour recalculer après la soumission du formulaire
  searchForm.addEventListener("submit", () => {
    setTimeout(() => {
      ensureSelectAllCheckbox();
      attachCheckboxEventListeners();
      selectAllCheckboxByDefault(); // Recalcule l'état après l'envoi
    }, 100);
  });
});

// Gestionnaire principal pour le changement de l'agence
agenceDebiteurInput.addEventListener("change", handleAgenceChange);

function handleAgenceChange() {
  serviceDebiteurInput.disabled = false;
  // Récupération de l'agence sélectionnée
  const agenceDebiteur =
    agenceDebiteurInput.value === "" ? null : agenceDebiteurInput.value;

  clearServiceCheckboxes();
  removeSelectAllCheckbox();

  if (!agenceDebiteur) {
    // Si aucune agence n'est sélectionnée, on arrête ici
    return;
  }

  // URL pour fetch
  const url = config.urls.serviceFetch(agenceDebiteur);

  // Création et affichage du spinner
  const spinner = createSpinner();
  serviceDebiteurInput.parentElement.appendChild(spinner);

  fetchManager
    .get(url)
    .then((services) => {
      updateServiceCheckboxes(services);
      ensureSelectAllCheckbox();
      attachCheckboxEventListeners();
      selectAllCheckboxByDefault(); // Ensure default selection after updating checkboxes
    })
    .catch((error) => console.error("Error:", error))
    .finally(() => {
      // Suppression du spinner
      spinner.remove();
    });
}

// Fonction pour retirer le bouton "Tout sélectionner"
function removeSelectAllCheckbox() {
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );
  if (selectAllCheckbox) {
    selectAllCheckbox.parentElement.remove();
  }
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

function updateServiceCheckboxes(services) {
  clearServiceCheckboxes();
  addServiceCheckboxes(services);
}

function clearServiceCheckboxes() {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => checkbox.parentElement.remove());
}

function ensureSelectAllCheckbox() {
  let selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );

  if (!selectAllCheckbox) {
    const selectAllDiv = document.createElement("div");
    selectAllDiv.className = "form-check";

    selectAllCheckbox = document.createElement("input");
    selectAllCheckbox.type = "checkbox";
    selectAllCheckbox.id = "planning_search_selectAll";
    selectAllCheckbox.className = "form-check-input";

    const selectAllLabel = document.createElement("label");
    selectAllLabel.htmlFor = selectAllCheckbox.id;
    selectAllLabel.textContent = "Tout sélectionner";
    selectAllLabel.className = "form-check-label";

    selectAllDiv.appendChild(selectAllCheckbox);
    selectAllDiv.appendChild(selectAllLabel);
    serviceDebiteurInput.insertBefore(
      selectAllDiv,
      serviceDebiteurInput.firstChild
    );

    selectAllCheckbox.addEventListener("change", handleSelectAllChange);
  }
}

function handleSelectAllChange(event) {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => {
    checkbox.checked = event.target.checked;
  });
}

function addServiceCheckboxes(services) {
  services.forEach((service, index) => {
    const div = document.createElement("div");
    div.className = "form-check";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.name = "planning_search[serviceDebite][]";
    checkbox.value = service.value;
    checkbox.id = `service_${index}`;
    checkbox.className = "form-check-input";
    checkbox.checked = true; // Set all checkboxes to checked by default

    const label = document.createElement("label");
    label.htmlFor = checkbox.id;
    label.textContent = service.text;
    label.className = "form-check-label";

    div.appendChild(checkbox);
    div.appendChild(label);
    serviceDebiteurInput.appendChild(div);
  });
}

function attachCheckboxEventListeners() {
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  serviceCheckboxes.forEach((checkbox) => {
    checkbox.removeEventListener("change", handleServiceCheckboxChange);
    checkbox.addEventListener("change", handleServiceCheckboxChange);
  });
}

function handleServiceCheckboxChange() {
  const allCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );

  const allChecked = Array.from(allCheckboxes).every(
    (checkbox) => checkbox.checked
  );

  selectAllCheckbox.checked = allChecked;
}

function selectAllCheckboxByDefault() {
  const selectAllCheckbox = document.querySelector(
    config.elements.selectAllCheckbox
  );
  const serviceCheckboxes = document.querySelectorAll(
    'input[name="planning_search[serviceDebite][]"]'
  );

  if (serviceCheckboxes.length > 0) {
    const allChecked = Array.from(serviceCheckboxes).every(
      (checkbox) => checkbox.checked
    );

    selectAllCheckbox.checked = allChecked;
  } else {
    selectAllCheckbox.checked = false;
  }
}
