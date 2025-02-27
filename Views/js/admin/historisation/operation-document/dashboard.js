// Données simulées, à remplacer par les données réelles fournies par le back-end
const operationTypesData = {
  Création: 120,
  Validation: 80,
  Modification: 60,
  Suppression: 40,
};

const userActivityData = {
  'Jean Dupont': 150,
  'Marie Leblanc': 120,
  'Pierre Martin': 90,
  'Lucie Bernard': 80,
};

const documentTypesData = {
  Factures: 180,
  Contrats: 130,
  Rapports: 100,
  'Notes internes': 70,
};

const operationStatusData = {
  Succès: 400,
  Échec: 30,
  'En attente': 40,
};

// Graphique 1 : Répartition des types d'opérations
new Chart(document.getElementById('operationTypesChart'), {
  type: 'pie',
  data: {
    labels: Object.keys(operationTypesData),
    datasets: [
      {
        label: "Répartition des types d'opérations",
        data: Object.values(operationTypesData),
        backgroundColor: [
          'rgba(255, 99, 132, 0.6)',
          'rgba(54, 162, 235, 0.6)',
          'rgba(255, 206, 86, 0.6)',
          'rgba(75, 192, 192, 0.6)',
        ],
      },
    ],
  },
});

// Graphique 2 : Activité des utilisateurs
new Chart(document.getElementById('userActivityChart'), {
  type: 'bar',
  data: {
    labels: Object.keys(userActivityData),
    datasets: [
      {
        label: 'Activité des utilisateurs (opérations)',
        data: Object.values(userActivityData),
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
      },
    ],
  },
});

// Graphique 3 : Répartition des types de documents
new Chart(document.getElementById('documentTypesChart'), {
  type: 'pie',
  data: {
    labels: Object.keys(documentTypesData),
    datasets: [
      {
        label: 'Répartition des types de documents',
        data: Object.values(documentTypesData),
        backgroundColor: [
          'rgba(153, 102, 255, 0.6)',
          'rgba(255, 159, 64, 0.6)',
          'rgba(255, 99, 132, 0.6)',
          'rgba(54, 162, 235, 0.6)',
        ],
      },
    ],
  },
});

// Graphique 4 : Statut des opérations
new Chart(document.getElementById('operationStatusChart'), {
  type: 'doughnut',
  data: {
    labels: Object.keys(operationStatusData),
    datasets: [
      {
        label: 'Statut des opérations',
        data: Object.values(operationStatusData),
        backgroundColor: [
          'rgba(75, 192, 192, 0.6)',
          'rgba(255, 159, 64, 0.6)',
          'rgba(255, 99, 132, 0.6)',
        ],
      },
    ],
  },
});
