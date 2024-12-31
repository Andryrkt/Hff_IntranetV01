export class TableauComponent {
  constructor(props) {
    this.props = props;
    this.state = {
      sortKey: null,
      sortOrder: "asc", // 'asc' ou 'desc'
    };
    this.container = document.createElement("div");
  }

  setState(newState) {
    this.state = { ...this.state, ...newState };
    this.render(); // Ré-render après mise à jour de l'état
  }

  handleSort(key) {
    const { sortKey, sortOrder } = this.state;
    const newOrder = sortKey === key && sortOrder === "asc" ? "desc" : "asc";
    const sortedData = [...this.props.data].sort((a, b) => {
      if (a[key] < b[key]) return newOrder === "asc" ? -1 : 1;
      if (a[key] > b[key]) return newOrder === "asc" ? 1 : -1;
      return 0;
    });
    this.props.data = sortedData; // Tri les données
    this.setState({ sortKey: key, sortOrder: newOrder });
  }

  render() {
    this.container.innerHTML = ""; // Efface l'ancien contenu

    // Créer l'élément table avec classes Bootstrap
    const table = document.createElement("table");
    table.className =
      "table table-bordered table-hover table-striped rounded table-plein-ecran"; // Classes Bootstrap

    // Ajouter l'en-tête avec classe `table-dark`
    const thead = document.createElement("thead");
    thead.className = "table-dark";
    const headerRow = document.createElement("tr");
    this.props.columns.forEach((column) => {
      const th = document.createElement("th");
      th.textContent = column.label;
      th.style.cursor = "pointer";

      // Centrer l'en-tête si align est défini
      if (column.align) {
        th.style.textAlign = column.align;
      }

      th.addEventListener("click", () => this.handleSort(column.key)); // Ajouter le tri
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Ajouter le corps
    const tbody = document.createElement("tbody");
    this.props.data.forEach((row) => {
      const tableRow = document.createElement("tr");
      this.props.columns.forEach((column) => {
        const td = document.createElement("td");
        td.textContent = row[column.key];

        // Centrer les cellules si align est défini
        if (column.align) {
          td.style.textAlign = column.align;
        }

        tableRow.appendChild(td);
      });
      tbody.appendChild(tableRow);
    });
    table.appendChild(tbody);

    this.container.appendChild(table);

    return this.container;
  }

  mount(targetId) {
    const target = document.getElementById(targetId);
    if (target) {
      target.appendChild(this.render());
    } else {
      throw new Error(`Le conteneur avec l'ID "${targetId}" n'existe pas.`);
    }
  }
}
