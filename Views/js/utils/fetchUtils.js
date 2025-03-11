import { FetchManager } from '../api/FetchManager';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

export async function fetchData(endpoint) {
  try {
    return await fetchManager.get(endpoint); // Déjà JSON
  } catch (error) {
    console.error(`Erreur de récupération des données (${endpoint}):`, error);
    throw error; // Propager l'erreur au lieu de retourner []
  }
}
