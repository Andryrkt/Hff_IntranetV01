import {
  conversionEnKo,
  iconSelonTypeFile,
  afficherFichier,
  couleurDefondClick,
} from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
  // Sélectionne toutes les lignes du premier tableau
  const rows = document.querySelectorAll(".clickable-row");

  rows.forEach(function (row) {
    row.addEventListener("click", function () {
      couleurDefondClick(row);
      // Récupère le numéro DIT de la ligne cliquée
      const numeroDit = this.dataset.dit;

      // Met à jour le titre avec le numéro DIT
      document.getElementById("numero-dit").textContent = numeroDit;
      console.log(numeroDit);

      // Appelle l'API via AJAX pour récupérer les détails
      const url = `/Hffintranet/dw-fetch/${numeroDit}`;
      fetch(url, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
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

          // Parcourt les données reçues et insère chaque document dans le tableau
          data.forEach((doc) => {
            // Conversion de la taille du fichier en kilo-octets (ko)
            const tailleFichierKo = conversionEnKo(doc.taille_fichier);

            // Sélectionne l'icône en fonction de l'extension du fichier avec Font Awesome
            let icon = iconSelonTypeFile(doc.extension_fichier);

            //affichage statut et version or
            let statut = "";
            let numVersion = "";
            if (doc.nomDoc === "Ordre de réparation") {
              statut = doc.statut_or ? doc.statut_or : "";
              numVersion = doc.numero_version ? doc.numero_version : "";
            }

            const row = document.createElement("tr");

            row.innerHTML = `
                      <td>${icon}</td>
                      <td>${doc.nomDoc}</td>
                      <td>${doc.numero_doc}</td>
                      <td>${new Date(
                        doc.date_creation
                      ).toLocaleDateString()}</td>
                      <td>${new Date(
                        doc.date_modification
                      ).toLocaleDateString()}</td>
                      <td>${numVersion}</td>
                      <td>${statut}</td>
                      <td class="text-center">${doc.total_page}</td>
                      <td>${tailleFichierKo} ko</td>
                  `;

            // Ajoute la classe "clickable" à la ligne
            row.classList.add("clickable");

            // Ajoute un événement de clic pour afficher le fichier dans la page
            row.addEventListener("click", function () {
              couleurDefondClick(row);
              afficherFichier(doc.chemin); // Appelle la fonction pour afficher le fichier
            });

            newTbody.appendChild(row);
          });
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
        });
    });
  });
});
