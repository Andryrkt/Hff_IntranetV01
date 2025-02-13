import { FetchManager } from "../api/FetchManager.js";
import { initializeFileHandlers } from "../utils/file_upload_Utils.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager("/Hffintranet");

  let preloadeFrnData = [];

  const numFrnInput = document.querySelector(
    "#demande_paiement_numeroFournisseur"
  );
  const nomFrnInput = document.querySelector("#demande_paiement_beneficiaire");
  const deviseInput = document.querySelector("#demande_paiement_devise");
  const modePaiementInput = document.querySelector(
    "#demande_paiement_modePaiement"
  );
  const ribInput = document.querySelector("#demande_paiement_ribFournisseur");
  const suggestionContainerNum = document.querySelector(
    "#suggestion-num-fournisseur"
  );
  const suggestionContainerNom = document.querySelector(
    "#suggestion-nom-fournisseur"
  );

  async function fetchListeFournisseur(endpoint) {
    try {
      const fornisseurs = await fetchManager.get(endpoint);
      preloadeFrnData = fornisseurs;
      // console.log("Données récupérées:", fornisseurs);
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des données:",
        error.message
      );
    }
  }

  const endpoint = "api/info-fournisseur-ddp";
  fetchListeFournisseur(endpoint);

  numFrnInput.addEventListener("input", filtrerLesDonnerNum);
  nomFrnInput.addEventListener("input", filtrerLesDonnerNom);

  /**
   * Methode permet de filtrer les donner selon les donnée saisi dans l'input
   */
  function filtrerLesDonnerNum() {
    const numFrn = numFrnInput.value.trim();

    // Si l'input est vide, efface les suggestions et arrête l'exécution
    if (numFrn === "") {
      suggestionContainerNum.innerHTML = ""; // Efface les suggestions
      return;
    }

    const filteredData = preloadeFrnData.filter((item) => {
      const phrase = item.num_fournisseur + " - " + item.nom_fournisseur;
      return phrase.toLowerCase().includes(numFrn.toLowerCase());
    });

    showSuggestions(suggestionContainerNum, filteredData);
  }

  function filtrerLesDonnerNom() {
    const nomFrn = nomFrnInput.value.trim();

    // Si l'input est vide, efface les suggestions et arrête l'exécution
    if (nomFrn === "") {
      suggestionContainerNom.innerHTML = ""; // Efface les suggestions
      return;
    }

    const filteredData = preloadeFrnData.filter((item) => {
      const phrase = item.num_fournisseur + " - " + item.nom_fournisseur;
      return phrase.toLowerCase().includes(nomFrn.toLowerCase());
    });

    showSuggestions(suggestionContainerNom, filteredData);
  }

  /**
   * Methode permet d'afficher les donner sur le div du suggestion
   * @param {HTMLElement} suggestionsContainer
   * @param {Array} data
   */
  function showSuggestions(suggestionsContainer, data) {
    // Vérifie si le tableau est vide
    if (data.length === 0) {
      suggestionsContainer.innerHTML = ""; // Efface les suggestions
      return; // Arrête l'exécution de la fonction
    }

    suggestionsContainer.innerHTML = ""; // Efface les suggestions existantes
    data.forEach((item) => {
      const numFournisseur = item.num_fournisseur;
      const nomFournisseur = item.nom_fournisseur;
      const suggestion = document.createElement("div");
      suggestion.textContent = numFournisseur + " - " + nomFournisseur; // Affiche la liste des suggestions
      remplitLesChamps(suggestion, suggestionsContainer, item);
      suggestionsContainer.appendChild(suggestion);
    });
  }

  function remplitLesChamps(suggestion, suggestionsContainer, item) {
    suggestion.addEventListener("click", () => {
      const numFournisseur = item.num_fournisseur;
      const nomFournisseur = item.nom_fournisseur;
      const deviseFournisseur = item.devise;
      const modePaiementFournisseur = item.mode_paiement;
      const rib = item.rib;
      updateNumFournisseurFields(numFournisseur); // Remplit le champ avec la sélection
      updateNomFournisseurFields(nomFournisseur);
      updateDeviseFournisseurFields(deviseFournisseur);
      updateModePaiementFournisseurFields(modePaiementFournisseur);
      updateRibFournisseurFields(rib);
      updateCdeFrn(numFournisseur);
      suggestionsContainer.innerHTML = ""; // Efface les suggestions
    });
  }

  async function updateCdeFrn(numFournisseur) {
    try {
      const endpoint = `api/num-cde-frn/${numFournisseur}`;
      const commandes = await fetchManager.get(endpoint);
      const numCdeInput = document.querySelector(
        "#demande_paiement_numeroCommande"
      );
      console.log(commandes.numCdes);

      if (numCdeInput) {
        numCdeInput.value = commandes.numCdes.join(";");
      } else {
        console.error("Les éléments du formulaire n'ont pas été trouvés.");
      }
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des données:",
        error.message
      );
    }
  }

  function updateNumFournisseurFields(numFournisseur) {
    // Vérification si les éléments sont présents dans le DOM
    if (numFrnInput) {
      numFrnInput.value = numFournisseur;
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  function updateNomFournisseurFields(nomFournisseur) {
    // Vérification si les éléments sont présents dans le DOM
    if (nomFrnInput) {
      nomFrnInput.value = nomFournisseur;
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  function updateDeviseFournisseurFields(devise) {
    if (deviseInput) {
      deviseInput.value = devise;
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  function updateModePaiementFournisseurFields(modePaiement) {
    if (modePaiementInput) {
      modePaiementInput.value = modePaiement;
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  function updateRibFournisseurFields(rib) {
    if (ribInput) {
      if (
        rib &&
        rib != 0 &&
        rib.trim() != "XXXXXXXXXXX" &&
        rib.trim() != "US" &&
        rib.trim() != "ZA"
      ) {
        ribInput.value = rib;
      } else {
        ribInput.value = "-";
      }
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  /** FICHIER */
  const fileInput = document.querySelector("#demande_paiement_pieceJoint01");
  initializeFileHandlers("1", fileInput);
});
