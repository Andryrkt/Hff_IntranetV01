import { FetchManager } from "../../api/FetchManager";

const fetchManager = new FetchManager();
const BASE_URL_DA = "demande-appro/autocomplete";

/**
 * Récupère la liste de tous les fournisseurs
 */
export async function getAllFournisseurs() {
  try {
    return await fetchManager.get(`${BASE_URL_DA}/all-fournisseur`);
  } catch (error) {
    console.error("Erreur lors de la récupération des fournisseurs :", error);
    throw error;
  }
}

/**
 * Récupère tous les références autorisées
 */
export async function getAllReferences() {
  try {
    return await fetchManager.get(`${BASE_URL_DA}/all-reference`);
  } catch (error) {
    console.error("Erreur lors de la récupération des références :", error);
    throw error;
  }
}

/**
 * Récupère la liste des articles stockés
 */
export async function getAllArticleStocke() {
  try {
    return await fetchManager.get(`${BASE_URL_DA}/all-article-stocke`);
  } catch (error) {
    console.error(
      "Erreur lors de la récupération des articles stockés :",
      error
    );
    throw error;
  }
}

/**
 * Récupère la liste des désignations
 * @param {boolean} direct - si true, utilise le mode direct ("zdi")
 * @param {string} codeFams1 - premier code famille (par défaut "-")
 * @param {string} codeFams2 - second code famille (par défaut "-")
 */
export async function getAllDesignations(
  direct = false,
  codeFams1 = "-",
  codeFams2 = "-"
) {
  try {
    let endpoint;

    if (direct) {
      endpoint = "all-designation-zdi";
    } else if (codeFams1 === "-" && codeFams2 === "-") {
      // Utiliser la route par défaut pour les paramètres par défaut
      endpoint = "all-designation";
    } else {
      endpoint = `all-designation-zst/${codeFams1}/${codeFams2}`;
    }

    return await fetchManager.get(`${BASE_URL_DA}/${endpoint}`);
  } catch (error) {
    console.error("Erreur lors de la récupération des désignations :", error);
    throw error;
  }
}
