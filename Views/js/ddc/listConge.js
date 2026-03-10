import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();

// === FONCTIONNALITÉ DE BASCULE ENTRE LES MODES ===

// Gestion du bouton de changement de vue
document.addEventListener("DOMContentLoaded", function () {
  const viewContainer = document.getElementById("view-container");

  // Fonction pour obtenir le mode actuel de manière sécurisée
  function getCurrentMode() {
    // Si on a explicitement changé de vue via switchView, c'est cette valeur qui prime
    if (window.currentActiveView) return window.currentActiveView;

    return (
      window.forcedViewMode ||
      document.body.getAttribute("data-type-view") ||
      document.body.getAttribute("data-view-mode") ||
      document.body.dataset.typeView ||
      document.body.dataset.viewMode ||
      "list"
    );
  }

  // Fonction d'initialisation sécurisée
  function startCalendar() {
    const mode = getCurrentMode();
    console.log("DDC: Tentative d'initialisation. Mode détecté:", mode);
    console.log("DDC: window.employeesData:", window.employeesData);

    if (mode === "calendar") {
      if (
        window.employeesData &&
        typeof window.employeesData === "object" &&
        Object.keys(window.employeesData).length > 0
      ) {
        console.log(
          "DDC: Données prêtes (" +
            Object.keys(window.employeesData).length +
            " employés), lancement de initializeCalendar",
        );
        initializeCalendar();
      } else {
        console.warn(
          "DDC: Mode calendrier actif mais window.employeesData est vide, invalide ou non défini.",
        );
        // Tentative de secours : si le DOM contient des lignes, on essaie quand même
        if (document.querySelectorAll("[data-employee-name]").length > 0) {
          console.log(
            "DDC: Lignes trouvées dans le DOM, tentative d'initialisation forcée",
          );
          initializeCalendar();
        }
      }
    }
  }

  // Initialiser au chargement
  setTimeout(startCalendar, 300);

  // Utilisation de l'événement delegation pour gérer les clics sur les boutons switch-view
  document.addEventListener("click", function (e) {
    if (
      e.target.classList.contains("switch-view") ||
      e.target.closest(".switch-view")
    ) {
      const button = e.target.classList.contains("switch-view")
        ? e.target
        : e.target.closest(".switch-view");
      e.preventDefault();
      const targetViewMode = button.getAttribute("data-mode");
      switchView(targetViewMode);
    }
  });
});

// Variables globales pour le calendrier
let congesCalendar = null;
let employeesCalendar = null;
let monthNamesCalendar = [
  "Janvier",
  "Février",
  "Mars",
  "Avril",
  "Mai",
  "Juin",
  "Juillet",
  "Août",
  "Septembre",
  "Octobre",
  "Novembre",
  "Décembre",
];
let currentMonthCalendar = window.selectedMonth
  ? new Date(window.selectedMonth)
  : new Date();

// Fonction pour charger dynamiquement le contenu
async function loadViewContent(viewMode, data) {
  try {
    let content = "";
    if (viewMode === "calendar") {
      const template = document.querySelector("#calendar-template");
      content = template
        ? template.innerHTML
        : '<div class="alert alert-info">Modèle de calendrier non disponible</div>';
    } else {
      const template = document.querySelector("#list-template");
      content = template
        ? template.innerHTML
        : '<div class="alert alert-info">Modèle de liste non disponible</div>';
    }
    return content;
  } catch (error) {
    console.error("Erreur lors du chargement du contenu:", error);
    return '<div class="alert alert-danger">Erreur de chargement de la vue</div>';
  }
}

// Fonction pour basculer entre les modes
async function switchView(viewMode) {
  window.currentActiveView = viewMode;
  const viewContainer = document.getElementById("view-container");
  if (!viewContainer) return;

  viewContainer.innerHTML =
    '<div class="text-center my-4"><div class="spinner-border" role="status"></div></div>';
  const newContent = await loadViewContent(viewMode, window.pageData || null);
  viewContainer.innerHTML = newContent;

  const titleElement = document.querySelector(".perso-titre");
  if (titleElement) {
    titleElement.textContent =
      viewMode === "calendar"
        ? "Calendrier des Demandes de Congé"
        : "Liste des Demandes de Congé";
  }

  updateAllViewElements(viewMode);
  if (viewMode === "calendar") {
    setTimeout(() => {
      initializeCalendar();
    }, 50);
  }
  document.body.dataset.viewMode = viewMode;
  document.body.dataset.typeView = viewMode;
}

function updateAllViewElements(viewMode) {
  const switchButtons = document.querySelectorAll(".switch-view");
  switchButtons.forEach((button) => {
    if (viewMode === "calendar") {
      button.innerHTML = '<i class="fas fa-list"></i> Liste';
      button.classList.remove("btn-info", "text-white");
      button.classList.add("btn-warning");
      button.setAttribute("data-mode", "list");
    } else {
      button.innerHTML = '<i class="fas fa-calendar"></i> Calendrier';
      button.classList.remove("btn-warning");
      button.classList.add("btn-info", "text-white");
      button.setAttribute("data-mode", "calendar");
    }
  });
}

function initializeCalendar() {
  congesCalendar = window.congesData || [];
  employeesCalendar = window.employeesData || {};

  if (!employeesCalendar || Object.keys(employeesCalendar).length === 0) {
    console.error("Aucune donnée d'employés disponible pour le calendrier");
    return;
  }

  if (document.getElementById("calendar-header")) {
    renderCalendar();
    setupCalendarNavigation();
    setupCongeModal();
  } else {
    setTimeout(() => {
      if (document.getElementById("calendar-header")) renderCalendar();
    }, 300);
  }
}

function renderCalendar() {
  const year = currentMonthCalendar.getFullYear();
  const month = currentMonthCalendar.getMonth();
  const days = new Date(year, month + 1, 0).getDate();

  const titleEl = document.getElementById("calendar-month-year");
  if (titleEl) titleEl.textContent = monthNamesCalendar[month] + " " + year;

  const header = document.getElementById("calendar-header");
  if (!header) return;
  header.innerHTML =
    '<div class="calendar-cell" style="min-width:340px;max-width:340px;"></div>';

  for (let d = 1; d <= days; d++) {
    const dt = new Date(year, month, d);
    const col = document.createElement("div");
    col.className =
      "calendar-cell" +
      (dt.getDay() === 0 || dt.getDay() === 6 ? " weekend" : "");
    col.textContent = d;
    header.appendChild(col);
  }

  const employeeKeys = Object.keys(employeesCalendar);
  employeeKeys.forEach((employeeKey, idx) => {
    // Utiliser getElementById en priorité car les IDs sont générés par Twig et sont simples (index)
    let rowId = `calendar-days-${idx}`;
    let rowEl = document.getElementById(rowId);

    // Fallback sur data-employee-name si l'ID ne correspond pas
    if (!rowEl) {
      rowEl = document.querySelector(
        `[data-employee-name="${CSS.escape(employeeKey)}"]`,
      );
    }

    if (!rowEl) {
      console.warn("Ligne non trouvée pour :", employeeKey, "index:", idx);
      return;
    }

    rowEl.innerHTML = "";
    rowEl.style.display = "flex"; // S'assurer que la ligne est visible
    const empConges = employeesCalendar[employeeKey] || [];

    for (let d = 1; d <= days; d++) {
      const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
      const cell = document.createElement("div");
      cell.className =
        "calendar-cell" +
        (new Date(dateStr).getDay() === 0 || new Date(dateStr).getDay() === 6
          ? " weekend"
          : "");

      const conge = empConges.find((c) => {
        const start = c.dateDebut;
        const end = c.dateFin;
        const check = new Date(dateStr);
        check.setHours(12, 0, 0, 0);
        return check >= new Date(start) && check <= new Date(end);
      });

      if (conge) {
        const statut = (conge.statutDemande || "").trim();
        if (statut.startsWith("Validé")) cell.classList.add("conge-bar-valide");
        else if (statut.startsWith("Refusé") || statut.startsWith("Annulé"))
          cell.classList.add("conge-bar-annuler");
        else cell.classList.add("conge-bar-encours");

        if (
          new Date(conge.dateDebut).toDateString() ===
          new Date(dateStr).toDateString()
        ) {
          const span = document.createElement("span");
          span.className = "day-indicator";
          span.textContent = "";
          span.setAttribute("data-bs-toggle", "modal");
          span.setAttribute("data-bs-target", "#congeDetailsModal");
          span.setAttribute("data-conge", JSON.stringify(conge));
          cell.appendChild(span);
        }
        cell.setAttribute("data-conge-full", JSON.stringify(conge));
        cell.style.cursor = "pointer";
        cell.onclick = function () {
          showModal(JSON.parse(this.getAttribute("data-conge-full")));
        };
      }
      rowEl.appendChild(cell);
    }
  });
}

function showModal(data) {
  const modalBody = document.getElementById("congeDetailsContent");
  if (!modalBody) return;
  modalBody.innerHTML = `
    <p><strong>Numéro :</strong> ${data.numeroDemande || "N/A"}</p>
    <p><strong>Employé :</strong> ${data.nomPrenoms || "N/A"}</p>
    <p><strong>Type :</strong> ${data.sousTypeDocument || "N/A"}</p>
    <p><strong>Début :</strong> ${data.dateDebut}</p>
    <p><strong>Fin :</strong> ${data.dateFin}</p>
    <p><strong>Durée :</strong> ${data.dureeConge} jours</p>
    <p><strong>Statut :</strong> ${data.statutDemande}</p>
  `;
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("congeDetailsModal"),
  );
  modal.show();
}

function setupCalendarNavigation() {
  const prevBtn = document.getElementById("prev-month");
  if (prevBtn)
    prevBtn.onclick = () => {
      currentMonthCalendar.setMonth(currentMonthCalendar.getMonth() - 1);
      renderCalendar();
    };

  const nextBtn = document.getElementById("next-month");
  if (nextBtn)
    nextBtn.onclick = () => {
      currentMonthCalendar.setMonth(currentMonthCalendar.getMonth() + 1);
      renderCalendar();
    };

  const currBtn = document.getElementById("current-month");
  if (currBtn)
    currBtn.onclick = () => {
      currentMonthCalendar = new Date();
      renderCalendar();
    };
}

function setupCongeModal() {}
