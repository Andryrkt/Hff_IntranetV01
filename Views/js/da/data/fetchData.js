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
    const endpoint = direct
      ? "all-designation-zdi"
      : `all-designation-zst/${codeFams1}/${codeFams2}`;

    return await fetchManager.get(`${BASE_URL_DA}/${endpoint}`);
  } catch (error) {
    console.error("Erreur lors de la récupération des désignations :", error);
    throw error;
  }
}
