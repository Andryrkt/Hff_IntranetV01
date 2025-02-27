// Sauvegarder l'onglet actif dans localStorage
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('button.nav-link');

  tabs.forEach((tab) => {
    tab.addEventListener('click', function () {
      localStorage.setItem('activeTab', this.getAttribute('data-bs-target'));
    });
  });

  // Restaurer l'onglet actif depuis localStorage
  const activeTab = localStorage.getItem('activeTab');
  if (activeTab) {
    const tabToActivate = document.querySelector(
      `button[data-bs-target="${activeTab}"]`
    );
    if (tabToActivate) {
      new bootstrap.Tab(tabToActivate).show();
    }
  } else {
    new bootstrap.Tab(document.querySelector('#nav-categorie-tab')).show();
  }
});
