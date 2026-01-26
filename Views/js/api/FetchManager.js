// FetchManager.js
import { baseUrl } from "../utils/config";

export class FetchManager {
  constructor() {
    this.baseUrl = baseUrl;
  }

  /**
   * Nettoie le texte JSON pour éliminer les caractères problématiques.
   */
  cleanJsonText(text) {
    // Supprimer les caractères de contrôle non imprimables
    let cleaned = text.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, "");

    // Supprimer les caractères BOM
    cleaned = cleaned.replace(/^\uFEFF/, "");

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

  /**
   * Gère la réponse JSON, y compris le parsing, le nettoyage et la gestion des erreurs.
   */
  async _handleJsonResponse(response, endpoint, method) {
    const responseText = await response.text();
    try {
      if (!responseText.trim()) {
        console.warn(`Empty response from ${this.baseUrl}/${endpoint}`);
        return method === "GET" ? [] : { success: true };
      }
      const cleanedText = this.cleanJsonText(responseText);
      const data = JSON.parse(cleanedText);
      if (data && data.error) {
        console.error("API Error:", data.message);
        return method === "GET" ? data.data || [] : data;
      }
      return data;
    } catch (error) {
      console.error("JSON parsing error:", error);
      console.error(
        "Response text (first 500 chars):",
        responseText.substring(0, 500),
      );
      throw new Error(`Invalid JSON response from ${this.baseUrl}/${endpoint}`);
    }
  }

  /**
   * Méthode de fetch privée pour centraliser la logique de requête.
   */
  async _fetch(endpoint, options = {}, responseType = "json") {
    const response = await fetch(`${this.baseUrl}/${endpoint}`, options);
    if (!response.ok) {
      const errorText = await response
        .text()
        .catch(() => "Could not retrieve error body");
      console.error(
        `HTTP Error ${response.status}: ${response.statusText}`,
        `URL: ${response.url}`,
        `Response: ${errorText}`,
      );
      throw new Error(
        `Request to ${endpoint} failed with status ${response.status}`,
      );
    }
    if (responseType === "json") {
      return this._handleJsonResponse(
        response,
        endpoint,
        options.method || "GET",
      );
    }
    return response.text();
  }

  async get(endpoint, responseType = "json") {
    return this._fetch(endpoint, { method: "GET" }, responseType);
  }

  async post(endpoint, data) {
    return this._fetch(
      endpoint,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      },
      "json",
    );
  }

  async put(endpoint, data) {
    return this._fetch(
      endpoint,
      {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      },
      "json",
    );
  }

  async delete(endpoint) {
    return this._fetch(endpoint, { method: "DELETE" }, "json");
  }
}
