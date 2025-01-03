const toggleContainer = document.getElementById('toggleIconContainer');

toggleContainer.addEventListener('click', function () {
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('toggleIcon');

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.classList.remove('fa-eye');
    toggleIcon.classList.add('fa-eye-slash');
    toggleContainer.title = 'Masquer le mot de passe';
  } else {
    passwordInput.type = 'password';
    toggleIcon.classList.remove('fa-eye-slash');
    toggleIcon.classList.add('fa-eye');
    toggleContainer.title = 'Afficher le mot de passe';
  }
});

localStorage.clear(); // Vider le localStorage
