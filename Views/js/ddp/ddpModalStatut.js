import { FetchManager } from "../api/FetchManager";
import { formatDate } from "../planning/utils/date-utils";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

const statutInput = document.querySelector("#listeStatut");
const statutBody = document.querySelector("#statutBody");
const loader = document.querySelector("#statutLoader");

// console.log(statutInput, statutBody);

if (statutInput) {
  statutInput.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const numDdp = button.getAttribute("data-id"); // Extract info from data-* attributes

    const url = `ddp/api/historique-statut/${numDdp}`;

    // Affiche le spinner
    if (loader) loader.style.display = "block";
    if (statutBody) statutBody.innerHTML = ""; // Vide le tableau

    fetchManager
      .get(url)
      .then((data) => {
        if (statutBody) statutBody.innerHTML = ""; // Clear previous data
        console.log(data);

        if (data.length > 0) {
          // Générer les lignes du tableau en fonction des données
          data.forEach((item) => {
            let row = `<tr>
                        <td class="fw-bold">${item.numeroDdp}</td>
                        <td>${item.statut}</td>
                        <td>${formatDate(item.date)}</td>
                    </tr>`;
            if (statutBody) statutBody.innerHTML += row;
          });
        } else {
          // Si aucune donnée n'est disponible
          if (statutBody)
            statutBody.innerHTML =
              '<tr><td colspan="3">Aucune donnée disponible.</td></tr>';
        }

        // Cache le spinner une fois les données affichées
        if (loader) loader.style.display = "none";
      })
      .catch((error) => {
        console.error("Erreur de récupération :", error);
        if (statutBody)
          statutBody.innerHTML =
            '<tr><td colspan="3">Erreur lors du chargement des données.</td></tr>';
        if (loader) loader.style.display = "none";
      });
  });

  statutInput.addEventListener("hidden.bs.modal", function () {
    if (statutBody) statutBody.innerHTML = ""; // Vider le tableau
    if (loader) loader.style.display = "none"; // Toujours cacher le loader à la fermeture
  });
}
