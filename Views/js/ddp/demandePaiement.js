import { DemandePaiementManager } from "./DemandePaiementManager.js";

document.addEventListener("DOMContentLoaded", function () {
  const config = {
    selectors: {
      numFrnInput: "#demande_paiement_numeroFournisseur",
      beneficiaireInput: "#demande_paiement_beneficiaire",
      deviseInput: "#demande_paiement_devise",
      modePaiementInput: "#demande_paiement_modePaiement",
      ribFrnInput: "#demande_paiement_ribFournisseur",
      numCommandeInput: "#demande_paiement_numeroCommande",
      numFactureInput: "#demande_paiement_numeroFacture",
      montantInput: "#demande_paiement_montantAPayer",
      suggestionNumFournisseur: "#suggestion-num-fournisseur",
      loaderNumFournisseur: "#loader-num-fournisseur",
      suggestionNomFournisseur: "#suggestion-nom-fournisseur",
      fileList: "#liste_fichiers",
      invoiceTableContainer: "#tableau_facture",
      documentTableContainer: "#tableau_dossier",
      fileInput1: "#demande_paiement_pieceJoint01",
      fileInput2: "#demande_paiement_pieceJoint02",
      fileInput3: "#demande_paiement_pieceJoint03",
      fileInput4: "#demande_paiement_pieceJoint04",
      agenceDebiteurInput: ".agenceDebiteur",
      serviceDebiteurInput: ".serviceDebiteur",
      spinnerService: "#spinner-service",
      serviceContainer: "#service-container",
      contactInput: "#demande_paiement_contact",
    },
    urls: {
      fournisseurs: "api/info-fournisseur-ddp",
      commandes: "api/num-cde-frn/:numFournisseur/:typeId",
      montantCommande: "api/montant-commande/:numCde",
      montantFacture: "api/montant-facture/:numFournisseur/:facturesString/:typeId",
      listeDoc: "api/liste-doc/:numero",
      recupererFichier: "/Hffintranet/api/recuperer-fichier",
      agenceFetch: "agence-fetch/:agenceDebiteur",
    },
  };

  const manager = new DemandePaiementManager(config);
  manager.init();
});
