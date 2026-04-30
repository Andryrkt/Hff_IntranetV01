import { updateDropdown } from "../utils/selectionHandler";

const agenceEmetteurInput = document.querySelector(
  "#mutation_form_agenceEmetteur"
);
const serviceEmetteurInput = document.querySelector(
  "#mutation_form_serviceEmetteur"
);
const agenceDebiteurSelect = document.querySelector(".agenceDebiteur");
const serviceDebiteurSelect = document.querySelector(".serviceDebiteur");
const placeholder = " -- Choisir une service débiteur -- ";
const spinnerElement = document.querySelector("#spinner-service-debiteur");
const containerElement = document.querySelector("#service-debiteur-container");
const errorMessage = document.querySelectorAll(".error-message")[0];

export function handleService() {
  agenceDebiteurSelect?.addEventListener("change", function () {
    if (agenceDebiteurSelect.value !== "") {
      updateDropdown(
        serviceDebiteurSelect,
        `api/agence-fetch/${agenceDebiteurSelect.value}`,
        placeholder,
        spinnerElement,
        containerElement
      );
    }
  });
  serviceDebiteurSelect?.addEventListener("change", function () {
    handleAgenceService();
  });
}

export function handleAgenceService() {
  let agenceEmetteurText = agenceEmetteurInput.value;
  let serviceEmetteurText = serviceEmetteurInput.value;
  let agenceDebiteurText = agenceDebiteurSelect.selectedIndex
    ? agenceDebiteurSelect.options[agenceDebiteurSelect.selectedIndex].text
    : "";
  let serviceDebiteurText = serviceDebiteurSelect.selectedIndex
    ? serviceDebiteurSelect.options[serviceDebiteurSelect.selectedIndex].text
    : "";

  if (
    agenceEmetteurText === agenceDebiteurText &&
    serviceEmetteurText === serviceDebiteurText
  ) {
    errorMessage.textContent =
      "L'agence et service de destination ne peuvent pas être même que ceux d'origine. Veuillez les changer s'il vous plaît!";
    errorMessage.classList.remove("d-none");
  } else {
    errorMessage.textContent = "";
    errorMessage.classList.add("d-none");
  }
}
