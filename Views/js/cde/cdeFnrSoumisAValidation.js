import { couleurDefondClick } from "../dw/utils";
import { formaterNombre } from "../utils/formatNumberUtils";
import { changerFormatDate } from "../utils/dateUtils";

document.addEventListener("DOMContentLoaded", function () {
  // Sélectionne toutes les lignes du premier tableau
  const rows = document.querySelectorAll(".clickable-row");
  const spinner = document.getElementById("spinner");

  rows.forEach(function (row) {
    row.addEventListener("click", function () {
      couleurDefondClick(row);
      const numFournisseur = this.dataset.numfnr;
      // Affiche le spinner
      spinner.style.display = "block";

      const url = `/Hffintranet/api/cde-fnr-non-receptionner/${numFournisseur}`;
      fetch(url, {
        method: "GET",
        headers: {
          "Content-type": "application/json",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erreur HTTP " + response.status);
          }
          return response.json();
        })
        .then((data) => {
          console.log(data);

          // Remplace le tbody du tableau à chaque clic
          const newTbody = document.createElement("tbody");
          newTbody.id = "documents-tbody";
          const oldTbody = document.getElementById("documents-tbody");
          oldTbody.parentNode.replaceChild(newTbody, oldTbody);

          data.forEach((doc) => {
            const row = document.createElement("tr");

            let dateCde = changerFormatDate(doc.date_cde, "DD/MM/YYYY");
            let prixCdeTtc = formaterNombre(doc.prix_cde_ttc, ".", ",");
            let prixCdeTtcDevise = formaterNombre(
              doc.prix_cde_ttc_devise,
              ".",
              ","
            );

            row.innerHTML = `
                      <td>${doc.num_cde}</td>
                      <td>${dateCde}</td>
                      <td>${doc.num_fournisseur}</td>
                      <td>${doc.nom_fournisseur}</td>
                      <td>${doc.libelle_cde}</td>
                      <td class="text-end">${prixCdeTtc}</td>
                      <td class="text-end">${prixCdeTtcDevise}</td>
                      <td>${doc.devise_cde}</td>
                      <td>${doc.type_cde}</td>
                  `;

            // Ajoute la classe "clickable" à la ligne
            row.classList.add("clickable");

            newTbody.appendChild(row);
          });
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
          // Masque le spinner en cas d'erreur
          spinner.style.display = "none";
        })
        .finally(() => {
          // Masque le spinner une fois les données chargées
          spinner.style.display = "none";
        });
    });
  });
});
