export class OptimisedAutoComplete {
  constructor({
    inputElement,
    suggestionContainer,
    fetchDataCallback,
    displayItemCallback,
    onSelectCallback,
    loaderElement = null,
    itemToStringCallback = null,
    itemToStringForBlur = null,
    onBlurCallback = null,
    debounceDelay = 300,
  }) {
    this.inputElement = inputElement;
    this.suggestionContainer = suggestionContainer;
    this.fetchDataCallback = fetchDataCallback;
    this.displayItemCallback = displayItemCallback;
    this.onSelectCallback = onSelectCallback;
    this.loaderElement = loaderElement;
    this.itemToStringCallback = itemToStringCallback;
    this.itemToStringForBlur = itemToStringForBlur;
    this.onBlurCallback = onBlurCallback;
    this.debounceDelay = debounceDelay;

    this.filteredData = [];
    this.activeIndex = -1;
    this.typingTimeout = null;

    this.init();
  }

  init() {
    this.inputElement.addEventListener("input", () => this.onInput());
    this.inputElement.addEventListener("keydown", (e) => this.onKeyDown(e));

    if (this.onBlurCallback) {
      this.inputElement.addEventListener("blur", () =>
        this.onBlur(this.onBlurCallback)
      );
    }

    document.addEventListener("click", (e) => {
      if (
        !this.suggestionContainer?.contains(e.target) &&
        e.target !== this.inputElement
      ) {
        this.clearSuggestions();
      }
    });
  }

  onInput() {
    clearTimeout(this.typingTimeout);
    const query = this.inputElement.value.trim();

    this.typingTimeout = setTimeout(async () => {
      if (query === "") {
        this.clearSuggestions();
        return;
      }

      this.toggleLoader(true);
      try {
        this.filteredData = await this.fetchDataCallback(query);
        this.showSuggestions(this.filteredData);
      } catch (error) {
        console.error("Erreur de récupération des suggestions :", error);
      }
      this.toggleLoader(false);
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
      case "Tab":
        if (suggestions.length > 0) {
          if (suggestions[0].classList.contains("no_results")) {
            this.clearSuggestions();
          } else {
            event.preventDefault();
            const indexToSelect = this.activeIndex >= 0 ? this.activeIndex : 0;
            suggestions[indexToSelect].click();
          }
        }
        break;
      case "Escape":
        this.clearSuggestions();
        break;
    }
  }

  onBlur(onBlurCallback) {
    const inputValue = this.inputElement.value.trim().toLowerCase();
    const found = this.filteredData.some(
      (item) => this.itemToStringForBlur(item).toLowerCase() === inputValue
    );
    onBlurCallback(found);
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

  showSuggestions(suggestions) {
    this.clearSuggestions();

    if (suggestions.length === 0) {
      const noResultDiv = document.createElement("div");
      noResultDiv.classList.add("no_results");
      noResultDiv.textContent = "Aucune donnée trouvée";
      this.suggestionContainer.appendChild(noResultDiv);
      return;
    }

    suggestions.slice(0, 15).forEach((item, index) => {
      const suggestionElement = document.createElement("div");
      suggestionElement.classList.add("suggestion-item");
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener("click", () => {
        if (this.onBlurCallback) this.onBlurCallback(true);
        this.onSelectCallback(item);
        this.clearSuggestions();
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });

    this.activeIndex = -1;
  }

  clearSuggestions() {
    if (this.suggestionContainer) {
      this.suggestionContainer.innerHTML = "";
    }
    this.activeIndex = -1;
  }

  toggleLoader(show) {
    if (this.loaderElement) {
      this.loaderElement.style.display = show ? "block" : "none";
      this.inputElement.style.pointerEvents = show ? "none" : "auto";
    }
  }

  itemToString(item) {
    return this.itemToStringCallback
      ? this.itemToStringCallback(item)
      : JSON.stringify(item);
  }
}
