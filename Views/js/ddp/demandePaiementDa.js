import { DemandePaiementManager } from "./DemandePaiementManager.js";

document.addEventListener("DOMContentLoaded", function () {
  const config = {
    selectors: {
      // Sélecteurs spécifiques au formulaire 'demande_paiement_da'
      numFrnInput: "#demande_paiement_da_numeroFournisseur",
      beneficiaireInput: "#demande_paiement_da_beneficiaire",
      deviseInput: "#demande_paiement_da_devise",
      modePaiementInput: "#demande_paiement_da_modePaiement",
      ribFrnInput: "#demande_paiement_da_ribFournisseur",
      numCommandeInput: "#demande_paiement_da_numeroCommande",
      numFactureInput: "#demande_paiement_da_numeroFacture",
      montantInput: "#demande_paiement_da_montantAPayer",
      contactInput: "#demande_paiement_da_contact",
      fileInput1: "#demande_paiement_da_pieceJoint01",
      fileInput2: "#demande_paiement_da_pieceJoint02",
      fileInput3: "#demande_paiement_da_pieceJoint03",
      fileInput4: "#demande_paiement_da_pieceJoint04",

      // Sélecteurs d'éléments de l'interface (probablement inchangés)
      suggestionNumFournisseur: "#suggestion-num-fournisseur",
      loaderNumFournisseur: "#loader-num-fournisseur",
      suggestionNomFournisseur: "#suggestion-nom-fournisseur",
      fileList: "#liste_fichiers",
      invoiceTableContainer: "#tableau_facture",
      documentTableContainer: "#tableau_dossier",
      spinnerService: "#spinner-service-debiteur",
      serviceContainer: "#service-container-debiteur",
      
      // Sélecteurs de classe (probablement inchangés)
      agenceDebiteurInput: ".agenceDebiteur",
      serviceDebiteurInput: ".serviceDebiteur",
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
