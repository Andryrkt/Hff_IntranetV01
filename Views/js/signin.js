document.addEventListener("DOMContentLoaded", function () {
  const toggleContainer = document.getElementById("toggleIconContainer");

  toggleContainer.addEventListener("click", function () {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("toggleIcon");
    const isPasswordHidden = passwordInput.type === "password";
    passwordInput.type = isPasswordHidden ? "text" : "password";
    toggleContainer.dataset.bsOriginalTitle = isPasswordHidden
      ? "Masquer le mot de passe"
      : "Afficher le mot de passe";
    toggleIcon.classList.toggle("fa-eye");
    toggleIcon.classList.toggle("fa-eye-slash");
  });

  localStorage.clear(); // Vider le localStorage
});
