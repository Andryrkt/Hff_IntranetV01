import { API_ENDPOINTS } from "../../api/apiEndpoints";
import { ApiRequestManager } from "../../api/ApiRequestManager";
import { displayOverlay } from "../../utils/ui/overlay";
import { createMultiPdfDropzone } from "../../utils/multiPdfDropzone.js";

const apiManager = new ApiRequestManager();

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm");
  const btnGenererPdf = document.getElementById("genererPdf");
  const contenuOriginal = btnGenererPdf.innerHTML;
  const validationBtn = document.getElementById("validationBtn");
  const iframe = document.getElementById("pdf-iframe");
  const viewerContainer = document.getElementById("viewer-container");
  const pjSection = document.getElementById("pj-section");
  const badgeCmde = document.getElementById("badge-cmde");
  const badgePj = document.getElementById("badge-pj");
  const numCdeInput = document.getElementById("soumission_commande_numCmde");
  const hiddenGenerationToken = document.getElementById(
    "soumission_commande_generationToken"
  );

  // ─── Helpers UI ───
  function setBadge(badge, n) {
    const changed = badge.textContent !== String(n);
    badge.classList.toggle("d-none", !n);
    badge.textContent = n || "";
    if (n && changed) {
      badge.classList.remove("is-pop");
      void badge.offsetWidth; // relance l'animation
      badge.classList.add("is-pop");
    }
  }

  const pj = createMultiPdfDropzone({
    dropzone: document.getElementById("dropzone-2"),
    uploadBtn: document.getElementById("upload-btn-2"),
    input: document.getElementById("soumission_commande_piecesJointesPdf"),
    list: document.getElementById("file-list-2"),
    summary: document.getElementById("file-summary-2"),
    clearBtn: document.getElementById("clear-all-2"),
    onChange: (n) => setBadge(badgePj, n),
  });

  document.getElementById("file-summary-2").addEventListener("click", () => {
    bootstrap.Tab.getOrCreateInstance(
      document.getElementById("tab2-tab")
    ).show();
    document
      .getElementById("file-viewer")
      .scrollIntoView({ behavior: "smooth", block: "start" });
  });

  let btnTimer;
  const BTN_STATES = {
    loading: [
      '<i class="fa-solid fa-spinner fa-spin"></i> Génération en cours...',
      true,
    ],
    success: ['<i class="fa-solid fa-check"></i> PDF généré', false],
    error: ['<i class="fa-solid fa-triangle-exclamation"></i> Erreur', false],
  };
  function setBtnState(state) {
    clearTimeout(btnTimer);
    const [html, busy] = BTN_STATES[state] ?? [contenuOriginal, false];
    btnGenererPdf.innerHTML = html;
    btnGenererPdf.classList.toggle("btn-warning", busy);
    btnGenererPdf.classList.toggle("bouton-brouillon", !busy);
    if (state === "success" || state === "error") {
      btnTimer = setTimeout(() => setBtnState("idle"), 2000);
    }
  }

  function reveal(el) {
    el.classList.remove("d-none");
    el.classList.remove("pj-reveal");
    void el.offsetWidth;
    el.classList.add("pj-reveal");
  }

  function showResult(ok) {
    viewerContainer.classList.toggle("d-none", !ok);
    pjSection.classList.toggle("d-none", !ok);
    validationBtn.style.display = ok ? "block" : "none";
    setBadge(badgeCmde, ok ? 1 : 0);
    if (ok) {
      reveal(viewerContainer);
      reveal(pjSection);
    } else {
      hiddenGenerationToken.value = "";
      pj.reset();
    }
  }

  numCdeInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      btnGenererPdf.click();
    }
  });
  btnGenererPdf.addEventListener("click", async function () {
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
      setBtnState("loading");
      displayOverlay(
        true,
        "Veuillez patienter pendant la génération de PDF s'il vous plaît!"
      );

      const response = await apiManager.post(
        API_ENDPOINTS.generatePdfCdeFrnMag,
        { numCde }
      );
      const dto = response.data;
      if (!dto) throw new Error("Aucune information de commande reçue");

      const pdfUrl = dto.urlPDFCourt;
      if (!pdfUrl) throw new Error("Aucune URL de PDF reçue");

      hiddenGenerationToken.value = dto.generationToken;
      iframe.src = `${pdfUrl}#zoom=${getOptimalZoom()}`;
      displayOverlay(false);
      setBtnState("success");
      showResult(true);
    } catch (error) {
      displayOverlay(false);
      console.error(error);
      Swal.fire({
        icon: "error",
        title: "Erreur",
        html: "Impossible de générer le PDF : <br>" + error.message,
      });
      setBtnState("error");
      showResult(false);
    }
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

    if (!hiddenGenerationToken.value) {
      Swal.fire({
        icon: "warning",
        title: "PDF non généré",
        text: "Veuillez d'abord générer le PDF de la commande.",
      });
      return;
    }

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
        const hiddenValidateBtn = document.createElement("input");
        hiddenValidateBtn.type = "hidden";
        hiddenValidateBtn.name = "action";
        hiddenValidateBtn.value = "validate";
        form.appendChild(hiddenValidateBtn);
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
