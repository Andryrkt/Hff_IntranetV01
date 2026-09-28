export const API_ENDPOINTS = {
  /** Demande Approvisionnement */
  DELETE_ARTICLES_DA: "api/demande-appro/da-list-cde-frn/delete-articles",
  CREATE_ARTICLES_DA: "api/demande-appro/da-list-cde-frn/create-new-articles",
  getArticlesDaReappro: (codeAgence, codeService) =>
    `api/demande-appro/agences/${codeAgence}/services/${codeService}/articles-reappro`,

  /** Ordre de réparation (OR) */
  commandModal: (numOr) => `api/command-modal/${numOr}`,

  /** Commande Fournissuer Magasin */
  generatePdfCdeFrnMag: (numCde) => `api/cde-frn/${numCde}/generate-pdf`,
};
