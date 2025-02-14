import { FetchManager } from "../api/FetchManager.js";
import { TableauComponent } from "../Component/TableauComponent.js";
import { formaterNombre } from "../utils/formatNumberUtils.js";
import { initializeFileHandlers } from "../utils/file_upload_Utils.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager("/Hffintranet");

  let preloadedData = [];

  const numFrnInput = document.querySelector(
    "#cde_fnr_soumis_a_validation_codeFournisseur"
  );
  const nomFrnInput = document.querySelector(
    "#cde_fnr_soumis_a_validation_libelleFournisseur"
  );
  const suggestionContainerNum = document.querySelector(
    "#suggestion-num-fournisseur"
  );
  const suggestionContainerNom = document.querySelector(
    "#suggestion-nom-fournisseur"
  );

  async function fetchListeFournisseur(endpoint) {
    try {
      const fornisseurs = await fetchManager.get(endpoint);
      preloadedData = fornisseurs;
      // console.log("Données récupérées:", fornisseurs);
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des données:",
        error.message
      );
    }
  }

  const endpoint = "api/liste-fournisseur";
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

    const filteredData = preloadedData.filter((item) => {
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

    const filteredData = preloadedData.filter((item) => {
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
      suggestion.addEventListener("click", () => {
        updateClientFields(numFournisseur, nomFournisseur); // Remplit le champ avec la sélection
        suggestionsContainer.innerHTML = ""; // Efface les suggestions
      });
      suggestionsContainer.appendChild(suggestion);
    });
  }

  function updateClientFields(numFournisseur, nomFournisseur) {
    // Vérification si les éléments sont présents dans le DOM
    if (numFrnInput && nomFrnInput) {
      numFrnInput.value = numFournisseur;
      nomFrnInput.value = nomFournisseur;
      const endpointCde = `api/cde-fnr-non-receptionner/${numFournisseur}`;
      fetchListeCdeFournisseur(endpointCde);
    } else {
      console.error("Les éléments du formulaire n'ont pas été trouvés.");
    }
  }

  /**
   * Mettre les champs numero fournisseur et numero commande à n'accepter que les chiffres
   */
  function allowOnlyNumbers(inputElement) {
    inputElement.addEventListener("input", () => {
      inputElement.value = inputElement.value.replace(/[^0-9]/g, "");
    });
  }

  allowOnlyNumbers(numFrnInput);
  const numCmdInput = document.querySelector(
    "#cde_fnr_soumis_a_validation_numCdeFournisseur"
  );
  allowOnlyNumbers(numCmdInput);

  /**
   * Affichage du liste commande fournisseur
   */
  let preloadedDataCde = [];
  const columns = [
    { label: "N° cde", key: "num_cde" },
    {
      label: "Date",
      key: "date_cde",
      format: (value) => new Date(value).toLocaleDateString("fr-FR"),
    },
    { label: "Libelle", key: "libelle_cde" },
    {
      label: "Prix TTC",
      key: "prix_cde_ttc",
      align: "right",
      format: (value) => formaterNombre(value),
    },
    {
      label: "Prix TTC Devise",
      key: "prix_cde_ttc_devise",
      align: "right",
      format: (value) => formaterNombre(value),
    },
    { label: "Devise", key: "devise_cde", align: "center" },
    { label: "Type", key: "type_cde", align: "center" },
  ];

  async function fetchListeCdeFournisseur(
    endpoint,
    spinnerElement,
    containerElement
  ) {
    try {
      // Afficher le spinner avant le début du chargement
      // toggleSpinner(spinnerElement, containerElement, true);

      const $tableauContainer = document.querySelector("#tableau_cde_frn");
      $tableauContainer.innerHTML = "";

      const cdes = await fetchManager.get(endpoint);

      const tableauComponent = new TableauComponent({
        columns: columns,
        data: cdes,
        theadClass: "table-dark",
      });

      tableauComponent.mount("tableau_cde_frn");
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des données:",
        error.message
      );
    } finally {
      // Cacher le spinner après le chargement des données (qu'il y ait une erreur ou non)
      // toggleSpinner(spinnerElement, containerElement, false);
    }
  }

  /**====================
   * FICHIER
   *=====================*/
  const fileInput = document.querySelector(
    "#cde_fnr_soumis_a_validation_pieceJoint01"
  );
  initializeFileHandlers("1", fileInput);

  /**==================================================
   * sweetalert pour le bouton Enregistrer
   *==================================================*/
  const btnCdeFnr = document.querySelector("#bouton-cde-fnr");
  btnCdeFnr.addEventListener("click", (e) => {
    e.preventDefault();
    const overlay = document.getElementById("loading-overlay");
    Swal.fire({
      title: "Êtes-vous sûr ?",
      text: `Vous êtes en train de soumettre une commande à validation dans DocuWare `,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#fbbb01",
      cancelButtonColor: "#d33",
      confirmButtonText: "OUI",
    })
      .then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Fait Attention!",
            text: "Veuillez de ne pas fermer l’onglet durant le traitement.",
            icon: "warning",
          }).then((res) => {
            overlay.classList.remove("hidden");
            // Soumettre le formulaire
            const form = document.querySelector("#myForm");
            form.submit();
          });
        }
      })
      .finally(() => {
        overlay.classList.add("hidden");
      });
  });
});
