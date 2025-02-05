export async function fetchData(url) {
  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.error('Erreur de récupération des données:', error);
    return [];
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
    return null;
  }
}
