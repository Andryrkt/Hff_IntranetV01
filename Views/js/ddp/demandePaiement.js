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
  const numCommandeInput = document.getElementById(
    "demande_paiement_numeroCommande"
  );
  const numFactureInput = document.querySelector(
    "#demande_paiement_numeroFacture"
  );
  const montantInput = document.querySelector(
    "#demande_paiement_montantAPayer"
  );
  const typeId = numFactureInput.dataset.typeid;

  let isUpdatingCommande = false;
  let isUpdatingFacture = false;
  /**====================================
   * AUTOCOMPLETE numero Fournisseur
   *====================================*/
  const fetchManager = new FetchManager();

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
    // modePaiementInput.value = item.mode_paiement;
    ribFrnInput.value =
      item.rib && item.rib != 0 && item.rib.trim() !== "XXXXXXXXXXX"
        ? item.rib
        : "-";

    // Récupérer les facture
    if (typeId == 2) {
      listeFacture(item.num_fournisseur, typeId);
      listeCommande2(item.num_fournisseur, typeId);
      //  pour affichage de tableau de facture
      updateCommandesFournisseur(item.num_fournisseur, typeId);

      $("#demande_paiement_numeroFacture").on("change", () => {
        changeCommandeSelonFacture(item.num_fournisseur, typeId);
      });

      // $("#demande_paiement_numeroCommande").on("change", () => {
      //   changeFactureSelonCommande(item.num_fournisseur, typeId);
      // });
    } else {
      $("#demande_paiement_numeroCommande").on("change", async function () {
        const numCdes = $(this).val();
        console.log("Valeur tableau à envoyer :", numCdes);
        let numCde;
        if (numCdes.length == 0) {
          numCde = 0;
        } else {
          numCde = numCdes.join(",");
        }

        console.log("Valeur string à envoyer :", numCdes);
        try {
          const montants = await fetchManager.get(
            `api/montant-commande/${numCde}`
          );
          if (this.length != 0) {
            montantInput.value = montants[0].montantcde;
          } else {
            montantInput.value = "";
          }
        } catch (err) {
          console.error("Erreur lors de la récupération des montants :", err);
        }
      });

      //  Récupérer les commandes du fournisseur après la sélection
      listeCommande(item.num_fournisseur, typeId);
    }
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

  /** =========================
   * numero commande
   *==========================*/

  async function listeCommande(numFournisseur, id_type) {
    try {
      const commandes = await fetchManager.get(
        `api/num-cde-frn/${numFournisseur}/${id_type}`
      );

      ajoutDesOptions(numCommandeInput, commandes.numCdes);
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    }
  }

  /** permet du choix multiple */
  $("#demande_paiement_numeroCommande").select2({
    placeholder: "-- Choisir les commandes --",
    allowClear: true,
    theme: "bootstrap",
    width: "100%",
  });

  async function listeCommande2(numFournisseur, id_type) {
    try {
      const commandes = await fetchManager.get(
        `api/num-cde-frn/${numFournisseur}/${id_type}`
      );

      const listeCommande = transformTab(commandes.listeGcot, "Numero_PO");

      ajoutDesOptions(numCommandeInput, listeCommande);
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    }
  }

  function transformTab(data, index = "") {
    return [
      ...new Map(
        data.map((el) => [
          el[index],
          {
            label: el[index],
            value: el[index],
          },
        ])
      ).values(),
    ];
  }

  function ajoutDesOptions(inputElement, data) {
    // Supprime les anciennes options
    inputElement.innerHTML = "";

    // Ajoute les nouvelles options
    data.forEach((item) => {
      let option = new Option(item.label, item.value);
      inputElement.appendChild(option);
    });
  }

  /**================
   * numéro facture
   ==================*/
  function afficherSpinner(containerSelector = "body") {
    // Création du style de l'animation si non déjà présent
    if (!document.getElementById("spinner-style")) {
      const style = document.createElement("style");
      style.id = "spinner-style";
      style.innerHTML = `
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `;
      document.head.appendChild(style);
    }

    // Création dynamique du spinner
    const spinner = document.createElement("div");
    spinner.id = "spinner";
    spinner.style.display = "flex";
    spinner.style.justifyContent = "center";
    spinner.style.alignItems = "center";
    spinner.style.margin = "1em 0";

    spinner.innerHTML = `
      <div style="
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
      "></div>
    `;

    const container =
      document.querySelector(containerSelector) || document.body;
    container.appendChild(spinner);
  }

  function supprimerSpinner() {
    const spinner = document.getElementById("spinner");
    if (spinner) spinner.remove();
  }

  async function listeFacture(numFournisseur, typeId) {
    try {
      console.log(numFournisseur);
      // afficherSpinner(numFactureInput);

      numFactureInput.innerHTML = "";
      numCommandeInput.innerHTML = "";
      montantInput.value = 0;

      const commandes = await fetchManager.get(
        `api/num-cde-frn/${numFournisseur}/${typeId}`
      );

      const listeFacture = transformTab(commandes.listeGcot, "Numero_Facture");

      ajoutDesOptions(numFactureInput, listeFacture);

      //afficher la liste des fichiers
      console.log(commandes.listeGcot);
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    }
    // finally {
    //   supprimerSpinner();
    // }
  }

  /**
   * permet du choix multiple */
  $("#demande_paiement_numeroFacture").select2({
    placeholder: "-- Choisir les factures --",
    allowClear: true,
    theme: "bootstrap",
    width: "100%",
  });

  async function changeCommandeSelonFacture(numFournisseur, typeId) {
    if (isUpdatingFacture) return; // évite le rebouclage
    isUpdatingCommande = true;

    const numFacs = $("#demande_paiement_numeroFacture").val(); // tableau de factures sélectionnées
    console.log("Factures sélectionnées :", numFacs);
    try {
      const commandes = await fetchManager.get(
        `api/num-cde-frn/${numFournisseur}/${typeId}`
      );

      // Filtrer les factures correspondant à au moins une facture sélectionnée
      const facturesCorrespondantes = commandes.listeGcot.filter((f) =>
        numFacs.includes(f.Numero_Facture)
      );

      // Extraire les Numero_PO uniques
      const numerosPO = [
        ...new Set(facturesCorrespondantes.map((f) => f.Numero_PO)),
      ];

      recupFichier(facturesCorrespondantes);

      console.log("Numero_PO à sélectionner :", numerosPO);

      // Définir les valeurs sélectionnées directement
      $(numCommandeInput).val(numerosPO).trigger("change");

      const facturesString = facturesCorrespondantes
        .map((f) => f.Numero_Facture) // extrait chaque numéro
        .join(",");
      if (numFacs.length === 0) {
        montantInput.value = 0;
      }

      console.log(facturesCorrespondantes, facturesString);
      const montantFacture = await fetchManager.get(
        `api/montant-facture/${numFournisseur}/${facturesString}/${typeId}`
      );
      console.log(formaterNombre(montantFacture[0], " "));

      montantInput.value = formaterNombre(montantFacture[0], " ");
    } catch (error) {
      console.error(
        "Erreur lors de la récupération du montant facture :",
        error
      );
    } finally {
      isUpdatingCommande = false;
    }
  }

  async function changeFactureSelonCommande(numFournisseur, typeId) {
    if (isUpdatingCommande) return;
    isUpdatingFacture = true;

    const numCdes = $("#demande_paiement_numeroCommande").val(); // tableau de factures sélectionnées
    console.log("Commande sélectionnées :", numCdes);

    try {
      const commandes = await fetchManager.get(
        `api/num-cde-frn/${numFournisseur}/${typeId}`
      );

      // Filtrer les factures correspondant à au moins une facture sélectionnée
      // const commandeCorrespondantes = commandes.listeGcot.filter((f) =>
      //   numCdes.includes(f.Numero_PO)
      // );

      // Extraire les Numero_PO uniques
      // const numerosFac = [
      //   ...new Set(commandeCorrespondantes.map((f) => f.Numero_Facture)),
      // ];

      // recupFichier(commandeCorrespondantes);
      // console.log("Numero_facture à sélectionner :", numerosFac);

      // Définir les valeurs sélectionnées directement
      $(numFactureInput).val(numerosFac).trigger("change");
    } catch (error) {
      console.error("Erreur lors de la récupération des commandes :", error);
    } finally {
      isUpdatingFacture = false;
    }
  }

  async function recupFichier(cdeFacCorrespondantes) {
    const numerosDossier = [
      ...new Set(cdeFacCorrespondantes.map((f) => f.Numero_Dossier_Douane)),
    ];

    console.log("Numero_Dossier_Douane :", numerosDossier);

    let dossiers = [];

    // Utiliser une boucle for...of pour pouvoir await
    for (const numero of numerosDossier) {
      try {
        const docs = await fetchManager.get(`api/liste-doc/${numero}`);
        dossiers.push(...docs); // Ajoute les fichiers au tableau
      } catch (error) {
        console.error(
          `Erreur lors de la récupération des fichiers pour le dossier ${numero} :`,
          error
        );
      }
    }

    // Afficher dans une liste UL
    const liste = document.getElementById("liste_fichiers");
    liste.innerHTML = ""; // Vider avant d'ajouter

    dossiers.forEach((fichier) => {
      const nom = nomFichier(fichier.Nom_Fichier);
      const li = document.createElement("li");
      const a = document.createElement("a");

      // Construction CORRECTE de l'URL
      const baseUrl = window.location.origin; // Récupère http://172.20.11.32
      const encodedPath = encodeURIComponent(fichier.Nom_Fichier);
      a.href = `${baseUrl}/Hffintranet/api/recuperer-fichier?path=${encodedPath}`;

      console.log("URL générée:", a.href); // Pour vérification

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

  /**============================================
   * AFFICHAGE LISTE TABLEAU FACTURE
   *============================================*/
  /**
   * Permet d'afficher le tableau de facture
   * @param {string} numFournisseur
   */
  async function updateCommandesFournisseur(numFournisseur, typeId) {
    const commandes = await fetchManager.get(
      `api/num-cde-frn/${numFournisseur}/${typeId}`
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
  }

  //fonction qui permet de fusionner les ligne du tableau facture
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
    console.log(numeroDossier);

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
      // const fileName = nomFichier(nomFichie);
      // const url = `${baseUrl}/api/recuperer-fichier/${fileName}`;
      // console.log(url);

      window.open(
        "file://192.168.0.15/GCOT_DATA/TRANSIT/DD1297A24/PDV_23575724.PDF",
        "_blank"
      );
    } catch (error) {
      console.error("Erreur lors de l'ouverture du fichier : ", error);
    }
  }

  /** ============================
   * FICHIER
   * =============================*/
  const fileInput1 = document.querySelector("#demande_paiement_pieceJoint01");
  initializeFileHandlersNouveau("1", fileInput1);

  const fileInput2 = document.querySelector("#demande_paiement_pieceJoint02");
  initializeFileHandlersNouveau("2", fileInput2);

  const fileInput3 = document.querySelector("#demande_paiement_pieceJoint03");
  initializeFileHandlersMultiple("3", fileInput3);

  const fileInput4 = document.querySelector("#demande_paiement_pieceJoint04");
  initializeFileHandlersNouveau("4", fileInput4);

  /**==================================================
   * sweetalert pour le bouton Enregistrer
   *==================================================*/
  setupConfirmationButtons();

  /** ========================================================================
   * recuperer l'agence debiteur et changer le service debiteur selon l'agence
   *============================================================================*/
  const agenceDebiteurInput = document.querySelector(".agenceDebiteur");
  const serviceDebiteurInput = document.querySelector(".serviceDebiteur");
  const spinnerService = document.getElementById("spinner-service");
  const serviceContainer = document.getElementById("service-container");
  agenceDebiteurInput.addEventListener("change", selectAgence);

  function selectAgence() {
    const agenceDebiteur = agenceDebiteurInput.value;
    const url = `api/agence-fetch/${agenceDebiteur}`;
    toggleSpinner(true);
    fetchManager
      .get(url)
      .then((services) => {
        console.log(services);
        updateServiceOptions(services);
      })
      .catch((error) => console.error("Error:", error))
      .finally(() => toggleSpinner(false));
  }

  function toggleSpinner(show) {
    spinnerService.style.display = show ? "inline-block" : "none";
    serviceContainer.style.display = show ? "none" : "block";
  }

  function updateServiceOptions(services) {
    // Supprimer toutes les options existantes
    while (serviceDebiteurInput.options.length > 0) {
      serviceDebiteurInput.remove(0);
    }

    // Ajouter les nouvelles options à partir du tableau services
    for (var i = 0; i < services.length; i++) {
      var option = document.createElement("option");
      option.value = services[i].value;
      option.text = services[i].text;
      serviceDebiteurInput.add(option);
    }

    //Afficher les nouvelles valeurs et textes des options
    for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
      var option = serviceDebiteurInput.options[i];
      console.log("Value: " + option.value + ", Text: " + option.text);
    }
  }

  /**==========================================
   * blockage  d'ecriture du champ CONTACT
   *=============================================*/
  const constactInput = document.querySelector("#demande_paiement_contact");
  allowOnlyNumbers(constactInput);
  limitInputLength(constactInput, 10);

  /**==========================================
   * blockage  d'ecriture du champ MONTANT
   *=============================================*/

  // allowOnlyNumbers(montantInput);
  registerLocale("fr-custom", { delimiters: { thousands: " ", decimal: "," } }); // Enregistrer une locale personnalisée "fr-custom"
  setLocale("fr-custom"); // Utiliser la locale personnalisée

  montantInput.addEventListener("input", (e) => {
    montantInput.value = formatNumberSpecial(montantInput.value);
  });

  /**
   * TEST
   */

  // document.getElementById("tester").addEventListener("click", async () => {
  //   const cheminRelatif = "DD0070A25/PDV_10236125.PDF";
  //   const url = `/Hffintranet/api/recuperer-fichier?path=${encodeURIComponent(
  //     cheminRelatif
  //   )}`;

  //   try {
  //     const response = await fetch(url);
  //     const text = await response.text(); // récupération brute
  //     const data = JSON.parse(text); // conversion en objet

  //     if (data.success) {
  //       alert(data.message);

  //       // ⚠️ Windows uniquement, et fonctionne seulement si l'utilisateur a accès au chemin réseau
  //       const cheminWindows = data.chemin.replace(/\\\\/g, "\\\\"); // sécurité, double échappement
  //       const lienFile = `file://${cheminWindows.replace(/\\/g, "/")}`; // file:// + conversion en URL

  //       // Ouvrir dans une nouvelle fenêtre ou onglet
  //       window.open(lienFile, "_blank");
  //     } else {
  //       alert(data.message);
  //     }
  //   } catch (error) {
  //     console.error("Erreur JSON ou réseau :", error);
  //     alert("Erreur de communication avec le serveur.");
  //   }
  // });
});
