export class AutoComplete {
  constructor({
    inputElement,
    suggestionContainer,
    fetchDataCallback,
    displayItemCallback,
    onSelectCallback,
    loaderElement = null,
    itemToStringCallback = null,
    debounceDelay = 300,
  }) {
    this.inputElement = inputElement;
    this.suggestionContainer = suggestionContainer;
    this.fetchDataCallback = fetchDataCallback;
    this.displayItemCallback = displayItemCallback;
    this.onSelectCallback = onSelectCallback;
    this.loaderElement = loaderElement;
    this.itemToStringCallback = itemToStringCallback;
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
      console.error('Erreur lors du chargement des données :', error);
    }
    this.toggleLoader(false);

    this.inputElement.addEventListener('input', () => this.onInput());
    this.inputElement.addEventListener('keydown', (e) => this.onKeyDown(e));

    document.addEventListener('click', (e) => {
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
    const suggestions = this.suggestionContainer.querySelectorAll('div');

    switch (event.key) {
      case 'ArrowDown':
        this.activeIndex = (this.activeIndex + 1) % suggestions.length;
        this.updateActiveSuggestion(suggestions);
        break;
      case 'ArrowUp':
        this.activeIndex =
          (this.activeIndex - 1 + suggestions.length) % suggestions.length;
        this.updateActiveSuggestion(suggestions);
        break;
      case 'Enter':
        event.preventDefault();
        if (suggestions.length > 0) {
          const indexToSelect = this.activeIndex >= 0 ? this.activeIndex : 0;
          suggestions[indexToSelect].click();
        }
        break;
      case 'Tab':
        if (suggestions.length > 0) {
          event.preventDefault();
          const indexToSelect = this.activeIndex >= 0 ? this.activeIndex : 0;
          suggestions[indexToSelect].click();
        }
        break;
      case 'Escape':
        this.clearSuggestions();
        break;
    }
  }

  onBlur() {
    const inputValue = this.inputElement.value.trim().toLowerCase();

    // Vérifie si la valeur saisie est dans les suggestions filtrées
    const found = this.filteredData.some(
      (item) => this.itemToString(item).toLowerCase() === inputValue
    );

    return found;
  }

  updateActiveSuggestion(suggestions) {
    suggestions.forEach((s, index) => {
      if (index === this.activeIndex) {
        s.classList.add('active-suggestion');
        s.scrollIntoView({ block: 'nearest' });
      } else {
        s.classList.remove('active-suggestion');
      }
    });
  }

  filterData(searchValue) {
    if (searchValue === '') {
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
      return;
    }

    suggestions.forEach((item, index) => {
      const suggestionElement = document.createElement('div');
      suggestionElement.classList.add('suggestion-item');
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener('click', () => {
        this.onSelectCallback(item);
        this.clearSuggestions();
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });

    this.activeIndex = -1;
  }

  clearSuggestions() {
    if (this.suggestionContainer) {
      this.suggestionContainer.innerHTML = '';
    }

    this.activeIndex = -1;
  }

  toggleLoader(show) {
    if (this.loaderElement) {
      this.loaderElement.style.display = show ? 'block' : 'none';
    }
  }
}

/** =============================
 * Selection multiple
 *===============================*/
export class MultiSelectAutoComplete extends AutoComplete {
  constructor(options) {
    // Passez les options à la classe parente
    super(options);
    // Initialisation du tableau des éléments sélectionnés
    this.selectedItems = [];
  }

  // Ajoute un élément sélectionné et met à jour l'input
  addSelectedItem(item) {
    if (!this.selectedItems.includes(item)) {
      this.selectedItems.push(item);
      // On peut déclencher un callback ici si besoin
      this.onSelectCallback(item);
      this.updateInputValue();
    }
    this.clearSuggestions();
  }

  // Met à jour la valeur de l'input en concaténant tous les éléments sélectionnés
  updateInputValue() {
    const selectedValues = this.selectedItems.map((item) =>
      this.itemToString(item)
    );
    // Par exemple, séparer les valeurs par une virgule et un espace
    this.inputElement.value = selectedValues.join(', ');
  }

  // Permet de supprimer un élément de la sélection et met à jour l'input
  removeSelectedItem(itemToRemove) {
    this.selectedItems = this.selectedItems.filter(
      (item) => item !== itemToRemove
    );
    this.updateInputValue();
  }

  // Surcharge de filterData pour exclure les éléments déjà sélectionnés
  filterData(searchValue) {
    if (searchValue === '') {
      this.clearSuggestions();
      return;
    }
    this.filteredData = this.data.filter((item) => {
      const itemStr = this.itemToString(item).toLowerCase();
      return (
        itemStr.includes(searchValue.toLowerCase()) &&
        !this.selectedItems.includes(item)
      );
    });
    this.showSuggestions(this.filteredData);
  }

  // Affiche les suggestions et lie le clic pour ajouter directement dans l'input
  showSuggestions(suggestions) {
    this.clearSuggestions();

    if (suggestions.length === 0) {
      return;
    }

    suggestions.forEach((item, index) => {
      const suggestionElement = document.createElement('div');
      suggestionElement.classList.add('suggestion-item');
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener('click', () => {
        this.addSelectedItem(item);
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });

    this.activeIndex = -1;
  }
}
