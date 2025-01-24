import config from "../config/config.js";
import { createElement, clearChildren } from "../utils/domUtils.js";

export default class ServiceCheckboxManager {
  constructor() {
    this.agenceDebiteurInput = document.querySelector(
      config.elements.agenceDebiteurInput
    );
    this.serviceDebiteurInput = document.querySelector(
      config.elements.serviceDebiteurInput
    );
    this.searchForm = document.querySelector(config.elements.searchForm);
    this.selectAllCheckbox = null;
  }

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.ensureSelectAllCheckbox();
      this.attachCheckboxEventListeners();
      this.selectAllCheckboxByDefault();

      if (this.searchForm) {
        this.searchForm.addEventListener("submit", () => {
          setTimeout(() => {
            this.refreshStateAfterSubmit();
          }, 100);
        });
      }

      if (this.agenceDebiteurInput) {
        this.agenceDebiteurInput.addEventListener("change", () =>
          this.handleAgenceChange()
        );
      }
    });
  }

  handleAgenceChange() {
    this.serviceDebiteurInput.disabled = false;
    const agenceDebiteur = this.agenceDebiteurInput.value;
    const url = config.urls.serviceFetch(agenceDebiteur);

    fetch(url)
      .then((response) => response.json())
      .then((services) => {
        this.updateServiceCheckboxes(services);
        this.selectAllCheckboxByDefault();
      })
      .catch((error) => console.error("Error:", error));
  }

  updateServiceCheckboxes(services) {
    clearChildren(this.serviceDebiteurInput);
    services.forEach((service, index) => {
      const checkbox = createElement("input", {
        type: "checkbox",
        name: "planning_search[serviceDebite][]",
        value: service.value,
        id: `service_${index}`,
        className: "form-check-input",
        checked: true,
      });

      const label = createElement(
        "label",
        {
          htmlFor: checkbox.id,
          className: "form-check-label",
        },
        [service.text]
      );

      const div = createElement("div", { className: "form-check" }, [
        checkbox,
        label,
      ]);
      this.serviceDebiteurInput.appendChild(div);
    });

    this.attachCheckboxEventListeners();
  }

  ensureSelectAllCheckbox() {
    if (!this.selectAllCheckbox) {
      const selectAllDiv = createElement("div", { className: "form-check" }, [
        (this.selectAllCheckbox = createElement("input", {
          type: "checkbox",
          id: config.elements.selectAllCheckbox.substring(1),
          className: "form-check-input",
        })),
        createElement(
          "label",
          {
            htmlFor: config.elements.selectAllCheckbox.substring(1),
            className: "form-check-label",
          },
          ["Tout sélectionner"]
        ),
      ]);

      this.serviceDebiteurInput.insertBefore(
        selectAllDiv,
        this.serviceDebiteurInput.firstChild
      );

      this.selectAllCheckbox.addEventListener("change", (event) =>
        this.handleSelectAllChange(event)
      );
    }
  }

  attachCheckboxEventListeners() {
    const checkboxes = this.getServiceCheckboxes();
    checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", () =>
        this.handleServiceCheckboxChange()
      );
    });
  }

  handleSelectAllChange(event) {
    const checkboxes = this.getServiceCheckboxes();
    checkboxes.forEach((checkbox) => {
      checkbox.checked = event.target.checked;
    });
  }

  handleServiceCheckboxChange() {
    const checkboxes = this.getServiceCheckboxes();
    const allChecked = Array.from(checkboxes).every(
      (checkbox) => checkbox.checked
    );
    this.selectAllCheckbox.checked = allChecked;
  }

  selectAllCheckboxByDefault() {
    const checkboxes = this.getServiceCheckboxes();
    const allChecked = Array.from(checkboxes).every(
      (checkbox) => checkbox.checked
    );
    this.selectAllCheckbox.checked = allChecked;
  }

  getServiceCheckboxes() {
    return document.querySelectorAll(
      'input[name="planning_search[serviceDebite][]"]'
    );
  }

  refreshStateAfterSubmit() {
    this.ensureSelectAllCheckbox();
    this.attachCheckboxEventListeners();
    this.selectAllCheckboxByDefault();
  }
}
