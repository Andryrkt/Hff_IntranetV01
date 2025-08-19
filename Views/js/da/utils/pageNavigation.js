/**
 * Fonction pour gérer le changement de page par les boutons précédent ou suivant
 * @param {string} direction
 */
export function changeTab(direction) {
  showTab(false); // cacher l'onglet actuel
  let numeroDa = document.querySelector('[id^="tab_"]')?.id.split("_")[2]; // numéro du document
  let currentTab = localStorage.getItem(`currentTab_${numeroDa}`) || 1; // Récupérer l'ID du tab actuel depuis le localStorage
  let idTabs = JSON.parse(localStorage.getItem(`idTabs_${numeroDa}`)) || []; // Récupérer les ID des onglets depuis le localStorage
  let currentPage = idTabs.indexOf(currentTab); // Récupérer page actuelle à partir de l'ID du tab stocké dans le localStorage
  if (direction === "next") {
    currentPage++; // Incrémenter la page actuelle
  } else if (direction === "prev") {
    currentPage--; // Décrémenter la page actuelle
  }
  localStorage.setItem(`currentTab_${numeroDa}`, idTabs[currentPage]); // Mettre à jour le localStorage avec la nouvelle page
  showTab();
}

/**
 * Fonction pour gérer l'affichage des boutons de navigation et la page actuelle.
 */
function gererAffichage(numeroDa) {
  let idTabs = JSON.parse(localStorage.getItem(`idTabs_${numeroDa}`)) || [];
  let currentTab = localStorage.getItem(`currentTab_${numeroDa}`) || idTabs[0]; // Récupérer l'ID du tab actuel depuis le localStorage
  let currentPage = idTabs.indexOf(currentTab) + 1; // Récupérer la page actuelle à partir de l'ID du tab stocké dans le localStorage
  document.querySelectorAll(".prevBtn").forEach((btn) => {
    if (currentPage === 1) {
      btn.classList.add("disabled");
    } else {
      btn.classList.remove("disabled");
    }
  });
  document.querySelectorAll(".nextBtn").forEach((btn) => {
    if (currentPage === idTabs.length) {
      btn.classList.add("disabled");
    } else {
      btn.classList.remove("disabled");
    }
  });
  document.querySelectorAll(".currentPage").forEach((page) => {
    page.textContent = currentPage;
  });
}

/**
 * Fonction pour afficher ou masquer un onglet spécifique.
 *
 * @param {*} afficher
 */
export function showTab(afficher = true) {
  let numeroDa = document.querySelector('[id^="tab_"]')?.id.split("_")[2]; // numéro du document
  let idTabs = JSON.parse(localStorage.getItem(`idTabs_${numeroDa}`));
  let currentTab = localStorage.getItem(`currentTab_${numeroDa}`) || idTabs[0];
  console.log(`currentTab_${numeroDa} = ` + currentTab);

  let tab = document.getElementById(`tab_${currentTab}_${numeroDa}`);

  if (afficher) {
    gererAffichage(numeroDa); // Mettre à jour l'affichage des boutons de navigation
    tab.classList.add("show", "active");
  } else {
    tab.classList.remove("show", "active");
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
  let numeroDa = null;
  document.querySelectorAll('[id^="tab_"]').forEach((el) => {
    const parts = el.id.split("_"); // ["tab", "1", "DAPXXXXXXXX"]
    idTabs.push(parts[1]); // Ajoute le numéro de ligne de la DA à la liste
    if (!numeroDa) numeroDa = parts[2]; // "DAPXXXXXXXX", on le récupère en une fois
  });

  localStorage.setItem(`idTabs_${numeroDa}`, JSON.stringify(idTabs)); // * localStorage ne peut stocker que des chaînes de caractères, donc convertir le tableau en JSON.
}
