export class AutoComplete {
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
    this.typingTimeout = setTimeout(() => {
      this.filterData(this.inputElement.value.trim());
    }, this.debounceDelay);
  }

  onKeyDown(event) {
    console.log("Keydown: ", event.key);
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
        event.preventDefault();
        if (suggestions.length > 0) {
          const indexToSelect = this.activeIndex >= 0 ? this.activeIndex : 0;
          suggestions[indexToSelect].click();
        }
        break;
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

    // Vérifie si la valeur saisie est dans les suggestions filtrées
    const found = this.data.some(
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
    if (this.itemToStringCallback) {
      return this.itemToStringCallback(item);
    }
    return JSON.stringify(item);
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

    suggestions.forEach((item, index) => {
      const suggestionElement = document.createElement("div");
      suggestionElement.classList.add("suggestion-item");
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener("click", () => {
        if (this.onBlurCallback) {
          this.onBlurCallback(true);
        }
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
}

/** =============================
 * Selection multiple
 *===============================*/
export class MultiSelectAutoComplete extends AutoComplete {
  constructor(options) {
    // Extraire les options spécifiques à la version "tags"
    const { tagsContainer, hiddenInputElement, ...parentOptions } = options;
    super(parentOptions);

    this.tagsContainer = tagsContainer;
    this.hiddenInputElement = hiddenInputElement;
    this.selectedItems = [];

    // Focus l'input quand on clique sur le conteneur
    if (this.tagsContainer) {
      this.tagsContainer.addEventListener('click', (e) => {
        if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'SPAN') {
          this.inputElement.focus();
        }
      });
      // Gérer la suppression avec backspace sur l'input vide
      this.inputElement.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace' && this.inputElement.value === '' && this.selectedItems.length > 0) {
              const lastItem = this.selectedItems[this.selectedItems.length - 1];
              this.removeSelectedItem(lastItem);
          }
      });
    }
  }

  async init() {
    await super.init();

    // La valeur initiale vient du champ caché
    const initialValue = this.hiddenInputElement.value.trim();
    if (initialValue) {
      const initialStrings = initialValue.split(",").map((v) => v.trim()).filter(v => v);
      
      const initialItems = this.data.filter((item) =>
        initialStrings.includes(this.itemToString(item))
      );
      
      // Utiliser un Set pour éviter les doublons à l'initialisation
      const uniqueItems = [...new Map(initialItems.map(item => [this.itemToString(item), item])).values()];
      
      uniqueItems.forEach(item => this.addSelectedItem(item, false)); // 'false' pour ne pas vider l'input de recherche (qui est déjà vide)
    }
  }

  addSelectedItem(item, clearInput = true) {
    const isAlreadySelected = this.selectedItems.some(selectedItem => this.itemToString(selectedItem) === this.itemToString(item));
    if (isAlreadySelected) {
        if(clearInput) this.inputElement.value = "";
        this.clearSuggestions();
        return;
    }

    this.selectedItems.push(item);
    this.createTag(item);
    this.updateHiddenInputValue();
    if (clearInput) this.inputElement.value = "";
    this.clearSuggestions();
  }

  removeSelectedItem(itemToRemove) {
    this.selectedItems = this.selectedItems.filter(
      (item) => this.itemToString(item) !== this.itemToString(itemToRemove)
    );
    
    const tagElement = this.tagsContainer.querySelector(`[data-value="${this.itemToString(itemToRemove)}"]`);
    if (tagElement) {
        this.tagsContainer.removeChild(tagElement);
    }
    
    this.updateHiddenInputValue();
    this.inputElement.focus();
  }
  
  createTag(item) {
    const tag = document.createElement('span');
    tag.classList.add('badge', 'bg-primary', 'd-flex', 'align-items-center', 'me-1', 'mb-1');
    tag.style.padding = '0.4em 0.6em';
    tag.dataset.value = this.itemToString(item);

    const tagText = document.createElement('span');
    tagText.textContent = this.itemToString(item); // On utilise le matricule pour le tag, c'est plus court

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.classList.add('btn-close', 'ms-2');
    removeBtn.style.cssText = 'font-size: 0.75em; filter: invert(1) grayscale(100%) brightness(200%);';
    removeBtn.setAttribute('aria-label', 'Remove');
    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.removeSelectedItem(item);
    });

    tag.appendChild(tagText);
    tag.appendChild(removeBtn);
    
    this.tagsContainer.insertBefore(tag, this.inputElement);
  }

  updateHiddenInputValue() {
    const selectedValues = this.selectedItems.map((item) =>
      this.itemToString(item)
    );
    this.hiddenInputElement.value = selectedValues.join(", ");
  }
  
  filterData(searchValue) {
    if (searchValue.trim() === "") {
      this.clearSuggestions();
      return;
    }
    // Surcharge pour exclure les éléments déjà sélectionnés
    this.filteredData = this.data.filter((item) => {
      const itemStr = this.itemToString(item).toLowerCase();
      const isSelected = this.selectedItems.some(selected => this.itemToString(selected) === this.itemToString(item));
      return (
        itemStr.includes(searchValue.toLowerCase()) && !isSelected
      );
    });
    this.showSuggestions(this.filteredData);
  }

  showSuggestions(suggestions) {
    this.clearSuggestions();

    if (suggestions.length === 0 && this.inputElement.value) {
        const noResultDiv = document.createElement("div");
        noResultDiv.classList.add("no_results");
        noResultDiv.textContent = "Aucune donnée trouvée";
        this.suggestionContainer.appendChild(noResultDiv);
        return;
    }
    
    suggestions.forEach((item) => {
      const suggestionElement = document.createElement("div");
      suggestionElement.classList.add("suggestion-item");
      suggestionElement.innerHTML = this.displayItemCallback(item);

      suggestionElement.addEventListener("click", () => {
        this.addSelectedItem(item);
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });
  }
}
