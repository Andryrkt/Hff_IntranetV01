import { FetchManager } from "../api/FetchManager.js";
import { initializeFileHandlers } from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { AutoComplete } from "../utils/Autocomplete.js";
import { TableauComponent } from "../Component/TableauComponent.js";
import { enleverPartiesTexte } from "../utils/ui/stringUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  /**====================================
   * AUTOCOMPLETE
   *====================================*/
  const fetchManager = new FetchManager("/Hffintranet");

  async function fetchFournisseurs() {
    return await fetchManager.get("api/info-fournisseur-ddp");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  function onSelectFournisseur(item) {
    document.querySelector("#demande_paiement_numeroFournisseur").value =
      item.num_fournisseur;
    document.querySelector("#demande_paiement_beneficiaire").value =
      item.nom_fournisseur;
    document.querySelector("#demande_paiement_devise").value = item.devise;
    document.querySelector("#demande_paiement_modePaiement").value =
      item.mode_paiement;
    document.querySelector("#demande_paiement_ribFournisseur").value =
      item.rib && item.rib != 0 && item.rib.trim() !== "XXXXXXXXXXX"
        ? item.rib
        : "-";

    // Exemple : Récupérer les commandes du fournisseur après la sélection
    updateCommandesFournisseur(item.num_fournisseur);
  }

  // Activation sur le champ "Numéro Fournisseur"
  new AutoComplete({
    inputElement: document.querySelector("#demande_paiement_numeroFournisseur"),
    suggestionContainer: document.querySelector("#suggestion-num-fournisseur"),
    loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
  });

  // Activation sur le champ "Nom Fournisseur"
  new AutoComplete({
    inputElement: document.querySelector("#demande_paiement_beneficiaire"),
    suggestionContainer: document.querySelector("#suggestion-nom-fournisseur"),
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
  });

  /**
   * Permet d'afficher le tableau de facture
   * @param {string} numFournisseur
   */
  async function updateCommandesFournisseur(numFournisseur) {
    const commandes = await fetchManager.get(
      `api/num-cde-frn/${numFournisseur}`
    );
    const $tableauContainer = document.querySelector("#tableau_facture");
    $tableauContainer.innerHTML = "";
    const columns = [
      { label: "N° Facture", key: "Numero_Facture" },
      { label: "N° fournisseur", key: "Code_Fournisseur" },
      { label: "Nom fournisseur", key: "Libelle_Fournisseur" },
      { label: "N° Dossier", key: "Numero_Dossier_Douane" },
      { label: "N° LTA", key: "Numero_LTA" },
      { label: "N° HAWB", key: "Numero_HAWB" },
      { label: "N° PO", key: "Numero_PO" },
    ];

    const tableauComponent = new TableauComponent({
      columns: columns,
      data: commandes.listeGcot,
      theadClass: "table-dark",
      rowClassName: "clickable-row clickable",
      customRenderRow: (row, index, data) =>
        customRenderRow(row, index, data, columns),
      onRowClick: (row) => chargerDocuments(row.Numero_Dossier_Douane),
    });

    tableauComponent.mount("tableau_facture");
    //     document.querySelector("#demande_paiement_numeroCommande").value =
    //       commandes.numCdes.join(";");
  }

  function customRenderRow(row, index, data, columns) {
    const tr = document.createElement("tr");

    // Colonnes qui doivent être identiques pour la fusion
    const columnsToMerge = [
      "Numero_Facture",
      "Code_Fournisseur",
      "Libelle_Fournisseur",
      "Numero_Dossier_Douane",
      "Numero_LTA",
      "Numero_HAWB",
    ];

    const previousRow = data[index - 1] || {};
    const nextRow = data[index + 1] || {};

    const isLastOfGroup =
      index === data.length - 1 ||
      columnsToMerge.some((key) => row[key] !== nextRow[key]);

    if (isLastOfGroup) {
      tr.style.borderBottom = "3px solid black";
    }

    // Vérifie si la ligne actuelle est la première d'un groupe "fusionnable"
    const isFirstOfGroup =
      index === 0 ||
      columnsToMerge.some(
        (key) => row[key] !== previousRow[key] // Si une valeur est différente, c'est le début d'un groupe
      );

    // Compte combien de lignes peuvent être fusionnées
    let rowspan = 1;
    if (isFirstOfGroup) {
      for (let i = index + 1; i < data.length; i++) {
        const nextRow = data[i];
        const isSameGroup = columnsToMerge.every(
          (key) => row[key] === nextRow[key]
        );

        if (isSameGroup) {
          rowspan++;
        } else {
          break;
        }
      }
    }

    columns.forEach((column) => {
      const td = document.createElement("td");
      const value = row[column.key] || "-";

      // Gestion des colonnes à fusionner
      if (columnsToMerge.includes(column.key)) {
        // Si ce n'est pas la première ligne du groupe, on ne met rien
        if (!isFirstOfGroup) {
          return; // Ne pas créer de cellule pour les lignes suivantes du groupe
        }

        td.textContent = value;

        if (rowspan > 1) {
          td.setAttribute("rowspan", rowspan);
          td.style.verticalAlign = "middle";
        }
      } else {
        // Colonnes normales sans fusion
        td.textContent = value;
      }

      tr.appendChild(td);
    });

    // Ajoute une classe personnalisée si nécessaire
    tr.classList.add("clickable-row", "clickable");

    // Ajoute l'événement au clic sur la ligne
    tr.addEventListener("click", () =>
      chargerDocuments(row.Numero_Dossier_Douane)
    );

    return tr;
  }

  async function chargerDocuments(numeroDossier) {
    // const spinners = document.getElementById("spinners");
    // const spinner = document.getElementById("spinner");

    // spinner.style.display = "block";

    try {
      const dossier = await fetchManager.get(`api/liste-doc/${numeroDossier}`);

      //spinner.style.display = "none";

      const tContainer = document.getElementById("tableau_dossier");
      tContainer.innerHTML = "";
      const columns = [
        {
          label: "Nom de fichier",
          key: "Nom_Fichier",
          format: (value) => nomFichier(value),
        },
        {
          label: "Date",
          key: "Date_Fichier",
          format: (value) => new Date(value).toLocaleDateString("fr-FR"),
        },
      ];

      const tableauComponent = new TableauComponent({
        columns: columns,
        data: dossier,
        theadClass: "table-dark",
        rowClassName: "clickable-row clickable",
        onRowClick: (row) => afficherFichier(row.Nom_Fichier),
      });

      tableauComponent.mount("tableau_dossier");
    } catch (error) {
      console.error("Erreur chargement des documents : ", error);
      spinner.style.display = "none";
    }
  }

  function nomFichier(cheminFichier) {
    const motExacteASupprimer = [
      "\\\\192.168.0.15",
      "\\GCOT_DATA",
      "\\TRANSIT",
    ];
    const motCommenceASupprimer = ["\\DD"];

    return enleverPartiesTexte(
      cheminFichier,
      motExacteASupprimer,
      motCommenceASupprimer
    );
  }

  async function afficherFichier(nomFichie) {
    try {
      const fileName = nomFichier(nomFichie);
      const url = `/Hffintranet/api/recuperer-fichier/${fileName}`;
      window.open(url, "_blank");
    } catch (error) {
      console.error("Erreur lors de l'ouverture du fichier : ", error);
    }
  }

  /** ============================
   * FICHIER
   * =============================*/
  const fileInput = document.querySelector("#demande_paiement_pieceJoint01");
  initializeFileHandlers("1", fileInput);

  /**==================================================
   * sweetalert pour le bouton Enregistrer
   *==================================================*/
  setupConfirmationButtons();
});
