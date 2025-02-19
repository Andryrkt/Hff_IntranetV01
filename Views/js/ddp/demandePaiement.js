import { FetchManager } from "../api/FetchManager.js";
import { initializeFileHandlers } from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { AutoComplete } from "../utils/Autocomplete.js";
import { TableauComponent } from "../Component/TableauComponent.js";
import { enleverPartiesTexte } from "../utils/ui/stringUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  const numFrnInput = document.querySelector(
    "#demande_paiement_numeroFournisseur"
  );
  const beneficiaireInput = document.querySelector(
    "#demande_paiement_beneficiaire"
  );
  const deviseInput = document.querySelector("#demande_paiement_devise");

  const modePaiementInput = document.querySelector(
    "#demande_paiement_modePaiement"
  );
  const ribFrnInput = document.querySelector(
    "#demande_paiement_ribFournisseur"
  );
  /**====================================
   * AUTOCOMPLETE numero Fournisseur
   *====================================*/
  const fetchManager = new FetchManager("/Hffintranet");

  async function fetchFournisseurs() {
    return await fetchManager.get("api/info-fournisseur-ddp");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  function onSelectFournisseur(item) {
    numFrnInput.value = item.num_fournisseur;
    beneficiaireInput.value = item.nom_fournisseur;
    deviseInput.value = item.devise;
    modePaiementInput.value = item.mode_paiement;
    ribFrnInput.value =
      item.rib && item.rib != 0 && item.rib.trim() !== "XXXXXXXXXXX"
        ? item.rib
        : "-";

    // Exemple : Récupérer les commandes du fournisseur après la sélection
    updateCommandesFournisseur(item.num_fournisseur);
  }

  // Activation sur le champ "Numéro Fournisseur"
  new AutoComplete({
    inputElement: numFrnInput,
    suggestionContainer: document.querySelector("#suggestion-num-fournisseur"),
    loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
  });

  // Activation sur le champ "Nom Fournisseur"
  new AutoComplete({
    inputElement: beneficiaireInput,
    suggestionContainer: document.querySelector("#suggestion-nom-fournisseur"),
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
  });

  /**============================================
   * AFFICHAGE LISTE TABLEAU FACTURE
   *============================================*/
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
      { label: "Sélectionner", key: "checkbox" },
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
  }

  function customRenderRow(row, index, data, columns) {
    const tr = document.createElement("tr");
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

    const isFirstOfGroup =
      index === 0 ||
      columnsToMerge.some((key) => row[key] !== previousRow[key]);

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

      if (column.key === "checkbox") {
        if (isFirstOfGroup) {
          const checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.dataset.numFacture = row.Numero_Facture;
          checkbox.addEventListener("change", (e) =>
            toggleSelection(e, row.Numero_Facture, data)
          );
          td.appendChild(checkbox);

          if (rowspan > 1) {
            td.setAttribute("rowspan", rowspan);
            td.style.verticalAlign = "middle";
          }
        } else {
          return;
        }
      } else if (columnsToMerge.includes(column.key)) {
        if (!isFirstOfGroup) return;
        td.textContent = row[column.key] || "-";
        if (rowspan > 1) {
          td.setAttribute("rowspan", rowspan);
          td.style.verticalAlign = "middle";
        }
      } else {
        td.textContent = row[column.key] || "-";
      }

      tr.appendChild(td);
    });

    tr.classList.add("clickable-row", "clickable");

    tr.addEventListener("click", () =>
      chargerDocuments(row.Numero_Dossier_Douane)
    );

    return tr;
  }

  function toggleSelection(event, numeroFacture, data) {
    const isChecked = event.target.checked;
    data.forEach((row) => {
      if (row.Numero_Facture === numeroFacture) {
        row.selected = isChecked;
      }
    });
    console.log(
      "Données sélectionnées :",
      data.filter((row) => row.selected)
    );
  }

  function getSelectedFactures(data) {
    return data.filter((row) => row.selected).map((row) => row.Numero_Facture);
  }

  /**==============================================
   * AFFICHAGE DU TABLEAU DE DOCUMENT
   *===============================================*/

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
