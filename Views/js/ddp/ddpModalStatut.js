import { FetchManager } from "../api/FetchManager";
import { formatDate } from "../planning/utils/date-utils";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

const statutInput = document.querySelector("#listeStatut");
const statutBody = document.querySelector("#statutBody");
const loader = document.querySelector("#statutLoader");

// console.log(statutInput, statutBody);

statutInput.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const numDdp = button.getAttribute("data-id"); // Extract info from data-* attributes

  const url = `ddp/api/historique-statut/${numDdp}`;

  // Affiche le spinner
  loader.style.display = "block";
  statutBody.innerHTML = ""; // Vide le tableau

  fetchManager
    .get(url)
    .then((data) => {
      statutBody.innerHTML = ""; // Clear previous data
      console.log(data);

      if (data.length > 0) {
        // Générer les lignes du tableau en fonction des données
        data.forEach((item) => {
          let row = `<tr>
                        <td class="fw-bold">${item.numeroDdp}</td>
                        <td>${item.statut}</td>
                        <td>${formatDate(item.date)}</td>
                    </tr>`;
          statutBody.innerHTML += row;
        });
      } else {
        // Si aucune donnée n'est disponible
        statutBody.innerHTML =
          '<tr><td colspan="3">Aucune donnée disponible.</td></tr>';
      }

      // Cache le spinner une fois les données affichées
      loader.style.display = "none";
    })
    .catch((error) => {
      console.error("Erreur de récupération :", error);
      statutBody.innerHTML =
        '<tr><td colspan="3">Erreur lors du chargement des données.</td></tr>';
      loader.style.display = "none";
    });
});

statutInput.addEventListener("hidden.bs.modal", function () {
  statutBody.innerHTML = ""; // Vider le tableau
  loader.style.display = "none"; // Toujours cacher le loader à la fermeture
});
