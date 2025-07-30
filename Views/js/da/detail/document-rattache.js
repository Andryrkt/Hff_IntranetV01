document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("file-viewer");
  const fileItems = document.querySelectorAll(".file-item");

  fileItems.forEach((fileItem) => {
    // Clic sur un fichier (hors lien de téléchargement)
    fileItem.addEventListener("click", function (event) {
      if (event.target.closest("a")) return; // ignore le clic sur l'icône de téléchargement

      const downloadLink = this.querySelector("a");
      const docType = downloadLink.dataset.docType;
      const filePath = downloadLink.href;
      const height = window.innerHeight;
      let textHtml = "";

      toggleSelectedItem(fileItem, fileItems);

      if (filePath.endsWith("-")) {
        textHtml = `Aucun <strong class="text-danger">"${docType}"</strong> n'est actuellement rattaché à cette demande d'achat.`;
        Swal.fire({
          icon: "error",
          title: "Fichier inexistant",
          html: textHtml,
          confirmButtonText: "OK",
        });
        viewer.innerHTML = textHtml;
      } else if (filePath.endsWith(".pdf")) {
        viewer.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="${height}px"/>`;
      } else if (filePath.match(/\.(jpeg|jpg|png|gif)$/i)) {
        // /i: insensible à la case
        viewer.innerHTML = `<img src="${filePath}" class="img-fluid" alt="Image du document" />`;
      } else {
        textHtml = `Le format du fichier du <strong class="text-danger">"${docType}"</strong> n'est pas pris en charge pour l'affichage.`;
        Swal.fire({
          icon: "error",
          title: "Fichier non supporté",
          html: textHtml,
          confirmButtonText: "OK",
        });
        viewer.innerHTML = textHtml;
      }
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
});
