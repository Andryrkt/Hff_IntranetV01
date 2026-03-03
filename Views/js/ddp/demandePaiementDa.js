import { DemandePaiementManager } from "./DemandePaiementManager.js";
import { formaterNombre } from "../utils/formatNumberUtils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";

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
  let lastValidPourcentageAPayer = 0; // Pour stocker la dernière valeur valide

  const poucentageAPayer = document.querySelector(
    "#demande_paiement_da_pourcentageAPayer",
  );

  poucentageAPayer.addEventListener("input", changeMontant);

  function validatePourcentage(value) {
    // Permettre à l'utilisateur d'effacer complètement le champ
    if (value.trim() === "") {
      return true;
    }

    // Remplacer la virgule par un point pour une validation correcte avec les décimaux
    const valueWithDot = value.replace(",", ".");
    const numValue = parseFloat(valueWithDot);

    if (isNaN(numValue) || numValue < 0 || numValue > 100) {
      return false;
    }
    return true;
  }

  function changeMontant(e) {
    let currentInput = e.target;
    let poucentageAPayerValue = currentInput.value;

    if (!validatePourcentage(poucentageAPayerValue)) {
      alert("Veuillez entrer un pourcentage valide entre 0 et 100.");
      currentInput.value = lastValidPourcentageAPayer; // Rétablit la dernière valeur valide
      return; // Arrête l'exécution si la validation échoue
    }

    lastValidPourcentageAPayer = poucentageAPayerValue; // Met à jour la dernière valeur valide

    let montantAPayerValue = stringEnNumber(montantAPayer.value, " ");
    let montantTotalCdeValue = stringEnNumber(montantTotalCde.value, ".");
    let montantDejaPayerValue = stringEnNumber(montantDejaPayer.value, ".");
    // let montantRestantApayerValue = montantRestantApayer.value;
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

  // File viewer logic
  const fileLinks = document.querySelectorAll(".view-file-link");

  fileLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const fileUrl = this.getAttribute("data-url");

      const previewTabItem = document.getElementById("pj-preview-tab-item");
      const previewTab = document.getElementById("pj-preview-tab");
      const previewPane = document.getElementById("pj-preview-pane");

      if (previewTabItem && previewPane && fileUrl) {
        // Make tab visible
        previewTabItem.classList.remove("d-none");

        // Create iframe
        const iframe = document.createElement("iframe");
        iframe.src = fileUrl;
        iframe.style.width = "100%";
        iframe.style.height = "80vh";
        iframe.style.border = "none";

        // Add iframe to pane
        previewPane.innerHTML = "";
        previewPane.appendChild(iframe);

        // Activate the new tab
        const tab = new bootstrap.Tab(previewTab);
        tab.show();
      }
    });
  });

  document.querySelectorAll(".remove-pj-file").forEach((button) => {
    button.addEventListener("click", function () {
      this.closest("li.file-item-pj").remove();
    });
  });
});

// Formatage automatique du RIB
document.addEventListener("DOMContentLoaded", function () {
  const ribField = document.querySelector('[data-format-rib="true"]');

  if (ribField) {
    // Créer dynamiquement l'élément pour le message d'erreur
    const errorDiv = document.createElement("div");
    errorDiv.className = "rib-error-message"; // Classe pour le style CSS
    errorDiv.style.color = "#dc3545"; // Rouge Bootstrap
    errorDiv.style.fontSize = "0.875em";
    errorDiv.style.marginTop = "5px";
    errorDiv.style.display = "none";

    // Insérer le message d'erreur juste après le champ RIB
    ribField.parentNode.insertBefore(errorDiv, ribField.nextSibling);

    // Fonction de validation de la longueur du RIB
    function validateRIBLenght(value) {
      const numbersCaractere = value;

      if (numbersCaractere.length === 26 || numbersCaractere.length === 0) {
        // Pas d'erreur si le champ est vide ou si 26 caractères
        errorDiv.style.display = "none";
        ribField.setCustomValidity(""); // Valide pour le formulaire HTML5
        return true;
      } else {
        errorDiv.textContent = `Le RIB doit contenir 26 caractères (actuellement ${numbersCaractere.length})`;
        errorDiv.style.display = "block";
        ribField.setCustomValidity("RIB incomplet"); // Invalide pour le formulaire HTML5
        return false;
      }
    }

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

      // Limiter à 26 chiffres maximum
      if (value.length > 26) {
        value = value.substring(0, 26);
        // Ajuster la position du curseur si nécessaire
        cursorPosition = Math.min(cursorPosition, value.length);
      }

      let formatted = "";
      if (value.length > 0) {
        // 5 premiers chiffres (Banque)
        formatted += value.substring(0, Math.min(5, value.length));

        // 5 chiffres suivants (Guichet)
        if (value.length > 5) {
          formatted += " " + value.substring(5, Math.min(10, value.length));
        }

        // 11 chiffres suivants (Compte)
        if (value.length > 10) {
          formatted += " " + value.substring(10, Math.min(21, value.length));
        }

        // 2 derniers chiffres (Clé)
        if (value.length > 21) {
          formatted += " " + value.substring(21, Math.min(23, value.length));
        }

        e.target.value = formatted;

        // Ajuster la position du curseur
        const spacePositions = [5, 11, 23]; // Positions où des espaces sont insérés
        let spacesBeforeCursor = spacePositions.filter(
          (pos) => pos < cursorPosition,
        ).length;

        if (e.inputType !== "deleteContentBackward") {
          cursorPosition += spacesBeforeCursor;
        }

        // S'assurer que le curseur reste dans les limites
        cursorPosition = Math.min(cursorPosition, formatted.length);
        e.target.setSelectionRange(cursorPosition, cursorPosition);
      } else {
        e.target.value = "";
      }

      // Valider la longueur après formatage
      validateRIBLenght(e.target.value);
    });

    // Validation lors de la perte de focus (blur)
    ribField.addEventListener("blur", function (e) {
      validateRIBLenght(e.target.value);
    });

    // Formater et valider la valeur initiale si elle existe
    if (ribField.value) {
      const event = new Event("input", { bubbles: true });
      ribField.dispatchEvent(event);
    }

    // Validation supplémentaire avant soumission du formulaire
    const form = ribField.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        if (!validateRIBLenght(ribField.value)) {
          e.preventDefault(); // Empêcher la soumission du formulaire
          ribField.focus(); // Mettre le focus sur le champ RIB

          // Optionnel : faire défiler la page jusqu'au champ
          ribField.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      });
    }
  }
});

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();
