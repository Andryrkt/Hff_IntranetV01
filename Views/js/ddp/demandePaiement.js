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
      montantFacture:
        "api/montant-facture/:numFournisseur/:facturesString/:typeId",
      listeDoc: "api/liste-doc/:numero",
      recupererFichier: "/Hffintranet/api/recuperer-fichier",
      agenceFetch: "agence-fetch/:agenceDebiteur",
    },
  };

  const manager = new DemandePaiementManager(config);
  manager.init();
});

// Formatage automatique du RIB
document.addEventListener("DOMContentLoaded", function () {
  const ribField = document.querySelector('[data-format-rib="true"]');

  if (ribField) {
    // Restreindre la saisie aux chiffres et espaces uniquement
    ribField.addEventListener("keydown", function (e) {
      const allowedKeys = [
        "Backspace",
        "Delete",
        "Tab",
        "Escape",
        "Enter",
        "ArrowLeft",
        "ArrowRight",
        "Home",
        "End",
      ];

      if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey) {
        return;
      }

      // Interdire l'espace au tout début
      if (e.key === " " && e.target.selectionStart === 0) {
        e.preventDefault();
        return;
      }

      // Autoriser l'espace (si pas au début) et les chiffres uniquement
      if (e.key === " " || /^[0-9]$/.test(e.key)) {
        return;
      }

      e.preventDefault();
    });

    ribField.addEventListener("input", function (e) {
      let cursorPosition = e.target.selectionStart;
      let value = e.target.value.replace(/[^0-9]/g, ""); // Garde uniquement les chiffres pour le formatage

      let formatted = "";
      if (value.length > 0) {
        // 5 premiers chiffres
        formatted += value.substring(0, Math.min(5, value.length));

        // 5 chiffres suivants
        if (value.length > 5) {
          formatted += " " + value.substring(5, Math.min(10, value.length));
        }

        // 11 chiffres suivants
        if (value.length > 10) {
          formatted += " " + value.substring(10, Math.min(21, value.length));
        }

        // 2 derniers chiffres
        if (value.length > 21) {
          formatted += " " + value.substring(21, Math.min(23, value.length));
        }

        // Gérer le décalage du curseur si un espace a été inséré
        const parts = [5, 11, 23]; // Positions où des espaces sont insérés (index 5, 11, 23)
        if (
          parts.includes(cursorPosition) &&
          e.inputType !== "deleteContentBackward"
        ) {
          cursorPosition++;
        }

        e.target.value = formatted;
        e.target.setSelectionRange(cursorPosition, cursorPosition);
      }
    });

    // Formater la valeur initiale si elle existe
    if (ribField.value) {
      const event = new Event("input", { bubbles: true });
      ribField.dispatchEvent(event);
    }
  }
});
