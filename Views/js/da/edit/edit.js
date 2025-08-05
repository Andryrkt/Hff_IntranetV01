import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterUneLigne, autocompleteTheFields } from "../new/dal";
import { eventOnFamille } from "../new/event";
import { formatAllField, onFileNamesInputChange } from "../new/field";

document.addEventListener("DOMContentLoaded", function () {
  buildIndexFromLinesAndBindEvents();

  document.querySelectorAll(".trombone-add-pj").forEach((el) => {
    el.addEventListener("click", function () {
      this.closest(".DAL-container") // le plus proche conteneur de la ligne DA
        .querySelector('input[type="file"]') // trouver l'input file dans ce conteneur
        .click();
    });
  });

  document
    .querySelectorAll('[id^="demande_appro_form_DAL_"][id$="_fileNames"]')
    .forEach((inputFile) => {
      inputFile.accept = ".pdf, image/*"; // Accepter les fichiers PDF et images
      inputFile.addEventListener("change", (event) =>
        onFileNamesInputChange(event)
      );
    });

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault();
    if (document.getElementById("children-container").childElementCount > 0) {
      document.getElementById("child-prototype").remove();
      document.getElementById("myForm").submit();
    } else {
      Swal.fire({
        icon: "warning",
        title: "Attention !",
        text: "Veuillez ajouter au moins un article avant d'enregistrer.",
      });
    }
  });

  document.querySelectorAll(".delete-DA").forEach((deleteButton) => {
    deleteButton.addEventListener("click", function () {
      deleteLigneDa(this);
    });
  });

  document.getElementById("info-icon").addEventListener("click", function () {
    Swal.fire({
      icon: "info",
      title: "Information utile",
      html: `
      <p class="mb-2">
        Pour faciliter votre recherche, vous pouvez saisir la <strong>référence de l’article</strong>
        ou bien sa <strong>désignation complète ou partielle</strong> 
        dans le champ <strong>surligné en jaune</strong> prévu à cet effet.
      </p>
    `,
      confirmButtonText: "Compris",
      confirmButtonColor: "#fbbb01", // couleur cohérente avec ton style
      customClass: {
        popup: "text-start", // alignement gauche professionnel
      },
    });
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

function buildIndexFromLinesAndBindEvents() {
  let maxIndex = 0;

  document.querySelectorAll("[id^='demande_appro_form_DAL_']").forEach((el) => {
    let match = el.id.match(/demande_appro_form_DAL_(\d+)$/);
    if (match) {
      let index = parseInt(match[1]);

      if (!isNaN(index) && index > maxIndex) {
        maxIndex = index;
      }

      eventOnFamille(index); // gestion d'évènement sur la famille et sous-famille à la ligne index
      formatAllField(index); // formater les champs à la ligne index
      autocompleteTheFields(index); // autocomplète les champs
    }
  });
  localStorage.setItem("index", maxIndex);
}

function deleteLigneDa(button) {
  Swal.fire({
    title: "Êtes-vous sûr(e) ?",
    html: `Voulez-vous vraiment supprimer cette ligne de demande d’achat?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Non, annuler",
  }).then((result) => {
    if (result.isConfirmed) {
      let prototypeId = button.getAttribute("prototype-id");
      let container = document.getElementById(
        `demande_appro_form_DAL_${prototypeId}`
      );
      let deletedCheck = document.getElementById(
        `demande_appro_form_DAL_${prototypeId}_deleted`
      );
      container.classList.add("d-none"); // cacher la ligne de DA
      deletedCheck.checked = true; // cocher le champ deleted

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
