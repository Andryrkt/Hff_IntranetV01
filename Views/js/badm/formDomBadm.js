const form = document.form;

/**
 * informer l'utilisateur si le type de fichier et la taille de l'image ne  correspond pas à ce qu'on attend
 * @param {*} event 
 */
export function verifierTailleEtType(event) {
    const fichier = event.target.files[0]; // On obtient le fichier sélectionné
    if (fichier) {
      // Taille maximale en octets 
      const tailleMax = 1*1024*1024; // 1MB
      const typesValides = ['image/jpeg', 'image/png'];

      if (!typesValides.includes(fichier.type)) {
        alert("Erreur : Le fichier doit être au format PNG, JPG ou JPEG.");
        event.target.value = ''; // Réinitialise le champ de sélection de fichier
      } else if (fichier.size > tailleMax) {
        alert(`Erreur : La taille du fichier doit être inférieure à ${tailleMax / (1024 * 1024)} MB.`);
        event.target.value = ''; // Réinitialise le champ de sélection de fichier
      } else {
        // Le fichier est valide, vous pouvez procéder à l'upload ou à d'autres traitements ici
        console.log("Fichier valide. Procéder à l'upload ou autre.");
      }
    }
  }


/**
 * permet de formater le nombre en limitant 2 chiffre après la virgule et séparer les millier par un point
 */
export function formatNumber() {
    let input = document.getElementById('numberInput').value;
    let number = parseFloat(input);
    if (!isNaN(number)) {
        // Formater le nombre en utilisant la locale fr-FR
        let formatted = number.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        // Remplacer les espaces par des points pour les séparateurs de milliers
        formatted = formatted.replace(/\s/g, '.');
        document.getElementById('formattedNumber').textContent = formatted;
    }
}


/**
 * changement de couleur pour le code de mouvemnet ou type de demande
 * @param {*} typeDemande 
 */
export function typeDemandeChangementCouleur(typeDemande){

  const codeMouvement = document.querySelector('#codeMouvement')
  
  if (typeDemande === 'ENTREE EN PARC') {
      codeMouvement.classList.add('codeMouvementParc')
  } else if (typeDemande === 'CHANGEMENT AGENCE/SERVICE') {
      codeMouvement.classList.add('codeMouvementAgenceService')
  } else if(typeDemande === 'CHANGEMENT DE CASIER') {
      codeMouvement.classList.add('codeMouvementCasier')
  } else if(typeDemande === 'CESSION D\'ACTIF') {
      codeMouvement.classList.add('codeMouvementActif')
  } else if(typeDemande === 'MISE AU REBUT') {
      codeMouvement.classList.add('codeMouvementRebut')
  }
}

export function envoieformulaire(e)
{
    e.preventDefault(); 
    if(confirm("Veuillez vérifier attentivement avant d'envoyer."))
    {
        form.submit();
    }


    // Swal.fire({
    //     title: "Vous confirmez ?",
    //     text: "Veuillez vérifier attentivement avant d'envoyer.",
    //     icon: "warning",
    //     showCancelButton: true,
    //     confirmButtonColor: "#3085d6",
    //     cancelButtonColor: "#d33",
    //     confirmButtonText: "Oui"
    // }).then((result) => {
    //     if (result.isConfirmed) {
    //         Swal.fire({
    //             title: "Envoyer!",
    //             text: "Votre demande a été bien enregistrée",
    //             icon: "success"
    //         }).then(() => {
               
    //             formCompleBadm.submit();
    //         });
    //     }
    // });
}