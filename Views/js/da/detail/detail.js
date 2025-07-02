import { baseUrl } from "../../utils/config";
import { formaterNombre } from "../../utils/formatNumberUtils";
import { displayOverlay } from "../../utils/spinnerUtils";

document.addEventListener("DOMContentLoaded", function () {
  const allMontantTd = document.querySelectorAll("td.format-mtt");
  allMontantTd.forEach((mtt) => {
    mtt.innerText = formaterNombre(mtt.innerText);
  });

  /** Toggle button pour le + et - */
  document.querySelectorAll(".toggle-btn").forEach(function (button) {
    button.addEventListener("click", function () {
      const icon = this.querySelector("i");
      const parentRow = this.closest("tr");
      let nextRow = parentRow.nextElementSibling;

      // Toggle les lignes enfants jusqu'à ce qu'on tombe sur une nouvelle ligne parente
      while (nextRow && nextRow.classList.contains("child-row")) {
        nextRow.style.display =
          nextRow.style.display === "none" ? "table-row" : "none";
        nextRow = nextRow.nextElementSibling;
      }

      // Change le bouton de + à - et inversement
      icon.classList.toggle("fa-chevron-down");
      icon.classList.toggle("fa-chevron-up");
    });
  });

  /**
   * Suppression de ligne de DA
   */
  const deleteLineBtns = document.querySelectorAll(".delete-line-DA");
  deleteLineBtns.forEach((deleteLineBtn) => {
    deleteLineBtn.addEventListener("click", function () {
      let dalId = this.dataset.id;
      if (
        confirm(
          "Voulez-vous vraiment supprimer cette ligne de DA?\nAttention!!! Cette action est irréversible."
        )
      ) {
        displayOverlay(true);
        window.location = `${baseUrl}/demande-appro/delete-line-da/${dalId}`;
      }
    });
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);

  const conversationContainer = document.getElementById(
    "conversationContainer"
  );

  if (!conversationContainer) return;

  const interval = setInterval(() => {
    const firstChild = conversationContainer.firstElementChild;

    if (firstChild && firstChild.offsetHeight > 0) {
      // Le contenu est prêt, on peut scroller en bas
      conversationContainer.scrollTop = conversationContainer.scrollHeight;

      // Stoppe le setInterval
      clearInterval(interval);
    }
  }, 100);
});
