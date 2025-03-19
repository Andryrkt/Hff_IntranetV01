import { FetchManager } from '../../js/api/FetchManager';

const btnRechercheInput = document.querySelector('#recherche');
const agenceInput = document.querySelector('#agence');
const casierInput = document.querySelector('#casier');

btnRechercheInput.addEventListener('click', sendData);

async function sendData() {
  const agenceValue = agenceInput.value;
  const casierValue = casierInput.value;

  // Instanciation de FetchManager avec la base URL
  const fetchManager = new FetchManager();
  const response = await fetchManager
    .post('index.php?action=dataRech', {
      agence: agenceValue,
      casier: casierValue,
    })
    .then((data) => {
      console.log(data);
      //document.getElementById("response").innerText = data;
    })
    .catch((error) => console.error('Error:', error));
}
