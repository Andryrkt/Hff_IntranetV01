// Validation spécifique selon le type de formulaire
export async function validateSpecificForm(form, formSelector) {
  // Récupérer le type de formulaire depuis un attribut data ou l'ID
  const formType =
    form.getAttribute("data-form-type") || form.id || formSelector;

  switch (formType) {
    case "formulaire-new-dit":
    case "#dit-form":
    case "dit-form":
      return validationDitForm(form);
    case "formulaire-inscription":
    case "#inscription-form":
    case "inscription-form":
      return validateInscriptionForm(form);

    case "formulaire-commande":
    case "#commande-form":
    case "commande-form":
      return validateCommandeForm(form);

    case "formulaire-contact":
    case "#contact-form":
    case "contact-form":
      return validateContactForm(form);

    case "formulaire-paiement":
    case "#paiement-form":
    case "paiement-form":
      return validatePaiementForm(form);

    default:
      // Validation par défaut si le formulaire n'est pas reconnu
      return { isValid: true, message: "" };
  }
}

function validationDitForm(form) {
  const errors = [];

  //si ATE POL TANA
  //  type de document doit egale maintenace curative
  // et catégorie = REPARATION
  const reparationRealiseSelect = form.querySelector(
    '[name="demande_intervention[reparationRealise]"]'
  );
  const typeDocumentSelect = form.querySelector(
    '[name="demande_intervention[typeDocument]"]'
  );
  const categorieSelect = form.querySelector(
    '[name="demande_intervention[categorieDemande]"]'
  );
  const MAINTENANCE_CURATIVE = 6;
  const REPARATION = 7;
  if (
    reparationRealiseSelect &&
    reparationRealiseSelect.value === "ATE POL TANA" &&
    typeDocumentSelect &&
    parseInt(typeDocumentSelect.value, 10) !== MAINTENANCE_CURATIVE &&
    categorieSelect &&
    parseInt(categorieSelect.value, 10) !== REPARATION
  ) {
    errors.push(
      "Rectifiez le type de document en Maintenance curative et le catégorie de demande en REPARATION"
    );
  }

  return {
    isValid: errors.length === 0,
    message: errors.join("<br>"),
    title: "Erreur pour ATE POL TANA",
  };
}

// Validation pour le formulaire d'inscription
function validateInscriptionForm(form) {
  const errors = [];

  // Validation email
  const email = form.querySelector('[name="email"]');
  if (email && email.value) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
      errors.push("L'adresse email n'est pas valide");
    }
  }

  // Validation mot de passe
  const password = form.querySelector('[name="password"]');
  const confirmPassword = form.querySelector('[name="confirm_password"]');
  if (password && confirmPassword) {
    if (password.value.length < 8) {
      errors.push("Le mot de passe doit contenir au moins 8 caractères");
    }
    if (password.value !== confirmPassword.value) {
      errors.push("Les mots de passe ne correspondent pas");
    }
  }

  // Validation âge
  const age = form.querySelector('[name="age"]');
  if (age && age.value) {
    const ageValue = parseInt(age.value);
    if (ageValue < 18 || ageValue > 100) {
      errors.push("L'âge doit être compris entre 18 et 100 ans");
    }
  }

  return {
    isValid: errors.length === 0,
    message: errors.join("<br>"),
    title: "Erreur d'inscription",
  };
}

// Validation pour le formulaire de commande
function validateCommandeForm(form) {
  const errors = [];

  // Validation quantité
  const quantite = form.querySelector('[name="quantite"]');
  if (quantite && quantite.value) {
    const qty = parseInt(quantite.value);
    if (qty <= 0 || qty > 1000) {
      errors.push("La quantité doit être comprise entre 1 et 1000");
    }
  }

  // Validation date de livraison
  const dateLivraison = form.querySelector('[name="date_livraison"]');
  if (dateLivraison && dateLivraison.value) {
    const livraisonDate = new Date(dateLivraison.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (livraisonDate < today) {
      errors.push("La date de livraison ne peut pas être dans le passé");
    }
  }

  // Validation stock (exemple avec appel API)
  const produitId = form.querySelector('[name="produit_id"]');
  if (produitId && produitId.value && quantite) {
    // Ici vous pourriez faire une vérification asynchrone du stock
    const stockDisponible = checkStock(produitId.value);
    if (stockDisponible < parseInt(quantite.value)) {
      errors.push("Stock insuffisant pour ce produit");
    }
  }

  return {
    isValid: errors.length === 0,
    message: errors.join("<br>"),
    title: "Erreur de commande",
  };
}

// Validation pour le formulaire de contact
function validateContactForm(form) {
  const errors = [];

  // Validation téléphone
  const telephone = form.querySelector('[name="telephone"]');
  if (telephone && telephone.value) {
    const phoneRegex = /^[0-9+\-\s()]{10,}$/;
    if (!phoneRegex.test(telephone.value.replace(/\s/g, ""))) {
      errors.push("Le numéro de téléphone n'est pas valide");
    }
  }

  // Validation sujet
  const sujet = form.querySelector('[name="sujet"]');
  if (sujet && sujet.value) {
    if (sujet.value.length < 5) {
      errors.push("Le sujet doit contenir au moins 5 caractères");
    }
  }

  // Validation message
  const message = form.querySelector('[name="message"]');
  if (message && message.value) {
    if (message.value.length < 10) {
      errors.push("Le message doit contenir au moins 10 caractères");
    }
    if (message.value.length > 1000) {
      errors.push("Le message ne peut pas dépasser 1000 caractères");
    }
  }

  return {
    isValid: errors.length === 0,
    message: errors.join("<br>"),
    title: "Erreur de formulaire de contact",
  };
}

// Validation pour le formulaire de paiement
function validatePaiementForm(form) {
  const errors = [];

  // Validation carte de crédit
  const carteCredit = form.querySelector('[name="numero_carte"]');
  if (carteCredit && carteCredit.value) {
    const cleanedCard = carteCredit.value.replace(/\s/g, "");
    if (!/^\d{16}$/.test(cleanedCard)) {
      errors.push("Le numéro de carte doit contenir 16 chiffres");
    }
  }

  // Validation date d'expiration
  const expiration = form.querySelector('[name="date_expiration"]');
  if (expiration && expiration.value) {
    const [month, year] = expiration.value.split("/");
    const expDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
    const today = new Date();

    if (expDate < today) {
      errors.push("La carte de crédit a expiré");
    }
  }

  // Validation CVV
  const cvv = form.querySelector('[name="cvv"]');
  if (cvv && cvv.value) {
    if (!/^\d{3,4}$/.test(cvv.value)) {
      errors.push("Le code CVV doit contenir 3 ou 4 chiffres");
    }
  }

  return {
    isValid: errors.length === 0,
    message: errors.join("<br>"),
    title: "Erreur de paiement",
  };
}

// Fonction utilitaire pour vérifier le stock (exemple)
async function checkStock(produitId) {
  try {
    const response = await fetch(`/api/stock/${produitId}`);
    const data = await response.json();
    return data.stock;
  } catch (error) {
    console.error("Erreur lors de la vérification du stock:", error);
    return 0;
  }
}

// Export des fonctions individuelles si besoin de les utiliser séparément
export {
  validateInscriptionForm,
  validateCommandeForm,
  validateContactForm,
  validatePaiementForm,
};
