const pageVisitsData = {}; // Injectez des données JSON côté serveur
const userActivityData = {};

// Graphique 1: Visites par page
new Chart(document.getElementById('pageVisitsChart'), {
  type: 'bar',
  data: {
    labels: Object.keys(pageVisitsData),
    datasets: [
      {
        label: 'Visites par page',
        data: Object.values(pageVisitsData),
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
      },
    ],
  },
});

// Graphique 2: Activité par utilisateur
new Chart(document.getElementById('userActivityChart'), {
  type: 'pie',
  data: {
    labels: Object.keys(userActivityData),
    datasets: [
      {
        label: 'Activité des utilisateurs',
        data: Object.values(userActivityData),
        backgroundColor: ['rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)'],
      },
    ],
  },
});
