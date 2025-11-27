import { handleAvance } from "./handleAvanceIndemnite";
import { handleService } from "./agenceService";
import { formatFieldsToUppercaseAndSlice } from "./formatField";
import {
  calculTotal,
  calculTotalAutreDepense,
  calculTotalIndemnite,
  conditionDisableField,
  handleAutresDepenses,
  updateIndemnite,
  updateModePaiement,
} from "./depense";
import { calculateDaysAvance } from "./handleDate";
import { formatMontant, parseMontant } from "../utils/formatUtils";
import { handleAllField } from "./handleField";
import { displayOverlay } from "../utils/ui/overlay";

document.addEventListener("DOMContentLoaded", function () {
  localStorage.setItem("site", 0); // initialiser le site à 0
  localStorage.setItem("catg", 0);

  const avance = document.getElementById("mutation_form_avanceSurIndemnite");
  const site = document.getElementById("mutation_form_site");
  const matricule = document.getElementById("mutation_form_matriculeNomPrenom");
  const modePaiementLabelInput = document.getElementById(
    "mutation_form_modePaiementLabel"
  );
  const modePaiementValueInput = document.getElementById(
    "mutation_form_modePaiementValue"
  );
  const indemniteForfaitaireInput = document.getElementById(
    "mutation_form_indemniteForfaitaire"
  );
  const dateDebutInput = document.getElementById("mutation_form_dateDebut");
  const dateFinInput = document.getElementById("mutation_form_dateFin");
  const nombreJourAvance = document.getElementById(
    "mutation_form_nombreJourAvance"
  );
  const supplementJournalier = document.getElementById(
    "mutation_form_supplementJournaliere"
  );
  const autreDepense1 = document.getElementById("mutation_form_autresDepense1");
  const autreDepense2 = document.getElementById("mutation_form_autresDepense2");
  const totalIndemniteInput = document.getElementById(
    "mutation_form_totalIndemniteForfaitaire"
  );
  const totaAutreDepenseInput = document.getElementById(
    "mutation_form_totalAutresDepenses"
  );
  const totaGeneralInput = document.getElementById(
    "mutation_form_totalGeneralPayer"
  );

  const categorieInput = document.querySelector("#mutation_form_categorie");
  categorieInput.addEventListener("change", () => {
    const catgId = categorieInput.value;
    localStorage.setItem("categorie", catgId);
    if (site.value) {
      updateIndemnite(site.value, catgId);
    }
  });

  /** Gérer les champs requis ou non */
  handleAllField(avance.value);

  /** Agence et service */
  handleService();

  /** Gérer les requis ou non sur les autres dépenses */
  handleAutresDepenses();

  /** Avance sur indemnité de chantier */
  avance.addEventListener("change", function () {
    handleAvance(this.value);

    // ajout d'une nouvelle evenement qui sera utiliser plus tard
    const event = new Event("valueAdded");
    this.dispatchEvent(event);
  });

  /** Calcul de la date de différence entre Date Début et Date Fin */
  dateDebutInput.addEventListener("change", function () {
    calculateDaysAvance();
    let siteId = localStorage.getItem("site");
    let catgId = localStorage.getItem("categorie");
    if (
      site.value &&
      (siteId !== site.value ||
        (siteId === site.value && indemniteForfaitaireInput.value === "")) &&
      this.value &&
      avance.value === "OUI"
    ) {
      updateIndemnite(site.value, catgId);
    }
  });
  dateFinInput.addEventListener("change", function () {
    calculateDaysAvance();
    let siteId = localStorage.getItem("site");
    let catgId = localStorage.getItem("categorie");
    if (
      site.value &&
      (siteId !== site.value ||
        (siteId === site.value && indemniteForfaitaireInput.value === "")) &&
      this.value &&
      avance.value === "OUI"
    ) {
      updateIndemnite(site.value, catgId);
    }
  });

  /** Calcul de l'indemnité forfaitaire journalière */
  site.addEventListener("change", function () {
    localStorage.setItem("site", this.value);
    let catgId = localStorage.getItem("categorie");
    if (this.value && avance.value === "OUI") {
      updateIndemnite(this.value, catgId);
    }
  });

  /** Mode de paiement et valeur */
  matricule.addEventListener("change", function () {
    if (this.value) {
      console.log(this.value);

      updateModePaiement(this.value);
    }
  });
  modePaiementLabelInput.addEventListener("change", function () {
    if (matricule.value) {
      updateModePaiement(matricule.value);
      if (this.value === "VIREMENT BANCAIRE") {
        totaGeneralInput.classList.remove(
          "border",
          "border-2",
          "border-danger",
          "border-success",
          "border-opacity-75"
        );
      }
    }
  });
  modePaiementValueInput.addEventListener("input", function () {
    this.value = this.value.replace(/[^\d]/g, "").slice(0, 10);
  });

  /** Calculer Montant total Autre dépense et montant total général */
  autreDepense1.addEventListener("input", function () {
    this.value = formatMontant(parseInt(this.value.replace(/[^\d]/g, "")));
    conditionDisableField();
    calculTotalAutreDepense();
  });
  autreDepense2.addEventListener("input", function () {
    this.value = formatMontant(parseInt(this.value.replace(/[^\d]/g, "")));
    calculTotalAutreDepense();
  });

  /** Formater des données en majuscule */
  formatFieldsToUppercaseAndSlice();

  /** Calcul de l'indemnité total forfaitaire */
  supplementJournalier.addEventListener("input", function () {
    supplementJournalier.value = formatMontant(
      parseInt(this.value.replace(/[^\d]/g, ""))
    );
    calculTotalIndemnite();
  });

  /** Ajout de l'évènement personnalisé pour caluler le total de l'indemnité forfaitaire */
  nombreJourAvance.addEventListener("valueAdded", calculTotalIndemnite);

  /** Ajout de l'évènement personnalisé pour calculer le total général */
  avance.addEventListener("valueAdded", calculTotal);
  totalIndemniteInput.addEventListener("valueAdded", calculTotal);
  totaAutreDepenseInput.addEventListener("valueAdded", calculTotal);

  /** Evènement sur le formulaire */
  const myForm = document.getElementById("form-mutation");
  myForm.addEventListener("submit", function (event) {
    let montantTotal = document.getElementById(
      "mutation_form_totalGeneralPayer"
    );
    let errorMessage = document.querySelectorAll(".error-message");

    if (parseMontant(montantTotal.value) > 500000) {
      if (modePaiementLabelInput.value !== "VIREMENT BANCAIRE") {
        event.preventDefault();
        alert(
          "Le montant total général ne peut être supérieur à 500.000 Ariary si c'est pour le mode paiement MOBILE MONEY.\n Veuillez changer le mode paiement en VIREMENT BANCAIRE ou bien diminuer le montant total général."
        );
        montantTotal.classList.add(
          "border",
          "border-2",
          "border-danger",
          "border-opacity-75"
        );
        montantTotal.focus();
      }
    } else {
      errorMessage.forEach((element) => {
        if (element.textContent !== "") {
          event.preventDefault();

          if (element.classList.contains("date")) {
            dateFinInput.focus();
          } else if (element.classList.contains("agence")) {
            document.querySelector(".serviceDebiteur").focus();
          }
          return;
        }
      });
    }
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});
