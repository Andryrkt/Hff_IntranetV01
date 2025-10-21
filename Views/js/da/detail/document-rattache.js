document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("file-viewer");
  const height = window.innerHeight;

  // Récupérer tous les éléments .file-item
  const fileItems = document.querySelectorAll(".file-item");

  // Regrouper par type pour un accès rapide
  const fileItemsByType = { BC: [], FACBL: [] };
  fileItems.forEach((item) => {
    const type = item.dataset.docLabelType;
    if (type in fileItemsByType) fileItemsByType[type].push(item);
  });

  // Construire les relations BC → FACBL et FACBL → BC
  const bcToFacbl = new Map(); // one to many
  fileItemsByType.BC.forEach((bc) => {
    const fileName = bc.querySelector("small").innerText;
    const relatedFacbls = fileItemsByType.FACBL.filter(
      (facbl) => facbl.dataset.relatedNumBc === fileName
    );
    bcToFacbl.set(fileName, relatedFacbls);
  });

  const facblToBc = new Map(); // one to one
  fileItemsByType.FACBL.forEach((facbl) => {
    const relatedNumBc = facbl.dataset.relatedNumBc;
    const relatedBc = fileItemsByType.BC.find(
      (bc) => bc.querySelector("small").innerText === relatedNumBc
    );
    if (relatedBc) facblToBc.set(facbl, relatedBc);
  });

  // Éléments concernés pour toggle related
  const relatedFileItems = Array.from(fileItems).filter((item) =>
    ["BC", "FACBL"].includes(item.dataset.docLabelType)
  );

  // Gestion du clic sur un fichier
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
      toggleRelatedItem(this, docLabelType, fileName);

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
      const docName = this.dataset.docName;
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
        link.download = docName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    });
  });

  // ---------------------
  // Fonctions
  // ---------------------

  function toggleSelectedItem(selectedItem, allItems) {
    // Retirer toutes les sélections
    allItems.forEach((item) => {
      item.classList.remove("selected");
      item.closest(".list-file-item")?.classList.remove("selected");
    });

    selectedItem.classList.add("selected"); // Ajouter la sélection au fichier cliqué
    selectedItem.closest(".list-file-item")?.classList.add("selected"); // Ajouter la sélection à son bloc parent
  }

  function toggleRelatedItem(selectedItem, docLabelType, fileName) {
    if (!["BC", "FACBL"].includes(docLabelType)) return;

    // Retirer l'effet des éléments liés
    relatedFileItems.forEach((item) => {
      item.classList.remove("related");
      item.closest(".list-file-item")?.classList.remove("related");
    });

    if (docLabelType === "BC") {
      const relatedFacbls = bcToFacbl.get(fileName) || []; // Chercher les FACBL liés au BC
      relatedFacbls.forEach((item) => {
        item.classList.add("related");
        item.closest(".list-file-item")?.classList.add("related");
      });
    } else if (docLabelType === "FACBL") {
      const relatedBc = facblToBc.get(selectedItem); // Chercher le BC lié au FACBL
      if (relatedBc) {
        relatedBc.classList.add("related");
        relatedBc.closest(".list-file-item")?.classList.add("related");
      }
    }
  }
});
