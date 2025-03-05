import { FetchManager } from '../api/FetchManager';

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

export async function fetchData(endpoint) {
  try {
    const response = await fetchManager.get(endpoint);
    if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.error('Erreur de récupération des données:', error);
    return [];
  }
}
