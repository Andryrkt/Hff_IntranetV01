import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit";
import { getAllArticleStocke } from "../data/fetchData";
import { handleAllOldFileEvents } from "../newDirect/field";
import { initCentraleCodeDesiInputs } from "../newReappro/event";
import { ajouterUneLigne } from "./dapl";

document.addEventListener("DOMContentLoaded", async function () {
  const articleStockeList = await getAllArticleStocke();

  initCentraleCodeDesiInputs(
    "demande_appro_achat_form_codeCentrale",
    "demande_appro_achat_form_desiCentrale"
  );

  buildIndexFromLines(); // initialiser le compteur de ligne pour la création d'une DA achat

  handleAllOldFileEvents("demande_appro_achat_form_demandeApproParentLines"); // gérer les évènements sur les anciens fichiers

  // Attachement des événements pour les agences
  document
    .getElementById("demande_appro_achat_form_debiteur_agence")
    .addEventListener("change", () => handleAgenceChange("debiteur"));

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne(articleStockeList));

  document.querySelectorAll(".delete-DA").forEach((deleteButton) => {
    deleteButton.addEventListener("click", function () {
      deleteLigneDa(this);
    });
  });
});

function getMaxIndexFromIds() {
  const elements = document.querySelectorAll(
    "div[id^='demande_appro_achat_form_demandeApproParentLines_'].demandeApproParentLines-container"
  );
  return Array.from(elements).reduce((max, el) => {
    const match = el.id.match(
      /^demande_appro_achat_form_demandeApproParentLines_(\d+)$/
    );
    if (match) {
      const value = parseInt(match[1], 10);
      return !isNaN(value) && value > max ? value : max;
    }
    return max;
  }, 0);
}

function getMaxLineFromValues() {
  const elements = document.querySelectorAll(
    "[id^='demande_appro_achat_form_demandeApproParentLines_'][id$='_numeroLigne']"
  );
  return Array.from(elements).reduce((max, el) => {
    const value = parseInt(el.value, 10);
    if (isNaN(value)) {
      console.warn("Valeur non numérique trouvée pour numeroLigne:", el.value);
      return max; // ignore les valeurs invalides
    }
    return value > max ? value : max;
  }, 0);
}

function buildIndexFromLines() {
  const maxIndex = getMaxIndexFromIds();
  const maxLine = getMaxLineFromValues();

  // Log et stockage des résultats dans localStorage
  console.log("Numéro de ligne Max:", maxLine);
  localStorage.setItem("daAchatNumLigneMax", maxLine);

  console.log("Max index:", maxIndex);
  localStorage.setItem("daAchatLineCounter", maxIndex);
}

function deleteLigneDa(button) {
  Swal.fire({
    title: "Êtes-vous sûr(e) ?",
    html: `Voulez-vous vraiment supprimer cette ligne de demande d’achat?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Non, annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      let ligne = button.dataset.numLigne;
      let container = document.getElementById(
        `demande_appro_achat_form_demandeApproParentLines_${ligne}`
      );
      let deletedCheck = document.getElementById(
        `demande_appro_achat_form_demandeApproParentLines_${ligne}_deleted`
      );
      container.classList.add("d-none"); // cacher la ligne de DA
      deletedCheck.checked = true; // cocher le champ deleted

      console.log("ligne = ");
      console.log(ligne);
      console.log("container = ");
      console.log(container);
      console.log("deletedCheck = ");
      console.log(deletedCheck);
      console.log("deletedCheck.checked = ");
      console.log(deletedCheck.checked);

      Swal.fire({
        icon: "success",
        title: "Supprimé",
        text: "La ligne de demande d'achat a bien été supprimée avec succès.",
        timer: 2000,
        showConfirmButton: false,
      });
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      // ❌ Si l'utilisateur annule
      Swal.fire({
        icon: "info",
        title: "Annulé",
        text: "La suppression de la ligne de demande a été annulée.",
        timer: 2000,
        showConfirmButton: false,
      });
    }
  });
}
