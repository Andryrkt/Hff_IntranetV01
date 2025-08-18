import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterUneLigne } from "./dal";

document.addEventListener("DOMContentLoaded", function () {
  localStorage.setItem("daWithDitLineCounter", 0); // initialiser le compteur de ligne pour la création d'une DA avec DIT

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault();

    if (document.getElementById("children-container").childElementCount > 0) {
      Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        html: `Voulez-vous vraiment envoyer la demande?`,
        icon: "warning",
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Oui, Envoyer",
        cancelButtonText: "Non, annuler",
      }).then((result) => {
        if (result.isConfirmed) {
          displayOverlay(true);
          document.getElementById("child-prototype").remove();
          document.getElementById("myForm").submit();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          // ❌ Si l'utilisateur annule
          Swal.fire({
            icon: "info",
            title: "Annulé",
            text: "Votre demande n'a pas été envoyée.",
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
