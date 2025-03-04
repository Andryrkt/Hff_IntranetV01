import {
  cellIndicesOrATraiter,
  cellIndicesOrALivrer,
  cellIndicesCisATraiter,
  cellIndicesCisALivrer,
  cellIndicesListePlanning,
} from "./cellIndicesConfig.js";
export const config = {
  or_a_traiter: {
    tableBody: "#tableBody",
    agenceInput: "#magasin_liste_or_a_traiter_search_agence",
    serviceInput: "#magasin_liste_or_a_traiter_search_service",
    spinnerService: "#spinner-service",
    serviceContainer: "#service-container",
    numDitInput: "#magasin_liste_or_a_traiter_search_numDit",
    refPieceInput: "#magasin_liste_or_a_traiter_search_referencePiece",
    numOrInput: "#magasin_liste_or_a_traiter_search_numOr",
    cellIndices: cellIndicesOrATraiter, // Utilise la config avec `user: 16`
  },
  or_a_livrer: {
    tableBody: "#tableBody",
    agenceInput: "#magasin_liste_or_a_livrer_search_agence",
    serviceInput: "#magasin_liste_or_a_livrer_search_service",
    spinnerService: "#spinner-service",
    serviceContainer: "#service-container",
    numDitInput: "#magasin_liste_or_a_livrer_search_numDit",
    refPieceInput: "#magasin_liste_or_a_livrer_search_referencePiece",
    numOrInput: "#magasin_liste_or_a_livrer_search_numOr",
    cellIndices: cellIndicesOrALivrer, // Utilise la config avec `user: 18`
  },
  cis_a_traiter: {
    tableBody: "#tableBody",
    agenceInput: "#a_traiter_search_agence",
    serviceInput: "#a_traiter_search_service",
    spinnerService: "#spinner-service",
    serviceContainer: "#service-container",
    numDitInput: "#a_traiter_search_numDit",
    refPieceInput: "#a_traiter_search_referencePiece",
    numOrInput: "#a_traiter_search_numOr",
    cellIndices: cellIndicesCisATraiter,
  },
  cis_a_livrer: {
    tableBody: "#tableBody",
    agenceInput: "#a_livrer_search_agence",
    serviceInput: "#a_livrer_search_service",
    spinnerService: "#spinner-service",
    serviceContainer: "#service-container",
    numDitInput: "#a_livrer_search_numDit",
    refPieceInput: "#a_livrer_search_referencePiece",
    numOrInput: "#a_livrer_search_numOr",
    cellIndices: cellIndicesCisALivrer,
  },
  liste_planning: {
    tableBody: "#tableBody",
    cellIndices: cellIndicesListePlanning
  }
};
