// utils.js

export function conversionEnKo(nombre) {
  return (nombre / 1024).toFixed(2).replace(".", ",");
}

export function iconSelonTypeFile(extension) {
  let icon = "";
  switch (extension.toLowerCase()) {
    case ".pdf":
      icon = '<i class="fas fa-file-pdf fs-4"></i>';
      break;
    case ".doc":
    case ".docx":
      icon = '<i class="fas fa-file-word fs-4"></i>';
      break;
    case ".xls":
    case ".xlsx":
      icon = '<i class="fas fa-file-excel fs-4"></i>';
      break;
    case ".jpg":
    case ".jpeg":
    case ".png":
      icon = '<i class="fas fa-file-image fs-4"></i>';
      break;
    case ".zip":
    case ".rar":
      icon = '<i class="fas fa-file-archive fs-4"></i>';
      break;
    case ".txt":
      icon = '<i class="fas fa-file-alt fs-4"></i>';
      break;
    default:
      icon = '<i class="fas fa-file fs-4"></i>';
  }

  return icon;
}

// Fonction pour afficher le fichier dans le conteneur
export function afficherFichier(cheminFichier) {
  console.log(cheminFichier);
  const fileUrl = `http://192.168.0.28/Upload/${cheminFichier}`;

  // console.log(fileUrl);

  const fileViewer = document.getElementById("file-viewer");
  fileViewer.innerHTML = `<iframe src="${fileUrl}#toolbar=0" width="100%" height="800px" frameborder="0"></iframe>`;
}

export function couleurDefondClick(row) {
  document.querySelectorAll("tr").forEach(function (r) {
    r.classList.remove("selected");
  });
  row.classList.add("selected");
}
