
document.addEventListener('DOMContentLoaded', () => {
    const select1 = document.getElementById('select1');
    const select2 = document.getElementById('select2');
    const select3 = document.getElementById('select3');

    // Fonction générique pour faire une requête vers l'API et obtenir des données
    const fetchData = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Erreur : ${response.statusText}`);
            return await response.json();
        } catch (error) {
            console.error('Erreur lors de la récupération des données :', error);
            return [];
        }
    };

    // Fonction pour mettre à jour un select avec des options
    const updateSelect = (select, options) => {
        select.innerHTML = '<option value="" selected disabled>Choose an option</option>';
        select.disabled = options.length === 0;

        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.value;
            opt.textContent = option.label;
            select.appendChild(opt);
        });
    };

    // Initialiser le select1 avec les catégories principales
    const initSelect1 = async () => {
        const categories = await fetchData('/api/categories');  // Exemple d'URL pour l'API
        updateSelect(select1, categories);
    };

    // Mise à jour de select2 en fonction de la sélection dans select1
    select1.addEventListener('change', async () => {
        const category = select1.value;
        if (category) {
            const subCategories = await fetchData(`/api/categories/${category}/subcategories`);
            updateSelect(select2, subCategories);
            select3.innerHTML = '<option value="" selected disabled>Choose an option</option>';
            select3.disabled = true;
        }
    });

    // Mise à jour de select3 en fonction de la sélection dans select2
    select2.addEventListener('change', async () => {
        const subCategory = select2.value;
        if (subCategory) {
            const options = await fetchData(`/api/subcategories/${subCategory}/options`);
            updateSelect(select3, options);
        }
    });

    // Charger initialement les catégories dans select1
    initSelect1();
});