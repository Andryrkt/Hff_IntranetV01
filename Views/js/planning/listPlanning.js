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
  
  