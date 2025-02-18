import { FetchManager } from "../api/FetchManager.js";
import { TableauComponent } from "../Component/TableauComponent.js";
import { formaterNombre } from "../utils/formatNumberUtils.js";
import {
  initializeFileHandlers,
  disableDropzone,
  enableDropzone,
} from "../utils/file_upload_Utils.js";
import { AutoComplete } from "../utils/Autocomplete.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager("/Hffintranet");

  disableDropzone(1);
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

  const overlay = document.getElementById("loading-overlay-petite");

  const boutonInput = document.querySelector("#bouton-cde-fnr");

  /**=================================================
   *AUTOCOMPLET LES CHAMPS NUMERO ET NOM FOURNISSEUR
   *=================================================*/

  async function fetchFournisseurs() {
    return await fetchManager.get("api/liste-fournisseur");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }
  let autoCompleteCde;
  function onSelectFournisseur(item) {
    numFrnInput.value = item.num_fournisseur;
    nomFrnInput.value = item.nom_fournisseur;
    if (!autoCompleteCde) {
      autoCompleteCde = new AutoComplete({
        inputElement: numCdeInput,
        suggestionContainer: document.querySelector("#suggestion-num-cde"),
        loaderElement: document.querySelector("#loader-num-cde"),
        debounceDelay: 300,
        fetchDataCallback: async () => {
          const commandes = await fetchCommandes();
          return filtreNumCde(commandes, item.num_fournisseur);
        }, // Capture la valeur du fournisseur sélectionné
        displayItemCallback: displayCommandes,
        onSelectCallback: onSelectCommandes,
        itemToStringCallback: (item) => `${item.num_cde}`,
      });
    } else {
      autoCompleteCde.fetchDataCallback = () =>
        fetchCommandes(item.num_fournisseur);
    }
  }

  // Activation sur le champ "Numéro Fournisseur"
  new AutoComplete({
    inputElement: numFrnInput,
    suggestionContainer: suggestionContainerNum,
    loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
    itemToStringCallback: (item) =>
      `${item.num_fournisseur} - ${item.nom_fournisseur}`,
  });

  // Activation sur le champ "Nom Fournisseur"
  new AutoComplete({
    inputElement: nomFrnInput,
    suggestionContainer: suggestionContainerNom,
    loaderElement: document.querySelector("#loader-nom-fournisseur"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
    itemToStringCallback: (item) =>
      `${item.num_fournisseur} - ${item.nom_fournisseur}`,
  });

  /**=====================================================================================
   * Mettre les champs numero fournisseur et numero commande à n'accepter que les chiffres
   *========================================================================================*/
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

  /**===========================================
   * AUTOCOMPLET NUMERO COMMANDE FOURNISSEUR
   *===========================================*/
  const numCdeInput = document.querySelector(
    "#cde_fnr_soumis_a_validation_numCdeFournisseur"
  );

  async function fetchCommandes() {
    try {
      return await fetchManager.get(`api/num-cde-fnr`);
    } catch (error) {
      console.error(
        `Erreur lors de la récupération des commandes pour le fournisseur:`,
        error
      );
      return [];
    }
  }

  function displayCommandes(item) {
    return `${item.num_cde}`;
  }

  function onSelectCommandes(item) {
    numCdeInput.value = item.num_cde;

    console.log(overlay);
    initTableau(item.num_cde);
  }

  function filtreNumCde(orders, numFournisseur) {
    return orders.filter((order) => order.num_fournisseur === numFournisseur);
  }

  /**=========================================
   * Affichage du liste commande fournisseur
   *=========================================*/
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
      label: "Constructeur",
      key: "constructeur",
      align: "center",
    },
    {
      label: "ref pièce",
      key: "ref_piece",
    },
    {
      label: "Nbre pièces",
      key: "nbr_piece",
      align: "center",
    },
    { label: "Devise", key: "devise_cde", align: "center" },
    { label: "Type", key: "type_cde", align: "center" },
  ];

  async function fetchListeCdeFournisseur() {
    try {
      const commandes = await fetchManager.get(`api/cde-fnr-non-receptionner`);
      return commandes;
    } catch (error) {
      console.error("Erreur lors du chargement des commandes :", error);
      return [];
    }
  }

  async function initTableau(numCde) {
    try {
      overlay.style.display = "flex";
      const infoCde = await fetchListeCdeFournisseur();
      const data = filtreListCdeFrn(infoCde, numCde);

      document.querySelector("#tableau_cde_frn").innerHTML = "";
      const tableauComponent = new TableauComponent({
        columns: columns,
        data: data,
        theadClass: "table-dark",
      });
      tableauComponent.mount("tableau_cde_frn");
      boutonInput.disabled = false;
      enableDropzone(1);
    } catch (error) {
      console.error("Erreur lors de l'afichage du tableau cde :", error);
    } finally {
      overlay.style.display = "none";
    }
  }

  function filtreListCdeFrn(orders, numCde) {
    return orders.filter((order) => order.num_cde === numCde);
  }
  /**=================================================
   * FICHIER
   *=================================================*/
  const fileInput = document.querySelector(
    `#cde_fnr_soumis_a_validation_pieceJoint01`
  );

  initializeFileHandlers("1", fileInput);
});
