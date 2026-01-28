import { DemandePaiementManager } from "./DemandePaiementManager.js";
import { formaterNombre } from "../utils/formatNumberUtils.js";

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
      montantFacture:
        "api/montant-facture/:numFournisseur/:facturesString/:typeId",
      listeDoc: "api/liste-doc/:numero",
      recupererFichier: "/Hffintranet/api/recuperer-fichier",
      agenceFetch: "agence-fetch/:agenceDebiteur",
    },
  };

  const manager = new DemandePaiementManager(config);
  manager.init();

  const montantTotalCde = document.querySelector(
    "#demande_paiement_da_montantTotalCde",
  );
  const montantDejaPayer = document.querySelector(
    "#demande_paiement_da_montantDejaPaye",
  );
  const montantRestantApayer = document.querySelector(
    "#demande_paiement_da_montantRestantApayer",
  );
  const montantAPayer = document.querySelector(
    "#demande_paiement_da_montantAPayer",
  );
  const poucentageAvance = document.querySelector(
    "#demande_paiement_da_pourcentageAvance",
  );
  const poucentageAPayer = document.querySelector(
    "#demande_paiement_da_pourcentageAPayer",
  );

  poucentageAPayer.addEventListener("input", changeMontant);

  function changeMontant(e) {
    let poucentageAPayerValue = e.target.value;
    let montantAPayerValue = stringEnNumber(montantAPayer.value, " ");
    let montantTotalCdeValue = stringEnNumber(montantTotalCde.value, ".");
    let montantDejaPayerValue = stringEnNumber(montantDejaPayer.value, ".");
    let montantRestantApayerValue = montantRestantApayer.value;
    console.log(
      montantTotalCde.value,
      montantTotalCdeValue,
      montantDejaPayerValue,
      montantAPayerValue,
    );

    //changement de montant à payer
    montantAPayerCalc(poucentageAPayerValue, montantTotalCdeValue);

    // changement de montant restant à payer
    montantRestantApayerCalc(
      montantTotalCdeValue,
      montantDejaPayerValue,
      poucentageAPayerValue,
    );

    // changement de pourcentage des avances
    pourcentageAvenceCalc(
      poucentageAPayerValue,
      montantTotalCdeValue,
      montantDejaPayerValue,
    );
  }

  function montantAPayerCalc(pourcentageAPayerValue, montantTotalCdeValue) {
    montantAPayer.value = formaterNombre(
      (pourcentageAPayerValue / 100) * montantTotalCdeValue,
    );
    return (pourcentageAPayerValue / 100) * montantTotalCdeValue;
  }

  function montantRestantApayerCalc(
    montantTotalCdeValue,
    montantDejaPayerValue,
    pourcentageAPayerValue,
  ) {
    montantRestantApayer.value = formaterNombre(
      montantTotalCdeValue -
        montantDejaPayerValue -
        montantAPayerCalc(pourcentageAPayerValue, montantTotalCdeValue),
    );
  }

  function stringEnNumber(value, separateurMilier) {
    // D'abord, supprimer TOUS les séparateurs de milliers
    if (separateurMilier) {
      const regex = new RegExp(`\\${separateurMilier}`, "g");
      value = value.replace(regex, "");
    }

    // Ensuite, remplacer la virgule décimale par un point
    value = value.replace(",", ".");

    // Convertir en nombre
    return parseFloat(value);
  }

  function pourcentageAvenceCalc(
    pourcentageAPayerValue,
    montantTotalCdeValue,
    montantDejaPayerValue,
  ) {
    poucentageAvance.value =
      (
        ((montantDejaPayerValue +
          montantAPayerCalc(pourcentageAPayerValue, montantTotalCdeValue)) /
          montantTotalCdeValue) *
        100
      ).toFixed(2) +
      " " +
      "%";
  }
});
