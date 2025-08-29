import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterUneLigne } from "./dal";

document.addEventListener("DOMContentLoaded", function () {
  localStorage.setItem("daWithDitLineCounter", 0); // initialiser le compteur de ligne pour la création d'une DA avec DIT

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault(); // empêcher l'envoi immédiat
    const action = e.submitter.name; // 👉 nom (attribut "name") du bouton qui a déclenché le submit
    // Définition des paramètres selon l'action
    const actionsConfig = {
      enregistrerBrouillon: {
        title: "Confirmer l’enregistrement",
        html: `Souhaitez-vous enregistrer cette demande d’approvisionnement en brouillon ?<br><small class="text-primary"><strong><u>NB</u>: </strong>Elle ne sera pas transmise au service APPRO.</small>`,
        icon: "question",
        confirmButtonText: "Oui, Enregistrer",
        canceledText: "L’enregistrement en brouillon a été annulé.",
      },
      soumissionAppro: {
        title: "Confirmer la soumission",
        html: `Êtes-vous sûr de vouloir soumettre cette demande d’approvisionnement ?<br><small class="text-danger"><strong><u>NB</u>: </strong>Elle sera transmise au service APPRO pour traitement.</small>`,
        icon: "warning",
        confirmButtonText: "Oui, Soumettre",
        canceledText: "La soumission de la demande a été annulée.",
      },
    };

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

  /** Message */
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
