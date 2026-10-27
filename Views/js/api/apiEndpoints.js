export const API_ENDPOINTS = {
  DELETE_ARTICLES_DA: "api/demande-appro/action-sur-non-dispo/delete-articles",
  CREATE_ARTICLES_DA:
    "api/demande-appro/action-sur-non-dispo/create-new-articles",
  getArticlesDaReappro: (codeAgence, codeService) =>
    `api/demande-appro/agences/${codeAgence}/services/${codeService}/articles-reappro`,
  commandModal: (numOr) => `api/command-modal/${numOr}`,
};
