export const configAgenceService = {
  emetteur: {
    agenceInput: document.querySelector(".agenceEmetteur"),
    serviceInput: document.querySelector(".serviceEmetteur"),
    spinner: document.getElementById("spinner-service-emetteur"),
    container: document.getElementById("service-container-emetteur"),
  },
  debiteur: {
    agenceInput: document.querySelector(".agenceDebiteur"),
    serviceInput: document.querySelector(".serviceDebiteur"),
    spinner: document.getElementById("spinner-service-debiteur"),
    container: document.getElementById("service-container-debiteur"),
  },
};

export const configDocSoumisDwModal = {
  docDansDwModal: document.getElementById("docDansDw"),
  numeroDitInput: document.querySelector("#numeroDit"),
  numDitHiddenInput: document.querySelector("#doc_dans_dw_numeroDit"),
  selecteInput: document.querySelector("#doc_dans_dw_docDansDW"),
  spinnerSelect: document.getElementById("spinner-doc-soumis"),
  selectContainer: document.getElementById("container-doc-soumis"),
};

export const configCloturDit = {
  clotureDit: document.querySelectorAll(".clotureDit"),
  text: {
    title: "Êtes-vous sûr ?",
    text: "Cette action est irréversible",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "OUI",
  },
};
