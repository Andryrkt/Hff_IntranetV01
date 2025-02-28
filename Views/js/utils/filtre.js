export  function filterRowsByColumn(filterClass) {
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach((row) => {
      let hasMatchingCell = false;

      // Parcourt toutes les cellules de la ligne
      const cells = row.querySelectorAll("td");
      cells.forEach((cell) => {
        const links = cell.querySelectorAll("a"); // Tous les liens dans la cellule

        if (!filterClass) {
          // Si "Tout afficher", montre toutes les lignes et cellules
          links.forEach((link) => (link.style.display = ""));
          hasMatchingCell = true; // La ligne reste visible
        } else {
          // Filtre par classe
          let cellMatches = false;
          links.forEach((link) => {
            if (link.classList.contains(filterClass)) {
              link.style.display = ""; // Affiche les liens correspondant
              cellMatches = true;
            } else {
              link.style.display = "none"; // Cache les liens non correspondants
            }
          });

          if (cellMatches) {
            hasMatchingCell = true; // Marque la ligne comme ayant une correspondance
          }
        }
      });

      // Masque ou affiche la ligne entière
      row.style.display = hasMatchingCell ? "" : "none";
    });
  }