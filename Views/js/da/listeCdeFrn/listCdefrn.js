import { mergeCellsRecursiveTable } from "./tableHandler";
import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
import { baseUrl } from "../../utils/config";
const fetchManager = new FetchManager();

document.addEventListener("DOMContentLoaded", function () {
  /*  1ᵉʳ appel : colonnes 0-3 selon le pivot que vous aviez déjà.
   *  2ᵉ appel : colonnes 4-5 selon la colonne 4.
   */
  mergeCellsRecursiveTable([
    { pivotIndex: 0, columns: [0, 1, 2, 3, 4, 5, 22], insertSeparator: true },
    { pivotIndex: 6, columns: [6, 7], insertSeparator: true },
    { pivotIndex: 8, columns: [8, 20], insertSeparator: true },
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
const hiddenInputTypeDa = document.getElementById("da_soumission_type_da");
const statutAffiche = document.getElementById("statut-affiche");
const bcValideTelecharger = document.getElementById("bcValideTelecharger"); // Déplacé ici
const form = document.forms["da_soumission"];
const DA_REAPPRO = "2";
const DAO = "0";
const DAD = "1";

function affichageStatutBcEnvoyerFournisseur() {
  statutAffiche.style.display = "block";
  statutAffiche.innerHTML = `
    <p title="cliquer pour confirmer l'envoi"
        class="text-decoration-none text-dark cursor-pointer bg-success text-white border-0 rounded px-2 py-1">
        BC envoyé au fournisseur
    </p> <hr/>`;
}

function desactiveTousLesChampsDuFormulaire() {
  //desactive le formulaire
  Array.from(form.elements).forEach((el) => (el.disabled = true)); // Désactive tous les champs du formulaire
  form.querySelector("button[type='submit']").classList.add("disabled"); //changer l'apparence du bouton
}

function activeTousLesChampsDuFormulaire() {
  Array.from(form.elements).forEach((el) => (el.disabled = false)); // active tous les champs du formulaire
  form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
}

function activeDesactiveFormualirePourSoumettreAValidation() {
  //desactive une partie du formulaire
  Array.from(form.elements).forEach((el) => {
    const value = el.value;

    if (
      value === "BL Reappro" ||
      value === "Facture + BL" ||
      value === "Ddpa"
    ) {
      el.disabled = true;
    }
  });
  form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
}

function activeDesactiveFormulairePourReappro() {
  //desactive une partie du formulaire
  Array.from(form.elements).forEach((el) => {
    const value = el.value;

    if (value === "BC" || value === "Facture + BL" || value === "Ddpa") {
      el.disabled = true;
    } else if (value === "BL Reappro") {
      // Choose one based on your needs:
      el.focus(); // Focus the element
      el.checked = true;
      el.style.borderColor = "blue";
    }
  });
  form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
}

function AffichageEtTraitementFOrmAEnvoyerFournisseur(commandeId) {
  // <<-- commandeId comme paramètre
  const overlay = document.getElementById("loading-overlays");
  overlay.classList.remove("hidden");
  const url = "api/da-envoie-cde"; // L'URL de votre route Symfony
  fetchManager
    .get(url, "text")
    .then((html) => {
      statutAffiche.innerHTML = html + "<hr>";
      statutAffiche.style.display = "block";
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

          // Génère le lien dynamiquement, avec une vraie URL (pas Twig)
          const urlLien = `${baseUrl}/demande-appro/changement-statuts-envoyer-fournisseur/${commandeId}/${jsonData.dateLivraisonPrevue}/${jsonData.estEnvoyer}`;
          window.location.href = urlLien;
        });
    })
    .catch((error) =>
      console.error("Erreur lors du chargement du formulaire:", error),
    )
    .finally(() => {
      overlay.classList.add("hidden");
    });
}

function activeDesactiveFormulairePourStatutsBcEnvoyer() {
  //desactive une partie du formulaire
  Array.from(form.elements).forEach((el) => {
    const value = el.value;

    if (value === "BC" || value === "BL Reappro" || value === "Ddpa") {
      // if (value === "BC" || value === "BL Reappro") {
      el.disabled = true;
    }
  });
  form.querySelector("button[type='submit']").classList.remove("disabled"); //changer l'apparence du bouton
}

document.addEventListener("contextmenu", function (event) {
  const targetCell = event.target.closest(".commande-cellule");
  if (!targetCell) return; // Ne fait rien si ce n’est pas une cellule cible

  event.preventDefault(); // Empêche le menu contextuel natif

  // --- Logique de réinitialisation ---
  statutAffiche.innerHTML = ""; // Vide le contenu précédent de statutAffiche
  bcValideTelecharger.innerHTML = ""; // Vide le contenu précédent de bcValideTelecharger
  activeTousLesChampsDuFormulaire(); // Réactive tous les champs du formulaire par défaut

  const commandeId = targetCell.dataset.commandeId;
  hiddenInputCde.value = commandeId;

  const numDa = targetCell.dataset.numDa;
  hiddenInputDa.value = numDa;

  const numOr = targetCell.dataset.numOr;
  hiddenInputNumOr.value = numOr;

  const typeDa = targetCell.dataset.typeDa;
  hiddenInputTypeDa.value = typeDa;

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

  if (statutsTelechargeBC.includes(statutBc) && typeDa !== DA_REAPPRO) {
    telechargerBcValide(commandeId);
  }

  const statutsBcEnvoyer = [
    "BC envoyé au fournisseur",
    "Partiellement dispo",
    "Complet non livré",
    "Tous livrés",
    "Partiellement livré",
  ];

  if (typeDa === DA_REAPPRO) {
    statutAffiche.style.display = "none"; // n'affiche pas le statut BC envoyé au fournisseur
    activeDesactiveFormulairePourReappro();
  } else if (statutsBcEnvoyer.includes(statutBc)) {
    affichageStatutBcEnvoyerFournisseur();
    activeDesactiveFormulairePourStatutsBcEnvoyer();
  } else if (statutBc == "A envoyer au fournisseur") {
    statutAffiche.style.display = "none";
    AffichageEtTraitementFOrmAEnvoyerFournisseur(commandeId); // <<-- Passé commandeId
    desactiveTousLesChampsDuFormulaire();
  } else if (statutBc == "A soumettre à validation") {
    statutAffiche.style.display = "none";
    activeDesactiveFormualirePourSoumettreAValidation();
  } else {
    statutAffiche.style.display = "none";
    desactiveTousLesChampsDuFormulaire();
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

// Fermer le menu si clic ailleurs
document.addEventListener("click", function (event) {
  if (!menu.contains(event.target)) {
    menu.style.display = "none";
  }
});

function telechargerBcValide(commandeId) {
  // const bcValideTelecharger = document.getElementById("bcValideTelecharger"); // Cett ligne n'est plus nécessaire ici
  bcValideTelecharger.innerHTML =
    '<button id="downloadBcBtn" class="btn btn-warning fw-bold"><i class="fas fa-download"></i> BC VALIDE</button> <hr/>';

  document
    .getElementById("downloadBcBtn")
    .addEventListener("click", async () => {
      // Lancement du téléchargement
      window.open(`${baseUrl}/api/generer-bc-valider/${commandeId}`);
    });
}

/** ===================================================
 * Modal du Date livraison prevu
 *==================================================*/
// Attendre que le DOM soit entièrement chargé
document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner le modal par son ID
  const modalDateLivraison = document.getElementById("dateLivraison");

  // Verifier si le modal existe sur la page
  if (modalDateLivraison) {
    //Ecouter l'événement 'show.bs.modal' qui est déclenché par Bootstrap
    // juste avant que le modal se soit affiché.
    modalDateLivraison.addEventListener("show.bs.modal", function (event) {
      // event.relatedTarget est l'élément qui a déclenché le modal (notre lien <a>)
      const button = event.relatedTarget;

      // Récupérer les données depuis les attributs data-* du lien
      const numeroCde = button.getAttribute("data-numero-cde");
      const dateActuelle = button.getAttribute("data-date-actuelle");

      // Mise à jour du contenu du modal
      const modalTitle = modalDateLivraison.querySelector(".modal-title");
      if (modalTitle) {
        modalTitle.textContent =
          "Modifier la date de livraison pour la commande n° : " + numeroCde;
      }

      // Pré-rempli le champ de date dans le formulaire du modal
      const dateInput = modalDateLivraison.querySelector(
        "#da_modal_date_livraison_dateLivraisonPrevue",
      );
      if (dateInput) {
        dateInput.value = dateActuelle;
      }

      // remplir le champ cacher avec le numero commande
      const numeroCdeInput = modalDateLivraison.querySelector(
        "#da_modal_date_livraison_numeroCde",
      );
      if (numeroCdeInput) {
        numeroCdeInput.value = numeroCde;
      }
    });
  }
});
