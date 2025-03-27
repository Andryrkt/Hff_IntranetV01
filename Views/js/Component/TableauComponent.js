export class TableauComponent {
  constructor(props) {
    this.props = props;
    this.state = {
      sortKey: null,
      sortOrder: 'asc', // 'asc' ou 'desc'
    };
    this.container = document.createElement('div');
  }

  setState(newState) {
    this.state = { ...this.state, ...newState };
    this.render(); // Ré-render après mise à jour de l'état
  }

  handleSort(key) {
    const { sortKey, sortOrder } = this.state;
    const newOrder = sortKey === key && sortOrder === 'asc' ? 'desc' : 'asc';
    const sortedData = [...this.props.data].sort((a, b) => {
      if (a[key] < b[key]) return newOrder === 'asc' ? -1 : 1;
      if (a[key] > b[key]) return newOrder === 'asc' ? 1 : -1;
      return 0;
    });
    this.props.data = sortedData; // Tri les données
    this.setState({ sortKey: key, sortOrder: newOrder });
  }

  render() {
    this.container.innerHTML = ''; // Efface l'ancien contenu

    // Créer l'élément table avec classes Bootstrap
    const table = document.createElement('table');
    table.className = 'table rounded table-plein-ecran'; // Classes Bootstrap

    // Définir la classe de l'en-tête (par défaut à 'table-dark' si non spécifiée)
    const theadClass = this.props.theadClass || 'table-dark';

    // Ajouter l'en-tête avec classe personnalisée
    const thead = document.createElement('thead');
    thead.className = theadClass;
    const headerRow = document.createElement('tr');
    this.props.columns.forEach((column) => {
      const th = document.createElement('th');
      th.textContent = column.label;
      th.style.cursor = 'pointer';

      // Appliquer l'alignement si défini
      if (column.align) {
        th.style.textAlign = column.align;
      }

      th.addEventListener('click', () => this.handleSort(column.key)); // Ajouter le tri
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Ajouter le corps
    const tbody = document.createElement('tbody');

    // Vérifier si les données sont présentes
    if (this.props.data && this.props.data.length > 0) {
      // Si des données existent, les afficher
      this.props.data.forEach((row, index) => {
        let tableRow;

        if (this.props.customRenderRow) {
          tableRow = this.props.customRenderRow(row, index, this.props.data);
        } else {
          tableRow = document.createElement('tr');

          this.props.columns.forEach((column) => {
            const td = document.createElement('td');
            const value =
              column.format && typeof column.format === 'function'
                ? column.format(row[column.key])
                : row[column.key] || this.props.defaultValue || '-';

            td.textContent = value;

            if (column.className) {
              td.className = column.className;
            }

            if (column.attributes) {
              Object.entries(column.attributes).forEach(
                ([attrName, attrValue]) => {
                  td.setAttribute(attrName, attrValue);
                }
              );
            }

            if (column.styles && typeof column.styles === 'function') {
              const dynamicStyles = column.styles(row);
              if (dynamicStyles) {
                Object.entries(dynamicStyles).forEach(
                  ([styleName, styleValue]) => {
                    td.style[styleName] = styleValue;
                  }
                );
              }
            }

            if (column.align) {
              td.style.textAlign = column.align;
            }

            tableRow.appendChild(td);
          });
        }

        // Ajout d'une classe personnalisée si définie
        if (this.props.rowClassName) {
          if (typeof this.props.rowClassName === 'function') {
            const dynamicClass = this.props.rowClassName(row);
            if (dynamicClass) {
              tableRow.className = dynamicClass;
            }
          } else {
            tableRow.className = this.props.rowClassName;
          }
        }

        // Ajout de l'événement personnalisé sur le clic de la ligne
        if (this.props.onRowClick) {
          tableRow.addEventListener('click', () => this.props.onRowClick(row));
        }

        tbody.appendChild(tableRow);
      });
    } else {
      // Si aucune donnée, afficher une ligne spéciale
      const noDataRow = document.createElement('tr');
      const noDataCell = document.createElement('td');
      noDataCell.colSpan = this.props.columns.length; // Fusionner toutes les colonnes
      noDataCell.textContent = 'Aucune donnée disponible.';
      noDataCell.style.textAlign = 'center'; // Centrer le texte
      noDataRow.appendChild(noDataCell);
      tbody.appendChild(noDataRow);
    }

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
