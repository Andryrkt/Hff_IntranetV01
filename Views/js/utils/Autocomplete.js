export class AutoComplete {
  constructor({
    inputElement,
    suggestionContainer,
    fetchDataCallback,
    displayItemCallback,
    onSelectCallback,
    loaderElement = null,
    debounceDelay = 300,
  }) {
    this.inputElement = inputElement;
    this.suggestionContainer = suggestionContainer;
    this.fetchDataCallback = fetchDataCallback;
    this.displayItemCallback = displayItemCallback;
    this.onSelectCallback = onSelectCallback;
    this.loaderElement = loaderElement;
    this.debounceDelay = debounceDelay;

    this.data = [];
    this.filteredData = [];
    this.activeIndex = -1;
    this.typingTimeout = null;

    this.init();
  }

  async init() {
    this.toggleLoader(true);
    try {
      this.data = await this.fetchDataCallback();
    } catch (error) {
      console.error("Erreur lors du chargement des données :", error);
    }
    this.toggleLoader(false);

    this.inputElement.addEventListener("input", () => this.onInput());
    this.inputElement.addEventListener("keydown", (e) => this.onKeyDown(e));

    document.addEventListener("click", (e) => {
      if (
        !this.suggestionContainer.contains(e.target) &&
        e.target !== this.inputElement
      ) {
        this.clearSuggestions();
      }
    });
  }

  onInput() {
    clearTimeout(this.typingTimeout);
    this.typingTimeout = setTimeout(() => {
      this.filterData(this.inputElement.value.trim());
    }, this.debounceDelay);
  }

  onKeyDown(event) {
    const suggestions = this.suggestionContainer.querySelectorAll("div");

    switch (event.key) {
      case "ArrowDown":
        this.activeIndex = (this.activeIndex + 1) % suggestions.length;
        this.updateActiveSuggestion(suggestions);
        break;
      case "ArrowUp":
        this.activeIndex =
          (this.activeIndex - 1 + suggestions.length) % suggestions.length;
        this.updateActiveSuggestion(suggestions);
        break;
      case "Enter":
        event.preventDefault(); // 🚫 Bloquer la soumission même si aucune suggestion sélectionnée
        if (this.activeIndex >= 0 && suggestions[this.activeIndex]) {
          suggestions[this.activeIndex].click();
        }
        break;
      case "Escape":
        this.clearSuggestions();
        break;
    }
  }

  updateActiveSuggestion(suggestions) {
    suggestions.forEach((s, index) => {
      if (index === this.activeIndex) {
        s.classList.add("active-suggestion");
        s.scrollIntoView({ block: "nearest" });
      } else {
        s.classList.remove("active-suggestion");
      }
    });
  }

  filterData(searchValue) {
    if (searchValue === "") {
      this.clearSuggestions();
      return;
    }

    this.filteredData = this.data.filter((item) =>
      this.itemToString(item).toLowerCase().includes(searchValue.toLowerCase())
    );

    this.showSuggestions(this.filteredData);
  }

  itemToString(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  showSuggestions(suggestions) {
    this.clearSuggestions();

    if (suggestions.length === 0) {
      return;
    }

    suggestions.forEach((item, index) => {
      const suggestionElement = document.createElement("div");
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener("click", () => {
        this.onSelectCallback(item);
        this.clearSuggestions();
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });

    this.activeIndex = -1; // Réinitialise l'index actif
  }

  clearSuggestions() {
    this.suggestionContainer.innerHTML = "";
    this.activeIndex = -1;
  }

  toggleLoader(show) {
    if (this.loaderElement) {
      this.loaderElement.style.display = show ? "block" : "none";
    }
  }
}
