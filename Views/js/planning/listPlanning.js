/**
 * RECUPERATION DES SERVICE PAR RAPPORT à l'AGENCE
 */
const agenceDebiteurInput = document.querySelector(
    "#planning_search_agenceDebite"
  );
  const serviceDebiteurInput = document.querySelector(
    "#planning_search_serviceDebite"
  );
agenceDebiteurInput.addEventListener('change',selectAgence);

  function selectAgence() {
    serviceDebiteurInput.disabled = false;
    
    const agenceDebiteur = agenceDebiteurInput.value;
    let url = `/Hffintranet/serviceDebiteurPlanning-fetch/${agenceDebiteur}`;
    fetch(url)
      .then((response) => response.json())
      .then((services) => {
        console.log(services);

        // Effacer les éléments existants dans le conteneur
        serviceDebiteurInput.innerHTML = ""; 
      
        for (var i = 0; i < services.length; i++) {
          var div = document.createElement("div");
          div.className = "form-check";
      
          var checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.name = 'planning_search[serviceDebite][]';
          checkbox.value = services[i].value;
          checkbox.id = "service_" + i;
          checkbox.className = "form-check-input";
      
          var label = document.createElement("label");
          label.htmlFor = checkbox.id;
          label.appendChild(document.createTextNode(services[i].text));
          label.className = "form-check-label";
      
          div.appendChild(checkbox);
          div.appendChild(label);
      
          serviceDebiteurInput.appendChild(div);
        }
        
      })
      .catch((error) => console.error("Error:", error));
  }
  
  /** LIST DETAIL MODAL */

document.addEventListener("DOMContentLoaded", (event) => {
  const listeCommandeModal = document.getElementById("listeCommande");

  listeCommandeModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const id = button.getAttribute("data-id"); // Extract info from data-* attributes

    // Afficher le spinner et masquer le contenu des données
    document.getElementById("loading").style.display = "block";
    document.getElementById("dataContent").style.display = "none";

    // Fetch request to get the data
    fetch(`/Hffintranet/detail-modal/${id}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        console.log(data);
        
        const tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML = ""; // Clear previous data

        if (data.length > 0) {
          data.forEach((detail) => {
          
            // Formater la date
            let dateEtaIvato   ;
            let dateMagasin ;
            let dateStatut; 
            let numCde;
            let statrmq;
            let statut;
            let message;
            let cmdColorRmq = '';
            if(formaterDate(detail.datestatut) == '01/01/1970'){
              dateStatut = '';
            }else{
              dateStatut = formaterDate(detail.datestatut);
            }
            if(detail.Eta_ivato == ''){
              dateEtaIvato = '';
            }else{
              dateEtaIvato = formaterDate(detail.Eta_ivato)
            }
            if(detail.Eta_magasin == ''){
              dateMagasin = '';
            }else{
              dateMagasin = formaterDate(detail.Eta_magasin)
            }
            if(detail.numerocmd == null){
              numCde = '';
            }else{
              numCde = detail.numerocmd;
            }
            if(detail.statut_ctrmq == null ){
              statrmq = '';
            }else{
              statrmq = detail.statut_ctrmq;
            }
            if(detail.statut == null ){
              statut = '';
            }else{
              statut = detail.statut;
            }
            if(detail.message == null ){
              message = '';
            }else{
              message = detail.message;
            }
            //reception partiel 
             let qteSolde = parseInt(detail.qteSlode);
             let qteQte = parseInt(detail.qte);

            if(qteSolde > 0 && qteSolde != qteQte){
              cmdColorRmq = 'style="background-color: yellow;"';
            }
            let cmdColor;
            if(statut =='DISPO STOCK'){
              cmdColor = 'style="background-color: purple; color: white;"';
            }else if(statut =='Error' || statut =='Back order'){
              cmdColor = 'style="background-color: red; color: white;"';
            }

            // Affichage
            let row = `<tr>
                      <td>${detail.numor}</td> 
                      <td>${detail.intv}</td> 
                      <td ${cmdColor}>${numCde}</td> 
                      <td ${cmdColorRmq}>${statrmq}</td> 
                      <td>${detail.cst}</td> 
                      <td>${detail.ref}</td> 
                      <td>${detail.desi	}</td> 
                      <td>${parseInt(detail.qteres_or)  }</td> 
                      <td>${parseInt(detail.qteall)	}</td> 
                      <td>${parseInt(detail.qtereliquat)	}</td> 
                      <td>${parseInt(detail.qteliv)}</td> 
                      <td >${statut}</td> 
                      <td>${dateStatut}</td> 
                      <td>${dateEtaIvato}</td> 
                      <td>${dateMagasin}</td> 
                      <td>${message}</td> 
                  </tr>`;
            tableBody.innerHTML += row;
          });

          // Masquer le spinner et afficher les données
          document.getElementById("loading").style.display = "none";
          document.getElementById("dataContent").style.display = "block";
        } else {
          // Si les données sont vides, afficher un message vide
          tableBody.innerHTML =
            '<tr><td colspan="5">Aucune donnée disponible.</td></tr>';
          document.getElementById("loading").style.display = "none";
          document.getElementById("dataContent").style.display = "block";
        }
      })
      .catch((error) => {
        const tableBody = document.getElementById("commandesTableBody");
        tableBody.innerHTML =
          '<tr><td colspan="5">Could not retrieve data.</td></tr>';
        console.error("There was a problem with the fetch operation:", error);

        // Masquer le spinner même en cas d'erreur
        document.getElementById("loading").style.display = "none";
        document.getElementById("dataContent").style.display = "block";
      });
  });

  // Gestionnaire pour la fermeture du modal
  listeCommandeModal.addEventListener("hidden.bs.modal", function () {
    const tableBody = document.getElementById("commandesTableBody");
    tableBody.innerHTML = ""; // Vider le tableau
  });

  function formaterDate(daty)
  {
    const date = new Date(daty);
            return  `${date
              .getDate()
              .toString()
              .padStart(2, "0")}/${(date.getMonth() + 1)
              .toString()
              .padStart(2, "0")}/${date.getFullYear()}`;
  }
  
});
  