import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsRecursiveTable } from "./tableHandler";
import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
import { baseUrl } from "../../utils/config";
const fetchManager = new FetchManager();

window.addEventListener("load", () => {
  displayOverlay(false);
});

document.addEventListener("DOMContentLoaded", function () {
  /*  1ᵉʳ appel : colonnes 0-3 selon le pivot que vous aviez déjà.
   *  2ᵉ appel : colonnes 4-5 selon la colonne 4.
   */
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0, 1, 2, 3, 4, 5], insertSeparator: true },
    { pivotIndex: 6, columns: [6, 7], insertSeparator: true },
    { pivotIndex: 8, columns: [8], insertSeparator: true },
  ]);
});

/** =========================================================*/
async function fetchFournisseurs() {
  return await fetchManager.get("api/numero-libelle-fournisseur");
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

/**===================================================
 * Autocomplete champ numero FOURNISSEUR
 *====================================================*/
const numFournisseurInput = document.querySelector("#cde_frn_list_numFrn");

function onSelectNumFournisseur(item) {
  numFournisseurInput.value = `${item.num_fournisseur}`;
}

new AutoComplete({
  inputElement: numFournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-num-fournisseur"),
  loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNumFournisseur,
});

/**===================================================
 * Autocomplete champ nom FOURNISSEUR
 *====================================================*/
const nomFournisseurInput = document.querySelector("#cde_frn_list_frn");

function onSelectNomFournisseur(item) {
  nomFournisseurInput.value = `${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: nomFournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-nom-fournisseur"),
  loaderElement: document.querySelector("#loader-nom-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNomFournisseur,
});

/**=============================================================
 * Click droite sur le numero commande (menu contextuel)
 *===============================================================*/
const menu = document.getElementById("menuContextuelGlobal");
const hiddenInputCde = document.getElementById("da_soumission_commande_id");
const hiddenInputDa = document.getElementById("da_soumission_da_id");
const hiddenInputNumOr = document.getElementById("da_soumission_num_or");
const statutAffiche = document.getElementById("statut-affiche");
const form = document.forms["da_soumission"];

document.addEventListener("contextmenu", function (event) {
  const targetCell = event.target.closest(".commande-cellule");
  if (!targetCell) return; // Ne fait rien si ce n’est pas une cellule cible

  event.preventDefault(); // Empêche le menu contextuel natif

  const commandeId = targetCell.dataset.commandeId;
  hiddenInputCde.value = commandeId;

  const numDa = targetCell.dataset.numDa;
  hiddenInputDa.value = numDa;

  const numOr = targetCell.dataset.numOr;
  hiddenInputNumOr.value = numOr;

  const statutBc = targetCell.dataset.statutBc;

  const positionCde = targetCell.dataset.positionCde;
  const positionCdeFacturer = ["FC", "FA", "CP"].includes(positionCde);

  const statutsTelechargeBC = [
    "Validé",
    "A envoyer au fournisseur",
    "BC envoyé au fournisseur",
    "Partiellement dispo",
    "Complet non livré",
    "Tous livrés",
    "Partiellement livré",
  ];

  if (statutsTelechargeBC.includes(statutBc)) {
    telechargerBcValide(commandeId);
  }

  const statutsBcEnvoyer = [
    "BC envoyé au fournisseur",
    "Partiellement dispo",
    "Complet non livré",
    "Tous livrés",
    "Partiellement livré",
  ];
  if (statutsBcEnvoyer.includes(statutBc)) {
    statutAffiche.style.display = "block";
    statutAffiche.innerHTML = `
      <p title="cliquer pour confirmer l'envoi"
         class="text-decoration-none text-dark cursor-pointer bg-success text-white border-0 rounded px-2 py-1">
         BC envoyé au fournisseur
      </p> <hr/>`;
    // if (statutBc !== "Tous livrés") { // selon le demande de hoby rahalahy le 25/09/2025
      //active le formulaire
      Array.from(form.elements).forEach((el) => (el.disabled = false)); // active tous les champs du formulaire
      form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
    // } else {
    //   //desactive le formulaire
    //   Array.from(form.elements).forEach((el) => (el.disabled = true)); // Désactive tous les champs du formulaire
    //   form.querySelector("button[type='submit']").classList.add("disabled"); //changer l'apparence du bouton
    // }
  } else if (statutBc == "A envoyer au fournisseur") {
    statutAffiche.style.display = "block";

    const overlay = document.getElementById("loading-overlays");
    overlay.classList.remove("hidden");
    const url = "api/da-envoie-cde"; // L'URL de votre route Symfony
    fetchManager
      .get(url, "text")
      .then((html) => {
        statutAffiche.innerHTML = html + "<hr>";

        // Ajouter un écouteur sur la soumission du formulaire
        document
          .getElementById("daCdeEnvoyer")
          .addEventListener("submit", function (event) {
            event.preventDefault();

            const formData = new FormData(this);

            let jsonData = {};
            formData.forEach((value, key) => {
              // Supprimer le préfixe `form_type_demande[...]`
              let cleanKey = key.replace(/^da_cde_envoyer\[(.*?)\]$/, "$1");
              jsonData[cleanKey] = value;
            });
            console.log(jsonData);

            // Génère le lien dynamiquement, avec une vraie URL (pas Twig)
            const urlLien = `${baseUrl}/demande-appro/changement-statuts-envoyer-fournisseur/${commandeId}/${jsonData.dateLivraisonPrevue}/${jsonData.estEnvoyer}`;
            window.location.href = urlLien;
          });
      })
      .catch((error) =>
        console.error("Erreur lors du chargement du formulaire:", error)
      )
      .finally(() => {
        overlay.classList.add("hidden");
      });

    //desactive le formulaire
    Array.from(form.elements).forEach((el) => (el.disabled = true)); // Désactive tous les champs du formulaire
    form.querySelector("button[type='submit']").classList.add("disabled"); //changer l'apparence du bouton
  } else if (statutBc == "A soumettre à validation") {
    statutAffiche.style.display = "none";

    //active le formulaire
    Array.from(form.elements).forEach((el) => (el.disabled = false)); // active tous les champs du formulaire
    form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
  } else {
    statutAffiche.style.display = "none";

    //desactive le formulaire
    Array.from(form.elements).forEach((el) => (el.disabled = true)); // Désactive tous les champs du formulaire
    form.querySelector("button[type='submit']").classList.add("disabled"); //changer l'apparence du bouton
  }

  menu.style.top = event.pageY + "px";
  menu.style.left = event.pageX + "px";
  menu.style.display = "block";
});

// Fermer le menu si clic ailleurs
document.addEventListener("click", function (event) {
  if (!menu.contains(event.target)) {
    menu.style.display = "none";
  }
});

function telechargerBcValide(commandeId) {
  const bcValideTelecharger = document.getElementById("bcValideTelecharger");
  bcValideTelecharger.innerHTML =
    '<button id="downloadBcBtn" class="btn btn-warning fw-bold"><i class="fas fa-download"></i> BC VALIDE</button> <hr/>';

  document
    .getElementById("downloadBcBtn")
    .addEventListener("click", async () => {
      // Lancement du téléchargement
      window.open(`${baseUrl}/api/generer-bc-valider/${commandeId}`);
    });
}
