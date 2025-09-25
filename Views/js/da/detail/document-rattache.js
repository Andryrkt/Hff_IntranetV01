document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("file-viewer");
  const height = window.innerHeight;
  // Tous les éléments .file-item
  const fileItems = document.querySelectorAll(".file-item");

  // Seulement ceux avec data-doc-label-type="BC"
  const bcFileItems = Array.from(fileItems).filter(
    (item) => item.dataset.docLabelType === "BC"
  );

  // Seulement ceux avec data-doc-label-type="FACBL"
  const facblFileItems = Array.from(fileItems).filter(
    (item) => item.dataset.docLabelType === "FACBL"
  );

  // Éléments pour lesquels on va ajouter un événement spécifique (BC ou FACBL)
  const relatedFileItems = Array.from(fileItems).filter((item) =>
    ["BC", "FACBL"].includes(item.dataset.docLabelType)
  );

  fileItems.forEach((fileItem) => {
    // Clic sur un fichier (hors lien de téléchargement)
    fileItem.addEventListener("click", function (event) {
      if (event.target.closest("a")) return; // ignore le clic sur l'icône de téléchargement

      const downloadLink = this.querySelector("a");
      const docLabelType = this.dataset.docLabelType;
      const fileName = this.querySelector("small").innerText;
      const docType = downloadLink.dataset.docType;
      const filePath = downloadLink.href;
      let textHtml = "";

      toggleSelectedItem(this, fileItems);
      toggleRelatedItem(
        this,
        docLabelType,
        fileName,
        relatedFileItems,
        bcFileItems,
        facblFileItems
      );

      // Vérification côté JS avant affichage
      fetch(filePath, { method: "HEAD" })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Fichier introuvable");
          }

          // Cas fichier vide marqué par "-"
          if (filePath.endsWith("-")) {
            textHtml = `Aucun <strong class="text-danger">"${docType}"</strong> n'est actuellement rattaché à cette demande d'achat.`;
            Swal.fire({
              icon: "error",
              title: "Fichier inexistant",
              html: textHtml,
              confirmButtonText: "OK",
            });
            viewer.innerHTML = textHtml;
          }
          // Cas PDF
          else if (filePath.endsWith(".pdf")) {
            viewer.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="${height}px"/>`;
          }
          // Cas image // /i: insensible à la case
          else if (filePath.match(/\.(jpeg|jpg|png|gif)$/i)) {
            viewer.innerHTML = `<img src="${filePath}" class="img-fluid" alt="Image du document" />`;
          }
          // Cas format non supporté
          else {
            textHtml = `Le format du fichier du <strong class="text-danger">"${docType}"</strong> n'est pas pris en charge pour l'affichage.`;
            Swal.fire({
              icon: "error",
              title: "Fichier non supporté",
              html: textHtml,
              confirmButtonText: "OK",
            });
            viewer.innerHTML = textHtml;
          }
        })
        .catch(() => {
          textHtml = `Le fichier <strong class="text-danger">"${fileName}"</strong> du type <strong class="text-danger">"${docType}"</strong> est introuvable sur le serveur.`;
          Swal.fire({
            icon: "error",
            title: "Erreur de chargement",
            html: textHtml,
            confirmButtonText: "OK",
          });
          viewer.innerHTML = textHtml;
        });
    });

    // Clic sur le bouton de téléchargement
    const downloadLink = fileItem.querySelector("a");
    downloadLink.addEventListener("click", function (event) {
      event.preventDefault();

      const docType = this.dataset.docType;
      const filePath = this.href;

      if (filePath.endsWith("-")) {
        const textHtml = `Aucun document de type <strong class="text-danger">"${docType}"</strong> n'est actuellement associé à cette demande d'achat. Aucun fichier n'est donc disponible au téléchargement.`;

        Swal.fire({
          icon: "error",
          title: "Fichier inexistant",
          html: textHtml,
          confirmButtonText: "OK",
        });
      } else {
        // Télécharger manuellement
        const link = document.createElement("a");
        link.href = filePath;
        link.download = filePath.split("/").pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    });
  });

  function toggleSelectedItem(selectedItem, allItems) {
    // Retirer toutes les sélections
    allItems.forEach((item) => {
      item.classList.remove("selected");
      item.closest(".list-file-item")?.classList.remove("selected");
    });

    // Ajouter la sélection au fichier cliqué
    selectedItem.classList.add("selected");

    // Ajouter la sélection à son bloc parent
    selectedItem.closest(".list-file-item")?.classList.add("selected");
  }

  function toggleRelatedItem(
    selectedItem,
    docLabelType,
    fileName,
    relatedFileItems,
    bcFileItems,
    facblFileItems
  ) {
    if (["BC", "FACBL"].includes(docLabelType)) {
      // Retirer toutes les effets pour les élements liés
      relatedFileItems.forEach((item) => {
        item.classList.remove("related");
      });

      if (docLabelType === "BC") {
        // Seulement ceux avec data-doc-label-type="FACBL"
        const relatedFacBls = Array.from(facblFileItems).filter(
          (item) => item.dataset.relatedNumBc === fileName
        );
        relatedFacBls.forEach((relatedFacBl) => {
          relatedFacBl.classList.add("related"); // ajouter l'effet pour l'élement lié
        });
      } else {
        const relatedNumBc = selectedItem.dataset.relatedNumBc;
        const relatedBcs = Array.from(bcFileItems).filter(
          (item) => item.querySelector("small").innerText === relatedNumBc
        );
        relatedBcs.forEach((relatedBc) => {
          relatedBc.classList.add("related"); // ajouter l'effet pour l'élement lié
        });
      }
    }
  }
});
