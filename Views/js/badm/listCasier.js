import { FetchManager } from "../FetchManager";

const btnRechercheInput = document.querySelector("#recherche");
const agenceInput = document.querySelector("#agence");
const casierInput = document.querySelector("#casier");

btnRechercheInput.addEventListener("click", sendData);

async function sendData() {
  const agenceValue = agenceInput.value;
  const casierValue = casierInput.value;

  const dataToSend = JSON.stringify({
    agence: agenceValue,
    casier: casierValue,
  });

  const response = await fetch("/Hffintranet/index.php?action=dataRech", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: dataToSend,
  })
    .then((response) => {
      console.log(response);
      return response.json();
    })
    .then((data) => {
      console.log(data);
      //   document.getElementById("response").innerText = data;
    })
    .catch((error) => console.error("Error:", error));
}
