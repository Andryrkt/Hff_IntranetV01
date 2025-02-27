// FetchManager.js
export class FetchManager {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    async get(endpoint) {
        const response = await fetch(`${this.baseUrl}/${endpoint}`);
        if (!response.ok) {
            throw new Error(`Failed to fetch data from ${this.baseUrl}/${endpoint}`);
        }
        return await response.json();
    }

    async post(endpoint, data) {
        const response = await fetch(`${this.baseUrl}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            throw new Error(`Failed to post data to ${this.baseUrl}/${endpoint}`);
        }
        return await response.json();
    }

    async put(endpoint, data) {
        const response = await fetch(`${this.baseUrl}/${endpoint}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            throw new Error(`Failed to put data to ${this.baseUrl}/${endpoint}`);
        }
        return await response.json();
    }

    async delete(endpoint) {
        const response = await fetch(`${this.baseUrl}/${endpoint}`, {
            method: 'DELETE'
        });
        if (!response.ok) {
            throw new Error(`Failed to delete data from ${this.baseUrl}/${endpoint}`);
        }
        return await response.json();
    }
}


