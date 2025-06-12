import { FetchManager } from '../../api/FetchManager';

export class ServiceCheckboxManager {
  constructor(config) {
    this.config = config;
    this.state = {
      services: [],
      allChecked: false,
      agenceSelected: false,
      isLoading: false,
    };

    this.root = document.querySelector(config.elements.serviceDebiteurInput);

    // Fixer le contexte de `this`
    this.handleAgenceChange = this.handleAgenceChange.bind(this);
    this.handleSelectAllChange = this.handleSelectAllChange.bind(this);
    this.handleServiceCheckboxChange =
      this.handleServiceCheckboxChange.bind(this);
    this.handleFormSubmit = this.handleFormSubmit.bind(this);
  }

  init() {
    const agenceDebiteurInput = document.querySelector(
      this.config.elements.agenceDebiteurInput
    );

    const searchForm = document.querySelector(this.config.elements.searchForm);

    document.addEventListener('DOMContentLoaded', () => {
      this.render();

      searchForm.addEventListener('submit', this.handleFormSubmit);
    });

    agenceDebiteurInput.addEventListener('change', this.handleAgenceChange);
  }

  setState(newState) {
    this.state = { ...this.state, ...newState };
    this.render();
  }

  handleFormSubmit(event) {
    event.preventDefault(); // Empêche le rechargement de la page
    console.log('Formulaire soumis. État actuel :', this.state);
  }

  handleAgenceChange(event) {
    const agenceDebiteur = event.target.value;

    if (!agenceDebiteur) {
      this.setState({ services: [], allChecked: false, agenceSelected: false });
      return;
    }

    this.setState({ isLoading: true, agenceSelected: true });

    // Instanciation de FetchManager avec la base URL
    const fetchManager = new FetchManager();
    const url = this.config.urls.serviceFetch(agenceDebiteur);

    fetchManager
      .get(url)
      .then((services) => {
        const updatedServices = services.map((service) => ({
          ...service,
          checked: true,
        }));
        this.setState({
          services: updatedServices,
          allChecked: true,
          isLoading: false,
        });
      })
      .catch((error) => {
        console.error('Error fetching services:', error);
        this.setState({ isLoading: false });
      });
  }

  handleSelectAllChange(event) {
    const allChecked = event.target.checked;
    const updatedServices = this.state.services.map((service) => ({
      ...service,
      checked: allChecked,
    }));

    this.setState({ services: updatedServices, allChecked });
  }

  handleServiceCheckboxChange(event) {
    const serviceId = event.target.value;
    const updatedServices = this.state.services.map((service) =>
      service.value === serviceId
        ? { ...service, checked: event.target.checked }
        : service
    );

    const allChecked = updatedServices.every((service) => service.checked);

    this.setState({ services: updatedServices, allChecked });
  }

  renderSpinner() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border text-primary';
    spinner.role = 'status';
    spinner.innerHTML = '<span class="sr-only">Chargement...</span>';
    return spinner;
  }

  renderSelectAllCheckbox() {
    const div = document.createElement('div');
    div.className = 'form-check';

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = 'planning_search_selectAll';
    checkbox.className = 'form-check-input';
    checkbox.checked = this.state.allChecked;
    checkbox.addEventListener('change', this.handleSelectAllChange);

    const label = document.createElement('label');
    label.htmlFor = 'planning_search_selectAll';
    label.className = 'form-check-label';
    label.textContent = 'Tout sélectionner';

    div.appendChild(checkbox);
    div.appendChild(label);

    return div;
  }

  renderServiceCheckboxes() {
    return this.state.services.map((service, index) => {
      const div = document.createElement('div');
      div.className = 'form-check';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.name = 'planning_search[serviceDebite][]';
      checkbox.value = service.value;
      checkbox.id = `service_${index}`;
      checkbox.className = 'form-check-input';
      checkbox.checked = service.checked;
      checkbox.addEventListener('change', this.handleServiceCheckboxChange);

      const label = document.createElement('label');
      label.htmlFor = checkbox.id;
      label.className = 'form-check-label';
      label.textContent = service.text;

      div.appendChild(checkbox);
      div.appendChild(label);
      return div;
    });
  }

  render() {
    // Efface les enfants existants du conteneur
    while (this.root.firstChild) {
      this.root.removeChild(this.root.firstChild);
    }

    if (this.state.isLoading) {
      this.root.appendChild(this.renderSpinner());
      return;
    }

    if (this.state.agenceSelected) {
      this.root.appendChild(this.renderSelectAllCheckbox());
    }

    this.renderServiceCheckboxes().forEach((checkbox) => {
      this.root.appendChild(checkbox);
    });
  }
}
