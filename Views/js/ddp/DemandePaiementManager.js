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
import { baseUrl } from "../utils/config.js";

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
    }
  }

  initElements() {
    for (const key in this.config.selectors) {
      this.elements[key] = document.querySelector(this.config.selectors[key]);
    }
    // For elements that might be selected with getElementById
    this.elements.numCommandeInput = document.getElementById(
      this.config.selectors.numCommandeInput.substring(1),
    );
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
    // Activation sur le champ "Numéro Fournisseur"
    new AutoComplete({
      inputElement: this.elements.numFrnInput,
      suggestionContainer: this.elements.suggestionNumFournisseur,
      loaderElement: this.elements.loaderNumFournisseur, // Ajout du loader
      debounceDelay: 300, // Délai en ms
      fetchDataCallback: () => this.fetchFournisseurs(),
      displayItemCallback: (item) => this.displayFournisseur(item),
      onSelectCallback: (item) => this.onSelectFournisseur(item),
    });

    // Activation sur le champ "Nom Fournisseur"
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

    this.elements.agenceDebiteurInput.addEventListener("change", () =>
      this.selectAgence(),
    );
  }

  initFileUploads() {
    initializeFileHandlersNouveau("1", this.elements.fileInput1);
    initializeFileHandlersNouveau("2", this.elements.fileInput2);
    initializeFileHandlersMultiple("3", this.elements.fileInput3);
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
    } else {
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
      console.error("Erreur lors de la récupération des commandes :", error);
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

      const encodedPath = encodeURIComponent(fichier.Nom_Fichier);
      a.href = `${this.config.urls.recupererFichier}?path=${encodedPath}`;
      a.textContent = `Ouvrir ${nom}`;
      a.target = "_blank";

      // Gestion des erreurs
      a.onclick = async (e) => {
        e.preventDefault();

        // Créer un nouvel onglet immédiatement
        const newWindow = window.open("", "_blank");
        newWindow.document.write(`
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
        `);

        try {
          const response = await fetch(a.href);

          if (!response.ok) {
            throw new Error(await response.text());
          }

          const contentType = response.headers.get("content-type");
          const blob = await response.blob();
          const blobUrl = URL.createObjectURL(blob);

          // Solution robuste pour l'affichage PDF
          if (contentType.includes("pdf")) {
            newWindow.location.href = blobUrl;
          }
          // Solution pour les images
          else if (contentType.startsWith("image/")) {
            newWindow.document.body.innerHTML = `
                    <img src="${blobUrl}" style="max-width: 100%; max-height: 100vh">
                `;
          }
          // Solution générique
          else {
            const iframe = document.createElement("iframe");
            iframe.src = blobUrl;
            iframe.style = "width:100%; height:100vh; border:none";
            newWindow.document.body.innerHTML = "";
            newWindow.document.body.appendChild(iframe);
          }

          // Nettoyage lorsque la fenêtre se ferme
          newWindow.onunload = () => {
            URL.revokeObjectURL(blobUrl);
          };
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
        customRenderRow(row, index, data, columns),
      onRowClick: (row) => chargerDocuments(row.Numero_Dossier_Douane),
    });

    tableauComponent.mount(
      this.config.selectors.invoiceTableContainer.substring(1),
    );
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
    this.elements.spinnerService.style.display = show ? "inline-block" : "none";
    this.elements.serviceContainer.style.display = show ? "none" : "block";
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
