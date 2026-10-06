export const config = {
  elements: {
    agenceDebiteurInput: "#planning_search_agenceDebite",
    serviceDebiteurInput: "#planning_search_serviceDebite",
    selectAllCheckbox: "#planning_search_selectAll",
    searchForm: "#planning_search_form",
  },
  urls: {
    serviceFetch: (agenceDebiteur) =>
      `api/serviceDebiteurPlanning-fetch/${agenceDebiteur}`,
  },
};
