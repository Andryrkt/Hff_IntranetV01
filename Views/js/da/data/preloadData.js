import { getAllDesignations, getAllFournisseurs } from "./fetchData";

export async function preloadAllData() {
  const cachedData = localStorage.getItem("autocompleteCache");

  if (cachedData) {
    console.log(
      "Données déjà présentes dans localStorage, pas besoin de fetch"
    );
    return; // On arrête ici, le cache est déjà en place
  }

  try {
    const [fournisseurs, designationsZST, designationsZDI] = await Promise.all([
      getAllFournisseurs(),
      getAllDesignations(false),
      getAllDesignations(true),
    ]);

    localStorage.setItem(
      "autocompleteCache",
      JSON.stringify({
        fournisseurs,
        designationsZST,
        designationsZDI,
      })
    );

    console.log("Données chargées depuis fetch et stockées");
  } catch (e) {
    console.error("Erreur de préchargement des données :", e);
  }
}
