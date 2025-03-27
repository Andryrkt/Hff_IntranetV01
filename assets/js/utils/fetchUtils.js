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

export async function postData(url, data) {
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.error("Erreur lors de l'envoi des données:", error);
    return [];
  }
}
