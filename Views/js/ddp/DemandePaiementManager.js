import { FetchManager } from "../api/FetchManager.js";
import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";
import { AutoComplete } from "../utils/AutoComplete.js";
import { TableauComponent } from "../Component/TableauComponent.js";
import { enleverPartiesTexte } from "../utils/ui/stringUtils.js";
import { allowOnlyNumbers, limitInputLength } from "../utils/inputUtils.js";
import {
  registerLocale,
  setLocale,
  formatNumberSpecial,
  formaterNombre,
} from "../utils/formatNumberUtils.js";

export class DemandePaiementManager {
  constructor(config) {
    this.config = config;
    this.elements = {};
    this.fetchManager = new FetchManager();
    this.isUpdatingCommande = false;
    this.isUpdatingFacture = false;
    this.typeId = null;

    this.initElements();
    if (this.elements.numFactureInput) {
      this.typeId = this.elements.numFactureInput.dataset.typeid;
      this.typeDa = this.elements.numFactureInput.dataset.typeda;
    }
  }

  initElements() {
    for (const key in this.config.selectors) {
      const selector = this.config.selectors[key];
      this.elements[key] = document.querySelector(selector);
      if (!this.elements[key]) {
        console.warn(
          `Element with selector "${selector}" not found for key "${key}".`,
        );
      }
    }
  }

  init() {
    this.initAutocomplete();
    this.initSelect2();
    this.initEventListeners();
    this.initFileUploads();
    this.initConfirmationButtons();
    this.initInputFormatters();
  }

  initAutocomplete() {
    new AutoComplete({
      inputElement: this.elements.numFrnInput,
      suggestionContainer: this.elements.suggestionNumFournisseur,
      loaderElement: this.elements.loaderNumFournisseur,
      debounceDelay: 300,
      fetchDataCallback: () => this.fetchFournisseurs(),
      displayItemCallback: (item) => this.displayFournisseur(item),
      onSelectCallback: (item) => this.onSelectFournisseur(item),
    });

    new AutoComplete({
      inputElement: this.elements.beneficiaireInput,
      suggestionContainer: this.elements.suggestionNomFournisseur,
      fetchDataCallback: () => this.fetchFournisseurs(),
      displayItemCallback: (item) => this.displayFournisseur(item),
      onSelectCallback: (item) => this.onSelectFournisseur(item),
    });
  }

  initSelect2() {
    $(this.elements.numCommandeInput).select2({
      placeholder: "-- Choisir les commandes --",
      allowClear: true,
      theme: "bootstrap",
      width: "100%",
    });

    $(this.elements.numFactureInput).select2({
      placeholder: "-- Choisir les factures --",
      allowClear: true,
      theme: "bootstrap",
      width: "100%",
    });
  }

  initEventListeners() {
    if (this.typeId != 2) {
      $(this.elements.numCommandeInput).on("change", async () => {
        const numCdes = $(this.elements.numCommandeInput).val();
        let numCde = numCdes.length == 0 ? 0 : numCdes.join(",");

        try {
          const url = this.config.urls.montantCommande.replace(
            ":numCde",
            numCde,
          );
          const montants = await this.fetchManager.get(url);
          this.elements.montantInput.value =
            numCdes.length != 0 ? montants[0].montantcde : "";
        } catch (err) {
          console.error("Erreur lors de la récupération des montants :", err);
        }
      });
    }

    if (this.elements.agenceDebiteurInput) {
      this.elements.agenceDebiteurInput.addEventListener("change", () =>
        this.selectAgence(),
      );
    }
  }

  initFileUploads() {
    initializeFileHandlersNouveau("1", this.elements.fileInput1);
    initializeFileHandlersNouveau("2", this.elements.fileInput2);
    if (this.elements.fileInput3) {
      initializeFileHandlersMultiple("3", this.elements.fileInput3);
    }
    initializeFileHandlersNouveau("4", this.elements.fileInput4);
  }

  initConfirmationButtons() {
    setupConfirmationButtons();
  }

  initInputFormatters() {
    allowOnlyNumbers(this.elements.contactInput);
    limitInputLength(this.elements.contactInput, 10);

    registerLocale("fr-custom", {
      delimiters: { thousands: " ", decimal: "," },
    });
    setLocale("fr-custom");

    this.elements.montantInput.addEventListener("input", (e) => {
      this.elements.montantInput.value = formatNumberSpecial(
        this.elements.montantInput.value,
      );
    });
  }

  async fetchFournisseurs() {
    return await this.fetchManager.get(this.config.urls.fournisseurs);
  }

  displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  onSelectFournisseur(item) {
    this.elements.numFrnInput.value = item.num_fournisseur;
    this.elements.beneficiaireInput.value = item.nom_fournisseur;
    this.elements.deviseInput.value = item.devise;
    this.elements.ribFrnInput.value =
      item.rib && item.rib != 0 && item.rib.trim() !== "XXXXXXXXXXX"
        ? item.rib
        : "-";

    if (this.typeId == 2) {
      this.listeFacture(item.num_fournisseur, this.typeId);
      this.listeCommande2(item.num_fournisseur, this.typeId);
      this.updateCommandesFournisseur(item.num_fournisseur, this.typeId);

      $(this.elements.numFactureInput).on("change", () => {
        this.changeCommandeSelonFacture(item.num_fournisseur, this.typeId);
      });
    } else if (this.typeId == 1 && this.typeDa === null) {
      this.listeCommande(item.num_fournisseur, this.typeId);
    }
  }

  async listeCommande(numFournisseur, id_type) {
    try {
      const url = this.config.urls.commandes
        .replace(":numFournisseur", numFournisseur)
        .replace(":id_type", id_type);
      const commandes = await this.fetchManager.get(url);
      this.ajoutDesOptions(this.elements.numCommandeInput, commandes.numCdes);
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    }
  }

  async listeCommande2(numFournisseur, id_type) {
    try {
      const url = this.config.urls.commandes
        .replace(":numFournisseur", numFournisseur)
        .replace(":id_type", id_type);
      const commandes = await this.fetchManager.get(url);
      const listeCommande = this.transformTab(commandes.listeGcot, "Numero_PO");
      this.ajoutDesOptions(this.elements.numCommandeInput, listeCommande);
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    }
  }

  transformTab(data, index = "") {
    return [
      ...new Map(
        data.map((el) => [
          el[index],
          {
            label: el[index],
            value: el[index],
          },
        ]),
      ).values(),
    ];
  }

  ajoutDesOptions(inputElement, data) {
    inputElement.innerHTML = "";
    data.forEach((item) => {
      let option = new Option(item.label, item.value);
      inputElement.appendChild(option);
    });
  }

  async listeFacture(numFournisseur, typeId) {
    try {
      this.elements.numFactureInput.innerHTML = "";
      this.elements.numCommandeInput.innerHTML = "";
      this.elements.montantInput.value = 0;

      const url = this.config.urls.commandes
        .replace(":numFournisseur", numFournisseur)
        .replace(":typeId", typeId);
      const commandes = await this.fetchManager.get(url);
      const listeFacture = this.transformTab(
        commandes.listeGcot,
        "Numero_Facture",
      );
      this.ajoutDesOptions(this.elements.numFactureInput, listeFacture);
    } catch (error) {
      console.error("Erreur lors de la récupération des factures :", error);
    }
  }

  async changeCommandeSelonFacture(numFournisseur, typeId) {
    if (this.isUpdatingFacture) return;
    this.isUpdatingCommande = true;

    const numFacs = $(this.elements.numFactureInput).val();
    try {
      const url = this.config.urls.commandes
        .replace(":numFournisseur", numFournisseur)
        .replace(":typeId", typeId);
      const commandes = await this.fetchManager.get(url);
      const facturesCorrespondantes = commandes.listeGcot.filter((f) =>
        numFacs.includes(f.Numero_Facture),
      );
      const numerosPO = [
        ...new Set(facturesCorrespondantes.map((f) => f.Numero_PO)),
      ];

      this.recupFichier(facturesCorrespondantes);
      $(this.elements.numCommandeInput).val(numerosPO).trigger("change");

      const facturesString = facturesCorrespondantes
        .map((f) => f.Numero_Facture)
        .join(",");
      if (numFacs.length === 0) {
        this.elements.montantInput.value = 0;
      }

      const montantUrl = this.config.urls.montantFacture
        .replace(":numFournisseur", numFournisseur)
        .replace(":facturesString", facturesString)
        .replace(":typeId", typeId);
      const montantFacture = await this.fetchManager.get(montantUrl);

      this.elements.montantInput.value = formaterNombre(montantFacture[0], " ");
    } catch (error) {
      console.error(
        "Erreur lors de la récupération du montant facture :",
        error,
      );
    } finally {
      this.isUpdatingCommande = false;
    }
  }

  async recupFichier(cdeFacCorrespondantes) {
    const numerosDossier = [
      ...new Set(cdeFacCorrespondantes.map((f) => f.Numero_Dossier_Douane)),
    ];
    let dossiers = [];

    for (const numero of numerosDossier) {
      try {
        const url = this.config.urls.listeDoc.replace(":numero", numero);
        const docs = await this.fetchManager.get(url);
        dossiers.push(...docs);
      } catch (error) {
        console.error(
          `Erreur lors de la récupération des fichiers pour le dossier ${numero} :`,
          error,
        );
      }
    }

    const liste = this.elements.fileList;
    liste.innerHTML = "";

    dossiers.forEach((fichier) => {
      const nom = this.nomFichier(fichier.Nom_Fichier);
      const li = document.createElement("li");
      const a = document.createElement("a");
      const baseUrl = window.location.origin;
      const encodedPath = encodeURIComponent(fichier.Nom_Fichier);
      a.href = `${baseUrl}${this.config.urls.recupererFichier}?path=${encodedPath}`;
      a.textContent = `Ouvrir ${nom}`;
      a.target = "_blank";

      a.onclick = async (e) => {
        e.preventDefault();
        const newWindow = window.open("", "_blank");
        newWindow.document.write(
          `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Chargement...</title>
                <style>
                    .loader {
                        border: 5px solid #f3f3f3;
                        border-top: 5px solid #3498db;
                        border-radius: 50%;
                        width: 50px;
                        height: 50px;
                        animation: spin 2s linear infinite;
                        margin: 20% auto;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            </head>
            <body>
                <div class="loader"></div>
                <p style="text-align: center">Chargement du document...</p>
            </body>
            </html>
        `,
        );

        try {
          const response = await fetch(a.href);
          if (!response.ok) throw new Error(await response.text());
          const contentType = response.headers.get("content-type");
          const blob = await response.blob();
          const blobUrl = URL.createObjectURL(blob);

          if (contentType.includes("pdf")) {
            newWindow.location.href = blobUrl;
          } else if (contentType.startsWith("image/")) {
            newWindow.document.body.innerHTML = `<img src="${blobUrl}" style="max-width: 100%; max-height: 100vh">`;
          } else {
            const iframe = document.createElement("iframe");
            iframe.src = blobUrl;
            iframe.style = "width:100%; height:100vh; border:none";
            newWindow.document.body.innerHTML = "";
            newWindow.document.body.appendChild(iframe);
          }

          newWindow.onunload = () => URL.revokeObjectURL(blobUrl);
        } catch (error) {
          console.error("Erreur:", error);
          newWindow.document.body.innerHTML = `
                <h1 style="color: red">Erreur</h1>
                <p>${error.message}</p>
                <button onclick="window.close()">Fermer</button>
            `;
        }
      };

      li.appendChild(a);
      liste.appendChild(li);
    });
  }

  async updateCommandesFournisseur(numFournisseur, typeId) {
    const url = this.config.urls.commandes
      .replace(":numFournisseur", numFournisseur)
      .replace(":typeId", typeId);
    const commandes = await this.fetchManager.get(url);

    const $tableauContainer = this.elements.invoiceTableContainer;
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
        this.customRenderRow(row, index, data, columns),
      onRowClick: (row) => this.chargerDocuments(row.Numero_Dossier_Douane),
    });

    tableauComponent.mount(
      this.config.selectors.invoiceTableContainer.substring(1),
    );
  }

  customRenderRow(row, index, data, columns) {
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
    const isFirstOfGroup =
      index === 0 ||
      columnsToMerge.some((key) => row[key] !== previousRow[key]);

    if (isFirstOfGroup) {
      let rowspan = 1;
      for (let i = index + 1; i < data.length; i++) {
        if (columnsToMerge.every((key) => row[key] === data[i][key])) {
          rowspan++;
        } else {
          break;
        }
      }
      columns.forEach((column) => {
        const td = document.createElement("td");
        td.textContent = row[column.key] || "-";
        if (columnsToMerge.includes(column.key)) {
          td.setAttribute("rowspan", rowspan);
          td.style.verticalAlign = "middle";
        }
        tr.appendChild(td);
      });
    } else {
      columns.forEach((column) => {
        if (!columnsToMerge.includes(column.key)) {
          const td = document.createElement("td");
          td.textContent = row[column.key] || "-";
          tr.appendChild(td);
        }
      });
    }
    const nextRow = data[index + 1] || {};
    const isLastOfGroup =
      index === data.length - 1 ||
      columnsToMerge.some((key) => row[key] !== nextRow[key]);
    if (isLastOfGroup) {
      tr.style.borderBottom = "3px solid black";
    }

    tr.addEventListener("click", () =>
      this.chargerDocuments(row.Numero_Dossier_Douane),
    );
    return tr;
  }

  async chargerDocuments(numeroDossier) {
    try {
      const url = this.config.urls.listeDoc.replace(":numero", numeroDossier);
      const dossier = await this.fetchManager.get(url);

      const tContainer = this.elements.documentTableContainer;
      tContainer.innerHTML = "";
      const columns = [
        {
          label: "Nom de fichier",
          key: "Nom_Fichier",
          format: (value) => this.nomFichier(value),
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
        onRowClick: (row) => this.afficherFichier(row.Nom_Fichier),
      });

      tableauComponent.mount(
        this.config.selectors.documentTableContainer.substring(1),
      );
    } catch (error) {
      console.error("Erreur chargement des documents : ", error);
    }
  }

  async afficherFichier(nomFichie) {
    try {
      const encodedPath = encodeURIComponent(nomFichie);
      const url = `${window.location.origin}${this.config.urls.recupererFichier}?path=${encodedPath}`;

      const newWindow = window.open("", "_blank");
      // ... (même logique d'affichage que dans recupFichier)
    } catch (error) {
      console.error("Erreur lors de l'ouverture du fichier : ", error);
    }
  }

  nomFichier(cheminFichier) {
    const motExacteASupprimer = ["\\192.168.0.15", "\GCOT_DATA", "\TRANSIT"];
    const motCommenceASupprimer = ["\DD"];
    return enleverPartiesTexte(
      cheminFichier,
      motExacteASupprimer,
      motCommenceASupprimer,
    );
  }

  selectAgence() {
    const agenceDebiteur = this.elements.agenceDebiteurInput.value;
    const url = this.config.urls.agenceFetch.replace(
      ":agenceDebiteur",
      agenceDebiteur,
    );
    this.toggleSpinner(true);
    this.fetchManager
      .get(url)
      .then((services) => {
        this.updateServiceOptions(services);
      })
      .catch((error) => console.error("Error:", error))
      .finally(() => this.toggleSpinner(false));
  }

  toggleSpinner(show) {
    if (this.elements.spinnerService) {
      this.elements.spinnerService.style.display = show
        ? "inline-block"
        : "none";
    }
    if (this.elements.serviceContainer) {
      this.elements.serviceContainer.style.display = show ? "none" : "block";
    }
  }

  updateServiceOptions(services) {
    const serviceDebiteurInput = this.elements.serviceDebiteurInput;
    while (serviceDebiteurInput.options.length > 0) {
      serviceDebiteurInput.remove(0);
    }
    for (var i = 0; i < services.length; i++) {
      var option = document.createElement("option");
      option.value = services[i].value;
      option.text = services[i].text;
      serviceDebiteurInput.add(option);
    }
  }
}
