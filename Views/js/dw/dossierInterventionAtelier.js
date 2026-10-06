import {
  conversionEnKo,
  iconSelonTypeFile,
  afficherFichier,
  couleurDefondClick,
} from "./utils.js";
import { FetchManager } from "../api/FetchManager.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

document.addEventListener("DOMContentLoaded", function () {
  // Sélectionne toutes les lignes du premier tableau
  const rows = document.querySelectorAll(".clickable-row");
  const spinners = document.getElementById("spinners");
  const spinner = document.getElementById("spinner");

  rows.forEach(function (row) {
    row.addEventListener("click", function () {
      couleurDefondClick(row);
      // Récupère le numéro DIT de la ligne cliquée
      const numeroDit = this.dataset.dit;

      // Affiche le spinner
      spinner.style.display = "block";

      // Met à jour le titre avec le numéro DIT
      document.getElementById("numero-dit").textContent = numeroDit;
      console.log(numeroDit);

      // Appelle l'API via AJAX pour récupérer les détails
      const url = `api/dw-fetch/${numeroDit}`;
      fetchManager
        .get(url)
        .then((data) => {
          console.log(data);
          // Masque le spinner une fois les données chargées
          spinner.style.display = "none";

          // Remplace le tbody du tableau à chaque clic
          const newTbody = document.createElement("tbody");
          newTbody.id = "documents-tbody";
          const oldTbody = document.getElementById("documents-tbody");
          oldTbody.parentNode.replaceChild(newTbody, oldTbody);

          // Parcourt les données reçues et insère chaque document dans le tableau
          data.forEach((doc) => {
            // Conversion de la taille du fichier en kilo-octets (ko)
            const tailleFichierKo = doc.taille_fichier
              ? conversionEnKo(doc.taille_fichier) + " Ko"
              : "-";

            // Sélectionne l'icône en fonction de l'extension du fichier avec Font Awesome
            let icon = iconSelonTypeFile(doc.extension_fichier);

            //affichage statut et version or
            let statut = "-";
            let numVersion = "-";
            if (doc.nomDoc === "Ordre de réparation") {
              statut = doc.statut_or ? doc.statut_or : "-";
              numVersion = doc.numero_version ? doc.numero_version : "-";
            }

            const row = document.createElement("tr");

            row.innerHTML = `
                      <td>${icon}</td>
                      <td>${doc.nomDoc}</td>
                      <td>${doc.numero_doc}</td>
                      <td>${
                        doc.date_creation
                          ? new Date(doc.date_creation).toLocaleDateString()
                          : "-"
                      }</td>
                      <td>${
                        doc.date_modification
                          ? new Date(doc.date_modification).toLocaleDateString()
                          : "-"
                      }</td>
                      <td class="text-center">${numVersion}</td>
                      <td class="text-center">${doc.total_page ?? "-"}</td>
                      <td>${tailleFichierKo}</td>
                  `;

            // Ajoute la classe "clickable" à la ligne
            row.classList.add("clickable");

            // Ajoute un événement de clic pour afficher le fichier dans la page
            row.addEventListener("click", function () {
              couleurDefondClick(row);

              // Affiche le spinner avant d'afficher le fichier
              spinners.style.display = "block";

              afficherFichier(doc.chemin); // Appelle la fonction pour afficher le fichier

              // Masque le spinner après l'affichage du fichier
              spinners.style.display = "none";
            });

            newTbody.appendChild(row);
          });
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
          // Masque le spinner en cas d'erreur
          spinner.style.display = "none";
        });
    });
  });
});
