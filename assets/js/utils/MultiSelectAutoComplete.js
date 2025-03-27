export class MultiSelectAutoComplete extends AutoComplete {
  constructor(options) {
    // Vous pouvez passer les options nécessaires à la classe parent
    super(options);
    this.selectedItems = [];
    this.selectedItemsContainer = options.selectedItemsContainer; // conteneur dédié
  }

  addSelectedItem(item) {
    if (!this.selectedItems.includes(item)) {
      this.selectedItems.push(item);
      // Vous pouvez appeler un callback supplémentaire ici si besoin
      this.onSelectCallback(item);
      this.updateSelectedItemsDisplay();
    }
    this.inputElement.value = "";
    this.clearSuggestions();
  }

  updateSelectedItemsDisplay() {
    this.selectedItemsContainer.innerHTML = "";
    this.selectedItems.forEach((item, index) => {
      const itemElement = document.createElement("span");
      itemElement.classList.add("selected-item");
      itemElement.innerText = this.itemToString(item);

      const removeButton = document.createElement("button");
      removeButton.innerText = "x";
      removeButton.addEventListener("click", () => {
        this.removeSelectedItem(index);
      });

      itemElement.appendChild(removeButton);
      this.selectedItemsContainer.appendChild(itemElement);
    });
  }

  removeSelectedItem(index) {
    this.selectedItems.splice(index, 1);
    this.updateSelectedItemsDisplay();
  }

  // On surcharge showSuggestions pour utiliser addSelectedItem
  showSuggestions(suggestions) {
    this.clearSuggestions();

    if (suggestions.length === 0) {
      return;
    }

    suggestions.forEach((item, index) => {
      const suggestionElement = document.createElement("div");
      suggestionElement.classList.add("suggestion-item");
      suggestionElement.innerHTML = this.displayItemCallback(item);
      suggestionElement.dataset.index = index;

      suggestionElement.addEventListener("click", () => {
        this.addSelectedItem(item);
      });

      this.suggestionContainer.appendChild(suggestionElement);
    });

    this.activeIndex = -1;
  }

  // Optionnel : redéfinir filterData pour exclure les items déjà sélectionnés
  filterData(searchValue) {
    if (searchValue === "") {
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
}
