import { displayOverlay } from "../../utils/ui/overlay";
import { ajouterUneLigne } from "./dal";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";

document.addEventListener("DOMContentLoaded", function () {
  buildIndexFromLines(); // initialiser le compteur de ligne pour la création d'une DA directe

  const actionsConfig = {
    enregistrerBrouillon: {
      title: "Confirmer l’enregistrement",
      html: `Souhaitez-vous enregistrer <strong class="text-primary">provisoirement</strong> cette demande ?<br><small class="text-primary"><strong><u>NB</u>: </strong>Elle ne sera pas transmise au service APPRO.</small>`,
      icon: "question",
      confirmButtonText: "Oui, Enregistrer",
      canceledText: "L’enregistrement provisoire a été annulé.",
    },
    soumissionAppro: {
      title: "Confirmer la soumission",
      html: `Êtes-vous sûr de vouloir <strong style="color: #f8bb86;">soumettre</strong> cette demande ?<br><small style="color: #f8bb86;"><strong><u>NB</u>: </strong>Elle sera transmise au service APPRO pour traitement.</small>`,
      icon: "warning",
      confirmButtonText: "Oui, Soumettre",
      canceledText: "La soumission de la demande a été annulée.",
    },
  };

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.querySelectorAll(".delete-DA").forEach((deleteButton) => {
    deleteButton.addEventListener("click", function () {
      deleteLigneDa(this);
    });
  });

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault(); // empêcher l'envoi immédiat
    const action = e.submitter.name; // 👉 nom (attribut "name") du bouton qui a déclenché le submit
    // Définition des paramètres selon l'action

    const config = actionsConfig[action];
    if (!config) return;

    if (document.getElementById("children-container").childElementCount > 0) {
      Swal.fire({
        title: config.title,
        html: config.html,
        icon: config.icon,
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: "Non, Annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          document.getElementById("child-prototype").remove();

          // ajouter un champ caché avec l’action choisie
          const hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = action;
          hidden.value = "1";
          document.getElementById("myForm").appendChild(hidden);

          document.getElementById("myForm").submit(); // n’émule pas le clic sur le bouton d’envoi → donc le name et value du bouton cliqué ne sont pas envoyés.
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulé",
            text: config.canceledText,
            timer: 2000,
            showConfirmButton: false,
          });
        }
      });
    } else {
      Swal.fire({
        icon: "warning",
        title: "Attention !",
        text: "Veuillez ajouter au moins un article avant d'enregistrer.",
      });
    }
  });
});

function getMaxIndexFromIds() {
  const elements = document.querySelectorAll(
    "div[id^='demande_appro_direct_form_DAL_'].DAL-container"
  );
  return Array.from(elements).reduce((max, el) => {
    const match = el.id.match(/^demande_appro_direct_form_DAL_(\d+)$/);
    if (match) {
      const value = parseInt(match[1], 10);
      return !isNaN(value) && value > max ? value : max;
    }
    return max;
  }, 0);
}

function getMaxLineFromValues() {
  const elements = document.querySelectorAll(
    "[id^='demande_appro_direct_form_DAL_'][id$='_numeroLigne']"
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
  localStorage.setItem("daDirectNumLigneMax", maxLine);

  console.log("Max index:", maxIndex);
  localStorage.setItem("daDirectLineCounter", maxIndex);
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
        `demande_appro_direct_form_DAL_${ligne}`
      );
      let deletedCheck = document.getElementById(
        `demande_appro_direct_form_DAL_${ligne}_deleted`
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

/**===========================================================================
 * Configuration des agences et services
 *============================================================================*/

// Attachement des événements pour les agences
document
  .getElementById("demande_appro_direct_form_debiteur_agence")
  .addEventListener("change", () => handleAgenceChange("debiteur"));
