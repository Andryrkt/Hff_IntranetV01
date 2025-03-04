// import { App } from "./component/App.js";
import { config } from "./config/config.js";
import { ServiceCheckboxManager } from "./component/ServiceCheckboxManager.js";
import { fetchDetailModal } from "./detail-modal.js";

// document.addEventListener("DOMContentLoaded", () => {
//   const app = new App({
//     agenceInput: document.querySelector(config.elements.agenceDebiteurInput),
//     serviceContainer: document.querySelector(
//       config.elements.serviceDebiteurInput
//     ),
//     serviceFetchUrl: config.urls.serviceFetch,
//     form: document.querySelector(config.elements.searchForm),
//   });

//   app.init();
// });

// Instanciation et initialisation
const manager = new ServiceCheckboxManager(config);
manager.init();
