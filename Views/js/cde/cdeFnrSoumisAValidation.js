import { FetchManager } from '../api/FetchManager.js';
import { TableauComponent } from '../Component/TableauComponent.js';
import { formaterNombre } from '../utils/formatNumberUtils.js';
import { limitInputLength, allowOnlyNumbers } from '../utils/inputUtils.js';
import {
  initializeFileHandlers,
  disableDropzone,
  enableDropzone,
} from '../utils/file_upload_Utils.js';
import { AutoComplete } from '../utils/AutoComplete.js';
import { setupConfirmationButtons } from '../utils/ui/boutonConfirmUtils.js';

document.addEventListener('DOMContentLoaded', function () {
  const fetchManager = new FetchManager();

  disableDropzone(1); //griser l'envoie du fichier

  const numFrnInput = document.querySelector(
    '#cde_fnr_soumis_a_validation_codeFournisseur'
  );
  const nomFrnInput = document.querySelector(
    '#cde_fnr_soumis_a_validation_libelleFournisseur'
  );
  const suggestionContainerNum = document.querySelector(
    '#suggestion-num-fournisseur'
  );
  const suggestionContainerNom = document.querySelector(
    '#suggestion-nom-fournisseur'
  );
  const numCdeInput = document.querySelector(
    '#cde_fnr_soumis_a_validation_numCdeFournisseur'
  );
  const overlay = document.getElementById('loading-overlay-petite');

  const boutonInput = document.querySelector('#bouton-cde-fnr');

  /** Mettre les champs numero fournisseur à n'accepter que les chiffres*/
  allowOnlyNumbers(numFrnInput);
  /** Limite le nombre de caractère du champ numero fournisseur */
  limitInputLength(numFrnInput, 7);
  /** N'accepte que les nombres pour le champ numero commande */
  allowOnlyNumbers(numCdeInput);
  /** Limite le nombre de caractère du champ numero commande */
  limitInputLength(numCdeInput, 8);

  numCdeInput.disabled = true; //griser le champ numero commande

  /**=================================================
   *AUTOCOMPLET LES CHAMPS NUMERO ET NOM FOURNISSEUR
   *=================================================*/

  async function fetchFournisseurs() {
    return await fetchManager.get('api/liste-fournisseur');
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  function onSelectFournisseur(item) {
    numFrnInput.value = item.num_fournisseur;
    nomFrnInput.value = item.nom_fournisseur;
    autocompletCde(item.num_fournisseur);
  }

  // Activation sur le champ "Numéro Fournisseur"
  new AutoComplete({
    inputElement: numFrnInput,
    suggestionContainer: suggestionContainerNum,
    loaderElement: document.querySelector('#loader-num-fournisseur'), // Ajout du loader
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
    loaderElement: document.querySelector('#loader-nom-fournisseur'), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
    itemToStringCallback: (item) =>
      `${item.num_fournisseur} - ${item.nom_fournisseur}`,
  });

  /**===========================================
   * AUTOCOMPLET NUMERO COMMANDE FOURNISSEUR
   *===========================================*/

  const erreurCdeInput = document.querySelector('#erreur-num-cde');
  let prelodDataCde = [];

  async function fetchCommandesFrn() {
    try {
      const response = await fetchManager.get(`api/num-cde-fnr`);
      prelodDataCde = response;
    } catch (error) {
      console.error(
        `Erreur lors de la récupération des commandes pour le fournisseur:`,
        error
      );
      prelodDataCde = [];
    }
  }
  // Appelle le chargement des commandes au démarrage
  fetchCommandesFrn();

  function displayCommandes(item) {
    return `${item.num_cde}`;
  }

  function onSelectCommandes(item) {
    console.log('Sélection effectuée : ', item);

    numCdeInput.value = item.num_cde;

    initTableau(item.num_cde);
  }

  function filtreNumCdeFrn(orders, numFournisseur) {
    return orders.filter((order) => order.num_fournisseur === numFournisseur);
  }

  // let autoCompleteCde;
  function autocompletCde(numFournisseur) {
    // if (!autoCompleteCde) {
    new AutoComplete({
      inputElement: numCdeInput,
      suggestionContainer: document.querySelector('#suggestion-num-cde'),
      loaderElement: document.querySelector('#loader-num-cde'),
      debounceDelay: 300,
      fetchDataCallback: () => {
        return dataCde(numFournisseur);
      },
      displayItemCallback: displayCommandes,
      onSelectCallback: onSelectCommandes,
      itemToStringCallback: (item) => `${item.num_cde}`,
    });
    // } else {
    //   autoCompleteCde.fetchDataCallback = () => fetchCommandes(numFournisseur);
    // }
  }

  /**
   * Renvoi les donnée filtrer
   * @param {string} numFournisseur
   * @returns
   */
  function dataCde(numFournisseur) {
    erreurCdeInput.innerHTML = '';

    const cdeFiltred = filtreNumCdeFrn(prelodDataCde, numFournisseur);

    siChangeNumcde(cdeFiltred); //block si l'utilisateur ne rentre pas la vrais valeur

    if (!cdeFiltred.length) {
      numCdeInput.disabled = true;
      erreurCdeInput.innerHTML =
        'pas de commande à soumettre pour ce numéro fournissseur';
    } else {
      numCdeInput.disabled = false;
    }
    return cdeFiltred;
  }

  function siChangeNumcde(cdeFiltred) {
    numCdeInput.addEventListener('input', () => {
      console.log(numCdeInput.value);
      const exists = cdeFiltred
        .map((item) => item.num_cde)
        .includes(numCdeInput.value);
      console.log(exists);

      if (!exists) {
        disableDropzone(1);
        boutonInput.disabled = true;
      }
    });
  }

  /**=========================================
   * Affichage du liste commande fournisseur
   *=========================================*/
  const columns = [
    { label: 'N° cde', key: 'num_cde' },
    {
      label: 'Date',
      key: 'date_cde',
      format: (value) => new Date(value).toLocaleDateString('fr-FR'),
    },
    { label: 'Libelle', key: 'libelle_cde' },
    {
      label: 'Prix TTC',
      key: 'prix_cde_ttc',
      align: 'right',
      format: (value) => formaterNombre(value),
    },
    {
      label: 'Constructeur',
      key: 'constructeur',
      align: 'center',
    },
    {
      label: 'ref pièce',
      key: 'ref_piece',
    },
    {
      label: 'Nbre pièces',
      key: 'nbr_piece',
      align: 'center',
    },
    { label: 'Devise', key: 'devise_cde', align: 'center' },
    { label: 'Type', key: 'type_cde', align: 'center' },
  ];

  async function fetchListeCdeFournisseur() {
    try {
      const commandes = await fetchManager.get(`api/cde-fnr-non-receptionner`);
      return commandes;
    } catch (error) {
      console.error('Erreur lors du chargement des commandes :', error);
      return [];
    }
  }

  async function initTableau(numCde) {
    try {
      effaceTab();
      overlay.style.display = 'flex';
      const infoCde = await fetchListeCdeFournisseur();

      const data = filtreListCdeFrn(infoCde, numCde);

      const tableauComponent = new TableauComponent({
        columns: columns,
        data: data,
        theadClass: 'table-dark',
      });
      tableauComponent.mount('tableau_cde_frn');
      boutonInput.disabled = false;
      enableDropzone(1);
    } catch (error) {
      console.error("Erreur lors de l'afichage du tableau cde :", error);
    } finally {
      overlay.style.display = 'none';
    }
  }

  function filtreListCdeFrn(orders, numCde) {
    return orders.filter((order) => order.num_cde === numCde);
  }

  function effaceTab() {
    const parent = document.querySelector('#tableau_cde_frn');
    const secondChild = parent.children[1];

    if (secondChild) {
      secondChild.remove(); // Plus besoin d'appeler removeChild sur le parent
      console.log('Deuxième enfant supprimé !');
      disableDropzone(1);
      boutonInput.disabled = true;
    } else {
      console.warn("Le deuxième enfant n'existe pas !");
    }
  }

  /**=================================================
   * FICHIER
   *=================================================*/
  const fileInput = document.querySelector(
    `#cde_fnr_soumis_a_validation_pieceJoint01`
  );

  initializeFileHandlers('1', fileInput);

  /** ====================================================
   * bouton Enregistrer
   *===================================================*/
  setupConfirmationButtons();
});
