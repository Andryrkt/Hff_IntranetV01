import { bootstrapNotify } from "../utils/notification/notification";
import { displayOverlay } from "../utils/ui/overlay";

document.addEventListener("DOMContentLoaded", function () {
  const checkAll = document.getElementById("inventaire_search_agence_all");
  const allInputCheckbox = document.querySelectorAll(".form-check-input");
  const allUploadFileAnalyse = document.querySelectorAll(
    ".upload-fichier-analyse"
  );
  const allDeleteFileAnalyse = document.querySelectorAll(
    ".delete-fichier-analyse"
  );
  const allInputFileAnalyse = document.querySelectorAll(".input-file-analyse");

  let afficherTous = true;
  for (const inputCheckbox of allInputCheckbox) {
    if (inputCheckbox.checked) {
      afficherTous = false;
      break;
    }
  }

  if (afficherTous) checkAllCheckbox(true);

  checkAll.addEventListener("click", () => checkAllCheckbox());

  allUploadFileAnalyse.forEach((uploadFileAnalyse) => {
    uploadFileAnalyse.addEventListener("click", function () {
      this.previousElementSibling.click();
    });
  });

  allInputFileAnalyse.forEach((inputFileAnalyse) => {
    inputFileAnalyse.addEventListener("change", function () {
      let file = this.files[0];
      let uploadUrl = this.dataset.uploadUrl;
      if (!file) {
        bootstrapNotify(
          "error",
          "Echec de l'opération",
          "Aucun fichier sélectionné."
        );
        return;
      }

      let formData = new FormData();
      displayOverlay(true, "Veuillez patienter pendant l'upload du fichier.");
      formData.append("fichier", file);
      fetch(uploadUrl, {
        method: "POST",
        body: formData,
      })
        .then(async (response) => {
          displayOverlay(false);

          // Lecture du texte renvoyé par Symfony
          const text = await response.text();

          if (response.ok) {
            // ✔ Succès
            bootstrapNotify("success", "Succès", text);

            // Optionnel : recharger la page automatiquement
            setTimeout(() => {
              window.location.reload();
            }, 800);
          } else {
            // ❌ Erreur envoyée par Symfony
            bootstrapNotify("error", "Erreur", text);
          }
        })
        .catch((error) => {
          displayOverlay(false);
          console.error("Erreur:", error);
          bootstrapNotify(
            "error",
            "Erreur réseau",
            "Impossible d'envoyer le fichier."
          );
        });
    });
  });

  allDeleteFileAnalyse.forEach((deleteFileAnalyse) => {
    deleteFileAnalyse.addEventListener("click", function () {
      let deleteUrl = this.dataset.deleteUrl;
      displayOverlay(
        true,
        "Veuillez patienter pendant la suppression du fichier."
      );
      fetch(deleteUrl, {
        method: "DELETE",
      })
        .then(async (response) => {
          displayOverlay(false);

          // Lecture du texte renvoyé par Symfony
          const text = await response.text();
          if (response.ok) {
            // ✔ Succès
            bootstrapNotify("success", "Succès", text);

            // Optionnel : recharger la page automatiquement
            setTimeout(() => {
              window.location.reload();
            }, 800);
          } else {
            // ❌ Erreur envoyée par Symfony
            bootstrapNotify("error", "Erreur", text);
          }
        })
        .catch((error) => {
          displayOverlay(false);
          console.error("Erreur:", error);
          bootstrapNotify(
            "error",
            "Erreur réseau",
            "Impossible de supprimer le fichier."
          );
        });
    });
  });

  function checkAllCheckbox(checked = false) {
    allInputCheckbox.forEach((inputCheckbox) => {
      checkAll.checked = checked ? true : checkAll.checked;
      inputCheckbox.checked = checkAll.checked;
    });
  }
});
