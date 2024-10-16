function loadFile(extension, fileUrl) {
  const fileViewer = document.getElementById("fileViewer");

  if (extension === ".pdf") {
    // Si le fichier est un PDF, l'afficher dans un iframe
    fileViewer.innerHTML =
      '<iframe src="' +
      fileUrl +
      '" style="width:100%; height:100%;" frameborder="0"></iframe>';
  } else if (
    extension === ".doc" ||
    extension === ".docx" ||
    extension === ".xls" ||
    extension === ".xlsx"
  ) {
    // Pour les fichiers Word et Excel, proposer un téléchargement ou ouverture dans un nouvel onglet
    window.open(fileUrl, "_blank");
  } else {
    // Pour tout autre type de fichier, proposer un téléchargement
    fileViewer.innerHTML =
      '<p class="text-center">Ce type de fichier ne peut pas être affiché. <a href="' +
      fileUrl +
      '" download>Télécharger le fichier</a></p>';
  }
}
