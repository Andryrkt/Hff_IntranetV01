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
    this.suggestionContainer.innerHTML = '';
    this.activeIndex = -1;
  }

  toggleLoader(show) {
    if (this.loaderElement) {
      this.loaderElement.style.display = show ? 'block' : 'none';
    }
  }
}
