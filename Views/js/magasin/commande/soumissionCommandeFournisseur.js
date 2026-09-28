import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { ApiRequestManager } from "../../api/ApiRequestManager";
import { displayOverlay } from "../../utils/ui/overlay";

const apiManager = new ApiRequestManager();

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm");
  const btnGenererPdf = document.getElementById("genererPdf");
  const contenuOriginal = btnGenererPdf.innerHTML;
  const validationBtn = document.getElementById("validationBtn");
  const iframe = document.getElementById("pdf-iframe");
  const viewerContainer = document.getElementById("viewer-container");
  const numCdeInput = document.getElementById("soumission_commande_numCmde");
  const hiddenNumCdeAValiderInput = document.getElementById(
    "soumission_commande_numCmdeAValider"
  );
  const hiddenGeneratedFilePath = document.getElementById(
    "soumission_commande_generatedFilePath"
  );

  numCdeInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      btnGenererPdf.click();
    }
  });
  btnGenererPdf.addEventListener("click", async function (e) {
    viewerContainer.classList.add("d-none");
    const numCde = numCdeInput.value.trim();
    if (!numCde) {
      Swal.fire({
        icon: "warning",
        title: "Numéro de commande requis",
        text: "Veuillez saisir un numéro de commande.",
        timer: 2500,
        showConfirmButton: false,
      });
      return;
    }

    try {
      // Passage à l'état "en cours de génération"
      btnGenererPdf.disabled = true;
      btnGenererPdf.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Génération en cours...';
      displayOverlay(
        true,
        "Veuillez patienter pendant la génération de PDF s'il vous plaît!"
      );

      const response = await apiManager.get(
        API_ENDPOINTS.generatePdfCdeFrnMag(numCde)
      );
      const pdfUrl = response.url || response;

      if (!pdfUrl) throw new Error("Aucune URL de PDF reçue");

      hiddenNumCdeAValiderInput.value = numCde;
      hiddenGeneratedFilePath.value = pdfUrl;

      console.log(hiddenGeneratedFilePath, hiddenNumCdeAValiderInput);

      iframe.src = `${pdfUrl}#zoom=${getOptimalZoom()}`;
      displayOverlay(false);
      btnGenererPdf.innerHTML = '<i class="fa-solid fa-check"></i> PDF généré';
      setTimeout(() => {
        btnGenererPdf.innerHTML = contenuOriginal;
        btnGenererPdf.disabled = false;
      }, 2000);
      validationBtn.style.display = "block";
      viewerContainer.classList.remove("d-none");
    } catch (error) {
      displayOverlay(false);
      console.error(error);
      Swal.fire({
        icon: "error",
        title: "Erreur",
        html: "Impossible de générer le PDF : <br>" + error.message,
      });
      btnGenererPdf.innerHTML =
        '<i class="fa-solid fa-triangle-exclamation"></i> Erreur';
      setTimeout(() => {
        btnGenererPdf.innerHTML = contenuOriginal;
        btnGenererPdf.disabled = false;
      }, 2000);
      validationBtn.style.display = "none";
      viewerContainer.classList.add("d-none");
    }
  });

  // ─── Hide loader when iframe loads ───
  iframe.addEventListener("load", function () {
    displayOverlay(false);
    btnGenererPdf.innerHTML = '<i class="fa-solid fa-check"></i> PDF généré';
    setTimeout(() => {
      btnGenererPdf.innerHTML = contenuOriginal;
      btnGenererPdf.disabled = false;
    }, 2000);
    viewerContainer.classList.remove("d-none");
    validationBtn.style.display = "block";
  });

  function getOptimalZoom() {
    const screenWidth = window.innerWidth;

    // Ajuste le zoom selon la largeur d'écran disponible
    let zoom;
    if (screenWidth < 600) zoom = 50;
    else if (screenWidth < 1024) zoom = 75;
    else if (screenWidth < 1600) zoom = 100;
    else zoom = 125;

    return zoom;
  }

  // ─── Validation (submission) ───

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    Swal.fire({
      title: "Confirmer la soumission",
      html: `Êtes-vous sûr de vouloir <strong style="color: #f8bb86;">soumettre</strong> cette demande ?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#198754",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Oui, Soumettre",
      cancelButtonText: "Non, Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        displayOverlay(
          true,
          "Veuillez patienter pendant la soumission de la commande s'il vous plaît!"
        );
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "action";
        hidden.value = "validate";
        form.appendChild(hidden);
        form.submit();
      } else {
        Swal.fire({
          icon: "info",
          title: "Annulé",
          text: "La soumission de la demande a été annulée.",
          timer: 2000,
          showConfirmButton: false,
        });
      }
    });
  });
});
