document.addEventListener("DOMContentLoaded", function () {
  fetch("/planning")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log(data.data);
        generateTable(data.data);
      } else {
        console.error(
          "Erreur lors de la récupération des données de planning."
        );
      }
    })
    .catch((error) => console.error("Erreur:", error));
});

function generateTable(planningData) {
  const tableBody = document.getElementById("planningTableBody");
  tableBody.innerHTML = ""; // Réinitialiser le tableau avant de le remplir

  planningData.forEach((item) => {
    const row = document.createElement("tr");

    // Agence - Service
    const agenceServiceCell = document.createElement("td");
    agenceServiceCell.textContent = `${item.libsuc} - ${item.libserv}`;
    row.appendChild(agenceServiceCell);

    // idMat
    const idMatCell = document.createElement("td");
    idMatCell.textContent = item.idmat;
    row.appendChild(idMatCell);

    // Marque (CST)
    const marqueCell = document.createElement("td");
    marqueCell.textContent = item.marqueMat;
    row.appendChild(marqueCell);

    // Type
    const typeCell = document.createElement("td");
    typeCell.textContent = item.typemat;
    row.appendChild(typeCell);

    // Numéro de série
    const numSerieCell = document.createElement("td");
    numSerieCell.textContent = item.numserie;
    row.appendChild(numSerieCell);

    // Numéro de parc
    const numParcCell = document.createElement("td");
    numParcCell.textContent = item.numparc;
    row.appendChild(numParcCell);

    // Casier
    const casierCell = document.createElement("td");
    casierCell.textContent = item.casier;
    row.appendChild(casierCell);

    // Mois (Janv à Déc)
    const moisDetails = item.moisDetails || [];
    for (let i = 1; i <= 12; i++) {
      const moisCell = document.createElement("td");
      const moisItems = moisDetails.filter((mois) => mois.mois === i);

      if (moisItems.length > 0) {
        // Appliquer une classe CSS en fonction des quantités (logique à adapter)
        let classe = "";
        if (item.qtecdm === item.qteliv) {
          classe = "bg-success text-white"; // total livré
        } else if (
          item.qteliv > 0 &&
          item.qteliv + item.qteall !== item.qtecdm
        ) {
          classe = "bg-warning text-white"; // partiellement livré
        } else if (item.qtecdm !== item.qteall && item.qteliv === 0) {
          classe = "bg-info text-white"; // partiellement complet
        } else if (item.qtecdm === item.qteall && item.qteliv < item.qtecdm) {
          classe = "bg-primary text-white"; // complet non livré
        }
        moisCell.className = classe;

        // Ajouter les liens d'intervention
        moisItems.forEach((mois) => {
          const link = document.createElement("a");
          link.href = "#";
          link.setAttribute("data-bs-toggle", "modal");
          link.setAttribute("data-bs-target", "#listeCommande");
          link.setAttribute("data-id", mois.orIntv);
          link.className =
            "link-dark link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover";
          link.textContent = mois.orIntv;
          moisCell.appendChild(link);
          moisCell.appendChild(document.createElement("br"));
        });
      } else {
        moisCell.textContent = "-"; // Si pas de données pour le mois
      }
      row.appendChild(moisCell);
    }

    tableBody.appendChild(row);
  });
}
