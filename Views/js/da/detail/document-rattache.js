document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("file-viewer");
  const height = window.innerHeight;

  // Récupérer tous les éléments .file-item
  const fileItems = document.querySelectorAll(".file-item");

  // Types liés à un BC (via data-related-num-bc) et classe CSS de surlignage associée
  const RELATED_CLASS = { FACBL: "related-fac-bl", DDP: "related-ddp" };
  const ALL_RELATED_CLASSES = Object.values(RELATED_CLASS);
  const norm = (value) => (value ?? "").trim().toUpperCase();

  // Regrouper par type pour un accès rapide
  const fileItemsByType = { BC: [], FACBL: [], DDP: [] };
  fileItems.forEach((item) => {
    const type = item.dataset.docLabelType;
    if (type in fileItemsByType) fileItemsByType[type].push(item);
  });

  // Construire les relations BC → (FACBL | DDP) et (FACBL | DDP) → BC
  const bcByNum = new Map(); // numéro BC normalisé → élément BC
  fileItemsByType.BC.forEach((bc) =>
    bcByNum.set(norm(bc.querySelector("small").innerText), bc)
  );

  const bcToChildren = new Map(); // one to many : numéro BC normalisé → [FACBL|DDP]
  const childToBc = new Map(); // one to one : FACBL|DDP → BC
  Object.keys(RELATED_CLASS).forEach((type) => {
    fileItemsByType[type].forEach((child) => {
      const num = norm(child.dataset.relatedNumBc);
      if (!num) return;
      if (!bcToChildren.has(num)) bcToChildren.set(num, []);
      bcToChildren.get(num).push(child);
      if (bcByNum.has(num)) childToBc.set(child, bcByNum.get(num));
    });
  });

  // Éléments concernés pour toggle related
  const relatedFileItems = [
    ...fileItemsByType.BC,
    ...fileItemsByType.FACBL,
    ...fileItemsByType.DDP,
  ];

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
    if (!["BC", ...Object.keys(RELATED_CLASS)].includes(docLabelType)) return;

    // Retirer l'effet des éléments liés
    relatedFileItems.forEach((item) => {
      item.classList.remove(...ALL_RELATED_CLASSES);
      item.closest(".list-file-item")?.classList.remove(...ALL_RELATED_CLASSES);
    });

    const highlight = (item, cssClass) => {
      item.classList.add(cssClass);
      item.closest(".list-file-item")?.classList.add(cssClass);
    };

    if (docLabelType === "BC") {
      // FACBL et DDP liés au BC, chacun dans la couleur de son type
      (bcToChildren.get(norm(fileName)) || []).forEach((child) =>
        highlight(child, RELATED_CLASS[child.dataset.docLabelType])
      );
    } else {
      // BC lié au FACBL / DDP, dans la couleur du type cliqué
      const relatedBc = childToBc.get(selectedItem);
      if (relatedBc) highlight(relatedBc, RELATED_CLASS[docLabelType]);
    }
  }
});
