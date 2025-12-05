import { initialiserIdTabs, showTab } from "../utils/pageNavigation";
import { boutonRadio } from "./boutonRadio";
import { handleRowClick } from "./dalr";
import {
  handleAllButtonEvents,
  handleAllInputEvents,
  handleFormSubmit,
} from "./event";

document.addEventListener("DOMContentLoaded", function () {
  initialiserIdTabs(); // initialiser les ID des onglets pour la navigation
  showTab(); // afficher la page d'article sélectionné par l'utilisateur
  handleAllInputEvents(); // gérer les événements sur tous les champs d'entrée
  handleAllButtonEvents(); // gérer les événements sur tous les boutons
  handleFormSubmit(); // gérer les événements sur le submit du formulaire

  /**=============================================
   * Desactive le bouton OK si la cage à cocher n'est pas cocher
   *==============================================*/
  const cageACocherInput = document.querySelector(
    "#demande_appro_lr_collection_estValidee"
  );
  const boutonOkInput = document.querySelector("#bouton_ok");

  // Fonction pour activer ou désactiver le bouton
  function verifierCaseCochee() {
    if (cageACocherInput.checked) {
      boutonOkInput.classList.remove("d-none");
    } else {
      boutonOkInput.classList.add("d-none");
    }
  }

  // Initialiser l'état du bouton au chargement
  verifierCaseCochee();

  // Écouteur d'événement sur la case à cocher
  cageACocherInput.addEventListener("change", verifierCaseCochee);

  /**=================================================================
   * lorsqu'on clique sur le bouton radio et envoyer le  proposition
   *==================================================================*/
  boutonRadio();

  /**===========================================
   * EVENEMENT SUR LES LIGNES DU TABLEAU
   *============================================*/
  document.querySelectorAll('tr[role="button"]').forEach((row) => {
    row.addEventListener("click", handleRowClick);
  });
});
