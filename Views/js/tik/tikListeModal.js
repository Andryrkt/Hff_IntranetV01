/** COMMENTAIRE MODAL */
const commentaireModal = document.getElementById('commentaire');

commentaireModal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget; // Button that triggered the modal
  const id = button.getAttribute('data-id'); // Extract info from data-* attributes
  const text = button.textContent;

  const textDiv = document.getElementById('text-content');

  if (text === '--') {
    textDiv.textContent = 'Pas de commentaire';
  } else {
    textDiv.textContent = text;
  }
});
