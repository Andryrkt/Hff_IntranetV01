// FetchManager.js
import { baseUrl } from "../utils/config";

export class FetchManager {
  constructor() {
    this.baseUrl = baseUrl;
  }

  async get(endpoint, responseType = "json") {
    const response = await fetch(`${this.baseUrl}/${endpoint}`);
    if (!response.ok) {
      throw new Error(`Failed to fetch data from ${this.baseUrl}/${endpoint}`);
    }
    
    if (responseType === "json") {
      // Lire le texte de la réponse d'abord
      const responseText = await response.text();
      
      try {
        // Vérifier si la réponse est vide
        if (!responseText.trim()) {
          console.warn(`Empty response from ${this.baseUrl}/${endpoint}`);
          return [];
        }
        
        // Nettoyer le texte avant de le parser
        const cleanedText = this.cleanJsonText(responseText);
        
        const data = JSON.parse(cleanedText);
        
        // Vérifier si la réponse contient une erreur
        if (data && data.error) {
          console.error("API Error:", data.message);
          return data.data || []; // Retourner un tableau vide en cas d'erreur
        }
        return data;
      } catch (error) {
        console.error("JSON parsing error:", error);
        console.error("Response text (first 500 chars):", responseText.substring(0, 500));
        console.error("Response length:", responseText.length);
        throw new Error(`Invalid JSON response from ${this.baseUrl}/${endpoint}`);
      }
    }
    
    return await response.text();
  }

  /**
   * Nettoie le texte JSON pour éliminer les caractères problématiques
   */
  cleanJsonText(text) {
    // Supprimer les caractères de contrôle non imprimables
    let cleaned = text.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
    
    // Supprimer les caractères BOM
    cleaned = cleaned.replace(/^\uFEFF/, '');
    
    // Supprimer les espaces en début et fin
    cleaned = cleaned.trim();
    
    // Vérifier que le texte commence et finit par des accolades ou crochets
    if (!cleaned.match(/^[\[\{]/) || !cleaned.match(/[\]\}]$/)) {
      // Essayer de trouver le JSON valide dans le texte
      const jsonMatch = cleaned.match(/[\[\{].*[\]\}]/s);
      if (jsonMatch) {
        cleaned = jsonMatch[0];
      }
    }
    
    return cleaned;
  }

  async post(endpoint, data) {
    const response = await fetch(`${this.baseUrl}/${endpoint}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error(`Failed to post data to ${this.baseUrl}/${endpoint}`);
    }
    return await response.json();
  }

  async put(endpoint, data) {
    const response = await fetch(`${this.baseUrl}/${endpoint}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error(`Failed to put data to ${this.baseUrl}/${endpoint}`);
    }
    return await response.json();
  }

  async delete(endpoint) {
    const response = await fetch(`${this.baseUrl}/${endpoint}`, {
      method: "DELETE",
    });
    if (!response.ok) {
      throw new Error(`Failed to delete data from ${this.baseUrl}/${endpoint}`);
    }
    return await response.json();
  }
}
