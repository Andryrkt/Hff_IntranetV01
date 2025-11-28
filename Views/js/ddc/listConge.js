import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();

// === FONCTIONNALITÉ DE BASCULE ENTRE LES MODES ===

// Gestion du bouton de changement de vue
document.addEventListener("DOMContentLoaded", function () {
  const viewContainer = document.getElementById("view-container");

  // Données fournies depuis Twig
  const conges = window.congesData || null;
  const employees = window.employeesData || null;
  const initialViewMode = document.body.dataset.viewMode || "list";

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
  let currentMonthCalendar = new Date();

  // Fonction pour charger dynamiquement le contenu
  async function loadViewContent(viewMode, data) {
    try {
      let content = "";

      if (viewMode === "calendar") {
        // Charger le contenu du template calendrier directement
        content = document.querySelector("#calendar-template")
          ? document.querySelector("#calendar-template").innerHTML
          : '<div class="alert alert-info">Modèle de calendrier non disponible</div>';
      } else {
        // Charger le contenu du template liste directement
        content = document.querySelector("#list-template")
          ? document.querySelector("#list-template").innerHTML
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
    if (!viewContainer) {
      console.error("Conteneur de vue introuvable");
      return;
    }

    // Afficher un indicateur de chargement
    viewContainer.innerHTML =
      '<div class="text-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Chargement...</span></div></div>';

    // Charger le nouveau contenu
    const newContent = await loadViewContent(viewMode, window.pageData || null);
    viewContainer.innerHTML = newContent;

    // Mettre à jour le titre de la page
    const titleElement = document.querySelector(".perso-titre");
    if (titleElement) {
      if (viewMode === "calendar") {
        titleElement.textContent = "Calendrier des Demandes de Congé";
      } else {
        titleElement.textContent = "Liste des Demandes de Congé";
      }
    }

    // Mettre à jour tous les éléments de la vue (boutons de changement et lien Excel)
    updateAllViewElements(viewMode);

    // Si c'est la vue calendrier, initialiser le calendrier
    if (viewMode === "calendar") {
      // Attendre un peu pour s'assurer que le DOM est mis à jour
      setTimeout(() => {
        initializeCalendar();
      }, 150);
    }

    // Mettre à jour la variable dataset du body pour refléter le mode de vue actuel
    document.body.dataset.viewMode = viewMode;
  }

  // Fonction pour mettre à jour le bouton de changement de vue
  function updateSwitchButton(currentViewMode) {
    const switchButtons = document.querySelectorAll(".switch-view");
    switchButtons.forEach((button) => {
      if (currentViewMode === "calendar") {
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

  // Fonction pour mettre à jour le lien Excel
  function updateExcelLink(viewMode) {
    const excelButton = document.querySelector(".excel-button");
    if (excelButton) {
      let baseUrl = excelButton.getAttribute("href").split("?")[0];
      const url = new URL(
        excelButton.getAttribute("href"),
        window.location.origin
      );
      const params = new URLSearchParams(url.search);

      // Ajouter le format
      params.set("format", viewMode === "calendar" ? "table" : "list");

      // Si on est en mode calendrier, ajouter les paramètres de mois et année
      if (viewMode === "calendar") {
        // Récupérer le mois et l'année courants du calendrier
        const month = currentMonthCalendar.getMonth() + 1; // getMonth() est 0-indexé
        const year = currentMonthCalendar.getFullYear();

        params.set("month", month.toString());
        params.set("year", year.toString());
      }

      // Construire l'URL finale
      const newUrl = baseUrl + "?" + params.toString();
      excelButton.setAttribute("href", newUrl);

      // Mettre à jour le texte du bouton pour indiquer le format
      const textSpan = excelButton.querySelector(".btn-text");
      if (textSpan) {
        textSpan.textContent = `Excel ${
          viewMode === "calendar" ? "calendrier" : "liste"
        }`;
      }
    }
  }

  // Exécuter la mise à jour du lien Excel après le changement de mode
  function updateAllViewElements(currentViewMode) {
    updateSwitchButton(currentViewMode);
    updateExcelLink(currentViewMode);
  }

  // Fonction pour initialiser le calendrier
  function initializeCalendar() {
    // Récupérer les données du calendrier
    congesCalendar = window.congesData || [];
    employeesCalendar = window.employeesData || {};

    // Vérifier que les données sont disponibles
    if (!congesCalendar || !employeesCalendar) {
      console.error(
        "Aucune donnée de congé ou d'employés disponible pour le calendrier"
      );
      if (viewContainer) {
        viewContainer.innerHTML =
          '<div class="alert alert-warning">Aucune donnée disponible pour le calendrier</div>';
      }
      return;
    }

    // console.log("Données congesCalendar:", congesCalendar);
    // console.log("Données employeesCalendar:", employeesCalendar);

    // Vérifier que les éléments nécessaires sont présents avant de continuer
    if (document.getElementById("calendar-header")) {
      renderCalendar();
      setupCalendarNavigation(); // Initialiser les événements de navigation
      setupCongeModal(); // Initialiser la gestion de la modal
    } else {
      // Attendre un peu plus longtemps si l'élément n'est pas encore là
      setTimeout(() => {
        if (document.getElementById("calendar-header")) {
          renderCalendar();
          setupCalendarNavigation(); // Initialiser les événements de navigation
          setupCongeModal(); // Initialiser la gestion de la modal
        } else {
          console.error(
            "Impossible de trouver les éléments du calendrier après chargement"
          );
        }
      }, 300);
    }
  }

  // Fonction pour afficher le calendrier
  function renderCalendar() {
    const year = currentMonthCalendar.getFullYear();
    const month = currentMonthCalendar.getMonth();
    const days = getDaysInMonth(currentMonthCalendar);

    // titre
    const titleEl = document.getElementById("calendar-month-year");
    if (titleEl) {
      titleEl.textContent = monthNamesCalendar[month] + " " + year;
    }

    // header
    const header = document.getElementById("calendar-header");
    if (!header) {
      console.error("Element #calendar-header introuvable");
      return;
    }
    header.innerHTML = "";

    const emptyCol = document.createElement("div");
    emptyCol.className = "calendar-cell";
    emptyCol.style.minWidth = "340px"; //240
    emptyCol.style.maxWidth = "340px"; // 240
    header.appendChild(emptyCol);

    for (let d = 1; d <= days; d++) {
      const dt = new Date(year, month, d);
      const col = document.createElement("div");
      col.className = "calendar-cell";
      // weekend
      if (dt.getDay() === 0 || dt.getDay() === 6) {
        col.classList.add("weekend");
      }
      col.textContent = d;
      header.appendChild(col);
    }

    // Récupérer les éléments de lignes côté DOM
    const rowElements = Array.from(
      document.querySelectorAll('[id^="calendar-days-"]')
    );

    // Vérification simple : même nombre
    const employeeKeys = Object.keys(employeesCalendar);
    if (rowElements.length !== employeeKeys.length) {
      console.warn(
        "Attention : le nombre de lignes DOM (" +
          rowElements.length +
          ") ne correspond pas au nombre d'employés (" +
          employeeKeys.length +
          "). On va mapper par index pour garantir la correspondance."
      );
    }

    // itérer sur les employés
    employeeKeys.forEach((employeeKey, idx) => {
      const rowId = `calendar-days-${idx}`;
      const rowEl = document.getElementById(rowId);

      if (!rowEl) {
        console.error(
          "Ligne introuvable pour employee index",
          idx,
          "attendu id:",
          rowId
        );
        return; // skip
      }

      // vider ligne
      rowEl.innerHTML = "";

      const empConges = employeesCalendar[employeeKey] || [];

      // pour accélérer, on peut pré-calculer les périodes de congé par jour ou tranches
      for (let d = 1; d <= days; d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(
          d
        ).padStart(2, "0")}`;
        const dateObj = new Date(dateStr);

        const cell = document.createElement("div");
        cell.className = "calendar-cell";

        if (dateObj.getDay() === 0 || dateObj.getDay() === 6) {
          cell.classList.add("weekend");
        }

        // Cherche un congé qui couvre ce jour
        const conge = empConges.find((c) => {
          if (!c.dateDebut || !c.dateFin) {
            console.warn("Congé sans date de début ou de fin:", c);
            return false;
          }
          // Extraire les dates si elles sont dans un objet imbriqué
          const dateDebut = c.dateDebut.date ? c.dateDebut.date : c.dateDebut;
          const dateFin = c.dateFin.date ? c.dateFin.date : c.dateFin;

          if (!dateDebut || !dateFin) {
            console.warn("Dates invalides pour le congé:", c);
            return false;
          }

          const isInRange = isDateInRange(dateStr, dateDebut, dateFin);
          return isInRange;
        });

        if (conge) {
          if (conge.statutDemande === "Validé") {
            cell.classList.add("conge-bar-valide");
          } else if (
            conge.statutDemande === "Refusé" ||
            conge.statutDemande === "Annulé"
          ) {
            cell.classList.add("conge-bar-annuler");
          } else {
            cell.classList.add("conge-bar-encours");
          }
          // Vérifier si c'est le premier jour du congé pour ajouter l'indicateur
          const isStartDate =
            new Date(
              conge.dateDebut.date ? conge.dateDebut.date : conge.dateDebut
            ).getTime() === new Date(dateStr).getTime();

          if (isStartDate) {
            // Créer un span avec l'indicateur
            const span = document.createElement("span");
            span.className = "day-indicator";
            // Utiliser la première lettre du sous-type de document comme indicateur
            span.textContent = conge.sousTypeDocument
              ? conge.sousTypeDocument.charAt(0)
              : "C";

            // Attacher les données pour la modal au span
            span.setAttribute("data-bs-toggle", "modal");
            span.setAttribute("data-bs-target", "#congeDetailsModal");
            span.setAttribute("data-conge", JSON.stringify(conge));

            cell.appendChild(span);
          }

          // Ajouter également les attributs à la cellule pour permettre le clic sur toute la cellule
          cell.setAttribute("data-conge-full", JSON.stringify(conge));

          // Ajouter un gestionnaire de clic à la cellule
          cell.addEventListener("click", function (e) {
            // Empêcher le clic sur le span d'être compté deux fois
            if (!e.target.classList.contains("day-indicator")) {
              // Ouvrir la modal avec les données du congé
              const modal =
                bootstrap.Modal.getInstance(
                  document.getElementById("congeDetailsModal")
                ) ||
                new bootstrap.Modal(
                  document.getElementById("congeDetailsModal")
                );

              // Mettre à jour le contenu de la modal avant de l'ouvrir
              const modalBody = document.getElementById("congeDetailsContent");
              const congeData = JSON.parse(
                this.getAttribute("data-conge-full")
              );
              console.log(congeData);

              // Gérer les dates potentiellement imbriquées
              const dateDebut =
                congeData.dateDebut && congeData.dateDebut.date
                  ? congeData.dateDebut.date
                  : congeData.dateDebut;
              const dateFin =
                congeData.dateFin && congeData.dateFin.date
                  ? congeData.dateFin.date
                  : congeData.dateFin;

              modalBody.innerHTML = `
                                <p><strong>Numéro :</strong> ${
                                  congeData.numeroDemande || "N/A"
                                }</p>
                                <p><strong>Employé :</strong> ${
                                  congeData.nomPrenoms || "N/A"
                                }</p>
                                <p><strong>Type :</strong> ${
                                  congeData.sousTypeDocument || "N/A"
                                }</p>
                                <p><strong>Début :</strong> ${
                                  dateDebut
                                    ? new Date(dateDebut).toLocaleDateString(
                                        "fr-FR"
                                      )
                                    : "N/A"
                                }</p>
                                <p><strong>Fin :</strong> ${
                                  dateFin
                                    ? new Date(dateFin).toLocaleDateString(
                                        "fr-FR"
                                      )
                                    : "N/A"
                                }</p>
                                <p><strong>Durée :</strong> ${
                                  congeData.dureeConge || "N/A"
                                } jours</p>
                                <p><strong>Statut :</strong> ${
                                  congeData.statutDemande || "N/A"
                                }</p>
                                `;
              let conger = congeData.dureeConge;

              // Ouvrir la modal
              modal.show();
            }
          });
        }

        rowEl.appendChild(cell);
      }
    });
  }

  function formatDuree(value) {
    return value.toLocaleString("fr-FR", {
      minimumFractionDigits: 1,
      maximumFractionDigits: 2,
    });
  }

  // Fonctions utilitaires pour le calendrier
  function getDaysInMonth(date) {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  }

  function isDateInRange(checkDate, startDate, endDate) {
    const check = new Date(checkDate);
    const start = new Date(startDate);
    const end = new Date(endDate);
    start.setHours(0, 0, 0, 0);
    end.setHours(23, 59, 59, 999);
    check.setHours(12, 0, 0, 0);
    return check >= start && check <= end;
  }

  // Gestion des événements pour la navigation dans le calendrier
  function setupCalendarNavigation() {
    document
      .getElementById("prev-month")
      ?.addEventListener("click", function () {
        currentMonthCalendar.setMonth(currentMonthCalendar.getMonth() - 1);
        renderCalendar();
        updateExcelLink("calendar"); // Mettre à jour le lien Excel après changement de mois
      });

    document
      .getElementById("next-month")
      ?.addEventListener("click", function () {
        currentMonthCalendar.setMonth(currentMonthCalendar.getMonth() + 1);
        renderCalendar();
        updateExcelLink("calendar"); // Mettre à jour le lien Excel après changement de mois
      });

    document
      .getElementById("current-month")
      ?.addEventListener("click", function () {
        currentMonthCalendar = new Date();
        renderCalendar();
        updateExcelLink("calendar"); // Mettre à jour le lien Excel après changement de mois
      });
  }

  // Gestion de la modal pour les détails des congés
  function setupCongeModal() {
    const modalEl = document.getElementById("congeDetailsModal");
    if (modalEl) {
      modalEl.addEventListener("show.bs.modal", function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        const raw = trigger.getAttribute("data-conge");
        if (!raw) return;
        let data;
        try {
          data = JSON.parse(raw);
        } catch (e) {
          console.error("Impossible de parser data-conge", e);
          return;
        }

        const body = document.getElementById("congeDetailsContent");
        if (!body) return;

        // Gérer les dates potentiellement imbriquées
        const dateDebut =
          data.dateDebut && data.dateDebut.date
            ? data.dateDebut.date
            : data.dateDebut;
        const dateFin =
          data.dateFin && data.dateFin.date ? data.dateFin.date : data.dateFin;

        body.innerHTML = `
                    <p><strong>Numéro :</strong> ${
                      data.numeroDemande || "N/A"
                    }</p>
                    <p><strong>Employé :</strong> ${
                      data.nomPrenoms || "N/A"
                    }</p>
                    <p><strong>Type :</strong> ${
                      data.sousTypeDocument || "N/A"
                    }</p>
                    <p><strong>Début :</strong> ${
                      dateDebut
                        ? new Date(dateDebut).toLocaleDateString("fr-FR")
                        : "N/A"
                    }</p>
                    <p><strong>Fin :</strong> ${
                      dateFin
                        ? new Date(dateFin).toLocaleDateString("fr-FR")
                        : "N/A"
                    }</p>
                    <p><strong>Durée :</strong> ${
                      data.dureeConge || "N/A"
                    } jours</p>
                    <p><strong>Statut :</strong> ${
                      data.statutDemande || "N/A"
                    }</p>
                `;
      });
    }
  }

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

/** ========================================================
 * Multiselect Tag Box champ Matricule, nom et prénom
 *==========================================================*/
document.addEventListener("DOMContentLoaded", function () {
  const champMatricule = document.querySelector("#demande_conge_matricule"); // Sélectionner le bon ID
  let hiddenMatriculeInput = document.querySelector("#form-matricule"); // Ancien champ caché potentiellement encore utilisé

  // Si le champ caché n'existe pas, utiliser directement le champ principal
  if (!hiddenMatriculeInput) {
    // For multiple values, we need to set the value differently
    hiddenMatriculeInput = champMatricule; // Use the main field if no hidden field exists
  }

  // Vérifier si l'élément requis existe avant d'initialiser
  if (champMatricule) {
    // Création d'une structure de base pour le multiselect
    // On enveloppe l'input dans un conteneur pour y ajouter les tags visuellement
    const container = document.createElement("div");
    container.classList.add("tag-input-container");
    container.style.position = "relative";
    container.style.display = "flex";
    container.style.flexWrap = "wrap";
    container.style.gap = "4px";
    container.style.alignItems = "center";
    container.style.border = "1px solid #ccc";
    container.style.padding = "4px";
    container.style.borderRadius = "4px";

    // Déplacer l'input dans le conteneur
    champMatricule.parentNode.insertBefore(container, champMatricule);
    container.appendChild(champMatricule);

    // Conteneur pour les tags
    const tagsContainer = document.createElement("div");
    tagsContainer.classList.add("tags-list");
    tagsContainer.style.display = "flex";
    tagsContainer.style.flexWrap = "wrap";
    tagsContainer.style.gap = "4px";
    container.insertBefore(tagsContainer, champMatricule); // Les tags apparaissent avant l'input

    // Gestion des tags sélectionnés
    const selectedTags = new Set();

    // Fonction pour ajouter un tag
    function addTag(matricule) {
      if (selectedTags.has(matricule)) {
        champMatricule.value = ""; // Réinitialiser l'input après tentative d'ajout d'un doublon
        return; // Ne pas ajouter si déjà présent
      }
      selectedTags.add(matricule);

      const tagElement = document.createElement("span");
      tagElement.classList.add("tag");
      tagElement.textContent = matricule;
      tagElement.style.backgroundColor = "#007bff";
      tagElement.style.color = "white";
      tagElement.style.padding = "2px 6px";
      tagElement.style.borderRadius = "4px";
      tagElement.style.display = "inline-flex";
      tagElement.style.alignItems = "center";
      tagElement.style.gap = "4px";

      const removeButton = document.createElement("button");
      removeButton.textContent = "×";
      removeButton.type = "button"; // Important pour ne pas soumettre le formulaire
      removeButton.style.marginLeft = "4px";
      removeButton.style.background = "none";
      removeButton.style.border = "none";
      removeButton.style.color = "inherit";
      removeButton.style.cursor = "pointer";
      removeButton.style.fontSize = "18px";
      removeButton.style.lineHeight = "1";
      removeButton.style.padding = "0";
      removeButton.onclick = function () {
        selectedTags.delete(matricule);
        tagsContainer.removeChild(tagElement);
        updateHiddenInput(); // Mettre à jour le champ caché après suppression
      };

      tagElement.appendChild(removeButton);
      tagsContainer.appendChild(tagElement);
      champMatricule.value = ""; // Réinitialiser l'input après ajout
      updateHiddenInput(); // Mettre à jour le champ caché après ajout
    }

    // Fonction pour retirer un tag (si nécessaire via API externe ou logique spécifique)
    function removeTag(matricule) {
      if (selectedTags.has(matricule)) {
        const tagElement = Array.from(tagsContainer.children).find(
          (child) =>
            child.textContent.includes(matricule) &&
            child.querySelector("button")
        );
        if (tagElement) {
          selectedTags.delete(matricule);
          tagsContainer.removeChild(tagElement);
          updateHiddenInput();
        }
      }
    }

    // Fonction pour mettre à jour le champ caché ou l'input principal avec les valeurs sélectionnées
    function updateHiddenInput() {
      const selectedValuesArray = Array.from(selectedTags);
      const selectedValuesString = selectedValuesArray.join(","); // Format CSV

      if (hiddenMatriculeInput && hiddenMatriculeInput !== champMatricule) {
        hiddenMatriculeInput.value = selectedValuesString; // Mettre à jour le champ caché
      } else if (hiddenMatriculeInput === champMatricule) {
        // If we're using the main field directly, we need to handle multiple values differently
        // Since this is likely a Symfony form field, we need to add multiple options or use a different approach

        // Clear the current value first
        champMatricule.value = "";

        // For multiple selection in form submission, we might need to handle it differently
        // depending on the form field type (input vs select multiple)
        if (champMatricule.tagName === "SELECT" && champMatricule.multiple) {
          // If it's a multi-select, select the appropriate options
          Array.from(champMatricule.options).forEach((option) => {
            option.selected = selectedTags.has(option.value);
          });
        } else {
          // For text input or if it's not a multi-select, join values with comma
          champMatricule.value = selectedValuesString;
        }
      } else {
        // Si pas de champ caché, on pourrait envisager de mettre à jour l'input principal
        // MAIS ceci pourrait interférer avec la saisie utilisateur, donc on laisse vide.
        // champMatricule.value = selectedValuesString;
        champMatricule.value = ""; // Garder l'input vide pour la saisie utilisateur
      }
    }

    // --- Intégration avec l'API (étape 4 et 5) ---
    const suggestionContainer = document.querySelector(
      "#suggestion-matricule-nom-prenom"
    ); // Utiliser le conteneur existant ou en créer un nouveau
    const loaderElement = document.querySelector(
      "#loader-matricule-nom-prenom"
    ); // Utiliser le loader existant

    if (!suggestionContainer) {
      // Si le conteneur n'existe pas, on le crée
      const newSuggestionContainer = document.createElement("div");
      newSuggestionContainer.id = "suggestion-matricule-nom-prenom";
      newSuggestionContainer.classList.add("suggestions-container");
      newSuggestionContainer.style.position = "absolute";
      newSuggestionContainer.style.top = "100%";
      newSuggestionContainer.style.left = "0";
      newSuggestionContainer.style.width = "100%";
      newSuggestionContainer.style.backgroundColor = "white";
      newSuggestionContainer.style.border = "1px solid #ccc";
      newSuggestionContainer.style.zIndex = "1000";
      newSuggestionContainer.style.display = "none"; // Caché par défaut
      container.appendChild(newSuggestionContainer);
    }

    if (!loaderElement) {
      // Si le loader n'existe pas, on le crée (optionnel, peut être stylé via CSS)
      const newLoaderElement = document.createElement("div");
      newLoaderElement.id = "loader-matricule-nom-prenom";
      newLoaderElement.textContent = "Chargement...";
      newLoaderElement.style.display = "none"; // Caché par défaut
      newLoaderElement.classList.add("spinner");
      container.appendChild(newLoaderElement);
    }

    async function fetchSuggestions(query) {
      try {
        if (loaderElement) loaderElement.style.display = "block";
        // Appeler l'API avec un filtre potentiel sur la requête utilisateur
        // Pour l'instant, on récupère toutes les suggestions
        const data = await fetchManager.get(
          "rh/demande-de-conge/api/matricule-nom-prenom"
        );
        if (loaderElement) loaderElement.style.display = "none";

        // Filtrer côté client en fonction de la saisie (optionnel, l'API pourrait le faire)
        if (query) {
          return data.filter(
            (item) =>
              item.matricule.toLowerCase().includes(query.toLowerCase()) ||
              item.nomPrenoms.toLowerCase().includes(query.toLowerCase())
          );
        }
        return data;
      } catch (error) {
        console.error("Erreur lors de la récupération des suggestions:", error);
        if (loaderElement) loaderElement.style.display = "none";
        return [];
      }
    }

    function showSuggestions(suggestions) {
      const suggestionContainer = document.querySelector(
        "#suggestion-matricule-nom-prenom"
      );
      if (!suggestionContainer) return;

      suggestionContainer.innerHTML = ""; // Vider les suggestions précédentes
      if (suggestions.length === 0) {
        suggestionContainer.style.display = "none";
        return;
      }

      suggestions.forEach((item) => {
        const suggestionElement = document.createElement("div");
        suggestionElement.classList.add("suggestion-item");
        suggestionElement.textContent = `${item.matricule} - ${item.nomPrenoms}`;
        suggestionElement.style.padding = "8px";
        suggestionElement.style.cursor = "pointer";
        suggestionElement.style.borderBottom = "1px solid #eee";

        suggestionElement.addEventListener("click", () => {
          addTag(item.matricule);
          suggestionContainer.style.display = "none"; // Cacher après sélection
        });

        suggestionContainer.appendChild(suggestionElement);
      });

      suggestionContainer.style.display = "block"; // Afficher le conteneur
    }

    // Gestion de l'input pour afficher les suggestions
    champMatricule.addEventListener("input", async (e) => {
      const query = e.target.value.trim();
      if (query.length > 0) {
        // Afficher suggestions seulement si qqch est saisi
        const suggestions = await fetchSuggestions(query);
        showSuggestions(suggestions);
      } else {
        const suggestionContainer = document.querySelector(
          "#suggestion-matricule-nom-prenom"
        );
        if (suggestionContainer) suggestionContainer.style.display = "none";
      }
    });

    // Gestion de la soumission via touche Entrée (facultatif, dépend du comportement souhaité)
    champMatricule.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && e.target.value.trim() !== "") {
        e.preventDefault(); // Empêcher la soumission de formulaire
        // Essayer d'ajouter la valeur saisie si elle correspond à un élément connu ou si on autorise la saisie libre
        // Pour simplifier, on va juste effacer l'input si ce n'est pas une sélection directe
        // Une implémentation plus poussée vérifierait la correspondance avec les suggestions
        const inputValue = e.target.value.trim();
        // Pour l'instant, on suppose qu'on ne peut ajouter que via la sélection dans la liste
        e.target.value = ""; // Effacer si "Entrée" est pressé sans sélection claire
      }
    });

    // Cacher les suggestions quand l'input perd le focus (facultatif)
    champMatricule.addEventListener("blur", () => {
      setTimeout(() => {
        const suggestionContainer = document.querySelector(
          "#suggestion-matricule-nom-prenom"
        );
        if (suggestionContainer) suggestionContainer.style.display = "none";
      }, 200); // Petit délai pour permettre le clic sur une suggestion
    });

    champMatricule.addEventListener("focus", () => {
      if (champMatricule.value.trim() !== "") {
        // Si l'input a du texte au focus, réafficher les suggestions
        fetchSuggestions(champMatricule.value.trim()).then(showSuggestions);
      }
    });

    // Intégration potentielle avec la fonction addTag existante dans le template (si elle existe toujours)
    // Cela permettrait de synchroniser les ajouts faits ailleurs
    if (typeof window.addTag === "function") {
      console.warn(
        "Fonction addTag globale détectée dans le template. Cela peut causer des conflits."
      );
      // Option 1: Remplacer la fonction globale par la nôtre
      // window.addTag = addTag;
      // Option 2: Conserver la fonction du template mais l'adapter pour interagir avec notre logique
      // Cela dépend de la logique exacte de la fonction existante dans le template
      // Pour l'instant, on ne fait rien et on suppose que notre logique est autonome
    }

    // Trouver le formulaire parent et ajouter un écouteur pour la mise à jour
    const form = champMatricule.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        // Mettre à jour le champ une dernière fois avant la soumission
        updateHiddenInput();
      });
    }

    console.log(
      "Multiselect initialisé pour le champ matricule:",
      champMatricule.id
    );
  } else {
    console.warn(
      "Élément requis pour le champ matricule non trouvé - le script ne s'exécutera pas"
    );
  }
});
