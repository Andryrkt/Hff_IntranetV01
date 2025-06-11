export function changeTab(direction) {
  showTab(false); // cacher l'onglet actuel
  let currentTab = localStorage.getItem('currentTab') || 1; // Récupérer l'ID du tab actuel depuis le localStorage
  let idTabs = JSON.parse(localStorage.getItem('idTabs')) || []; // Récupérer les ID des onglets depuis le localStorage
  let currentPage = idTabs.indexOf(currentTab); // Récupérer page actuelle à partir de l'ID du tab stocké dans le localStorage
  if (direction === 'next') {
    currentPage++; // Incrémenter la page actuelle
  } else if (direction === 'prev') {
    currentPage--; // Décrémenter la page actuelle
  }
  localStorage.setItem('currentTab', idTabs[currentPage]); // Mettre à jour le localStorage avec la nouvelle page
  showTab();
}

/**
 * Fonction pour gérer l'affichage des boutons de navigation et la page actuelle.
 */
function gererAffichage() {
  let currentTab = localStorage.getItem('currentTab') || 1; // Récupérer l'ID du tab actuel depuis le localStorage
  let idTabs = JSON.parse(localStorage.getItem('idTabs')) || [];
  let currentPage = idTabs.indexOf(currentTab) + 1; // Récupérer la page actuelle à partir de l'ID du tab stocké dans le localStorage
  document.querySelectorAll('.prevBtn').forEach((btn) => {
    if (currentPage === 1) {
      btn.classList.add('disabled');
    } else {
      btn.classList.remove('disabled');
    }
  });
  document.querySelectorAll('.nextBtn').forEach((btn) => {
    if (currentPage === idTabs.length) {
      btn.classList.add('disabled');
    } else {
      btn.classList.remove('disabled');
    }
  });
  document.querySelectorAll('.currentPage').forEach((page) => {
    page.textContent = currentPage;
  });
}

/**
 * Fonction pour afficher ou masquer un onglet spécifique.
 *
 * @param {*} afficher
 */
export function showTab(afficher = true) {
  let currentTab = localStorage.getItem('currentTab') || 1;
  console.log(currentTab);

  let tab = document.getElementById(`tab_${currentTab}`);

  if (afficher) {
    gererAffichage(); // Mettre à jour l'affichage des boutons de navigation
    tab.classList.add('show', 'active');
  } else {
    tab.classList.remove('show', 'active');
  }
}

/**
 * Initialise les ID des onglets pour la navigation.
 * Cette fonction parcourt tous les éléments dont l'ID commence par "tab_",
 * extrait les numéros d'onglet et les stocke dans le localStorage.
 * @returns {void}
 */
export function initialiserIdTabs() {
  const idTabs = [];
  document.querySelectorAll('[id^="tab_"]').forEach((el) => {
    const match = el.id.match(/^tab_(\d+)$/); // Ex: tab_1, tab_2, etc.
    // Vérifie si l'ID correspond au format attendu
    if (match) {
      idTabs.push(match[1]); // Ajoute l'ID (chaine) du tab à la liste
    }
  });

  localStorage.setItem('idTabs', JSON.stringify(idTabs)); // * localStorage ne peut stocker que des chaînes de caractères, donc convertir le tableau en JSON.
}
