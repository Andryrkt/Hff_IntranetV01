document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("file-viewer");
  const fileItems = document.querySelectorAll(".list-file-item");
  fileItems.forEach((fileItem) => {
    fileItem.addEventListener("click", function (event) {
      toggleSelectedItem(fileItem, fileItems);
      if (event.target.closest("a")) return;

      let docType = this.querySelector("a").dataset.docType;
      let filePath = this.querySelector("a").href;
      let textHtml = "";
      let height = window.innerHeight;
      console.log(filePath, docType);

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
      } else if (filePath.match(/\.(jpeg|jpg|png|gif)$/)) {
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

    fileItem.querySelectorAll("a").forEach((downloadLink) => {
      downloadLink.addEventListener("click", function (event) {
        event.preventDefault();

        const docType = downloadLink.dataset.docType;
        const filePath = downloadLink.href;

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
          const link = document.createElement("a"); // créer une balise a temporaire
          link.href = filePath;
          link.download = filePath.split("/").pop();
          document.body.appendChild(link);
          link.click(); // Aucun handler ne s'exécute ici
          document.body.removeChild(link);
        }
      });
    });
  });

  function toggleSelectedItem(selectedItem, allItem) {
    allItem.forEach((item) => {
      item.classList.remove("selected");
    });
    selectedItem.classList.add("selected");
  }
});
