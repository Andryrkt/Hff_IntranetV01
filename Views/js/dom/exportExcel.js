const exportExcelButton = document.querySelector("#export_excel");

//export excel
exportExcelButton.addEventListener("click", (e) => {
  e.preventDefault();
  dataDom();
});

function dataDom() {
  let url = `/Hffintranet/data-fetch`;
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      ExportExcel(data);
    })
    .catch((error) => console.error("Error:", error));
}

/**
 * cette fonction permet d'exporter les données filtrée ou non dans une fichier excel
 */
function ExportExcel(data) {
  // Crée une feuille Excel
  const worksheet = XLSX.utils.json_to_sheet(data);
  const workbook = XLSX.utils.book_new();

  // Ajoute les en-têtes à la feuille Excel
  const headers = [
    "Id",
    "Statut",
    "type document",
    "Numéro d'Ordre de Mission",
    "Date de Demande",
    "Motif de Déplacement",
    "Numero Matricule",
    "Nom",
    "Prénoms",
    "Mode de paiement",
    "Agence de service",
    "Date de Debut",
    "Date de Fin",
    "Nombre de Jour",
    "Client",
    "Numéro OR",
    "Lieu d'intervention",
    "Numero Vehicule",
    "Total Autres Dépenses",
    "Total Général Payer",
    "Devis",
  ];
  XLSX.utils.sheet_add_aoa(worksheet, [headers], {
    origin: "A1",
  });

  // Ajoute la feuille Excel au classeur
  XLSX.utils.book_append_sheet(workbook, worksheet, "Données");

  // Télécharge le fichier Excel
  XLSX.writeFile(workbook, "Exportation-Excel.xlsx", {
    compression: true,
  });
}
