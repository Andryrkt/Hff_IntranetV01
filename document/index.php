<?php
$api_url = "https://hffc.docuware.cloud/DocuWare/Platform/FileCabinets/572ed9b0-37af-482f-803b-e6fe259dbf1f/Documents/154/FileDownload";

// URL de l'endpoint de l'API DocuWare que vous souhaitez appeler
// $api_url = 'https://hffc.docuware.cloud/DocuWare/Search/FileCabinets/32554638-bec0-44e8-a52e-07af489d7614/Documents';

if ($api_url=="") {
	die("Impossible de télecharger le document");
}


// https://hffc.docuware.cloud/DocuWare/FileCabinets/faf31b9b-ddba-44b8-8be2-2074b17cc64d/Documents

// Token d'accès
$access_token = getTokenDocuware();

// Configuration de la requête cURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $api_url);
curl_setopt($curl, CURLOPT_MAXREDIRS, 1000);
curl_setopt($curl, CURLOPT_TIMEOUT, 360);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true); // Inclure les en-têtes dans la réponse
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
   'Authorization: Bearer ' . $access_token,
    'Accept: application/docx', // Spécifier le format attendu si supporté par l'API
));

// Envoi de la requête
$response = curl_exec($curl);

// Vérifier les erreurs cURL
if (curl_errno($curl)) {
    die('Erreur cURL : ' . curl_error($curl));
}

// Séparer les en-têtes du corps de la réponse
$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
//https://login-emea.docuware.cloud/5adf2517-2f77-4e19-8b42-9c3da43af7be/Account/Logout?logoutId=CfDJ8Bn0MJGXdwJCttYBBrZvpPRbcDUTNlVUNmo3ia-HmJPhYjp6qUcekrXZREXPoH8wL3BtoybKBZ4QIcT6e-9Lad1EOE8kkmIU9jVhy3rHuTy3L6pb1OpJW21rdkU5fuIDsHrt8Wa5CNQS7fR-NRCpxYCa_LXzgahazWqghpaK_hnAi6vN5nYTU6nkmtIXFeoT9KET9_6cPI5Pvu5tMOYoY0KM-MPOncuPALY7ABoiT4rAF1m-CgnRt4T3pqHLOxi0aOmcuGkM8kViiDWDRiJZW01eeCiQwe1w81-mq5vwg1FXJr_UUXUL-aopcNjZE9WwcttarfgS-prO4JfNF21TxIM6LCamzXwR3Pt096H62uZLB7sdVhaIOet8D_Dxukw-v4tDSJU4CUe5tcqC84pBB6DrxX-puD-JcbpK2i7K7h0EE7kafPFDgwoXyk861aRMXArxD07H0jd0DK7rmLNag44s8k6_1tJX6mTEhGmR7hMqKNDj7_McZPoQ9HFRerzBQUlEcPkgvLM9_oqd9YXvuPd-E4mIcemtwJB1id5u0UenH8HI1NYSVJlmC05tI_68blNfkUWIoaSTcCcYUFnSfgFdtwALH_7eXJyRswxJFzaR
// Afficher les en-têtes et le corps
// echo "En-têtes :\n";
// echo $headers;

// echo "\n\nCorps :\n";
// echo $body;

// Sauvegarder le fichier téléchargé si le corps est un contenu valide
if ($body) {
    $outputFile = "PCS003-HFF-PCD-015 Validation BADM Comptabilité".date('YmdHis').".docx";
    file_put_contents($outputFile, $body);
    // echo "\nFichier téléchargé avec succès : $outputFile";
} else {
    die('Erreur : Impossible de télécharger le document rattaché.');
}

// Fermeture de la session cURL
curl_close($curl);
deconnexion($access_token);


curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

function getTokenDocuware(){
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://login-emea.docuware.cloud/5adf2517-2f77-4e19-8b42-9c3da43af7be/connect/token',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
      CURLOPT_SSL_VERIFYPEER, false,
      CURLOPT_SSL_VERIFYHOST, 0,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => 'grant_type=password&scope=docuware.platform&client_id=docuware.platform.net.client&username=admin&password=261dwhffadmin',
	  CURLOPT_HTTPHEADER => array(
	    'Accept: application/json',
	    'Content-Type: application/x-www-form-urlencoded'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);

	$oauth_response = json_decode($response, true);

    $access_token = $oauth_response['access_token'];

    return $access_token;
}

// le nom de 'utuilisateur et le mot de passe devrait être envoyué en paramètre
// la liste des tiroirs aussi
// si consultation d'un document spécifique qui est connu, on doit le passer en paramètre.

function writeXmlFile($response){
	if ($response !== false) {
    // Nom du fichier XML dans lequel enregistrer la réponse
	    $xmlFile = "reponse.xml";

	    // chemin du fichier xml généré
	    // Récupère le chemin absolu du fichier XML
    	$filePath = realpath($xmlFile);

	    // Ouvre le fichier XML en écriture
	    $xmlHandle = fopen($xmlFile, "w");

	    // Vérifie si l'ouverture du fichier a réussi
	    if ($xmlHandle !== false) {
	        // Écrit la réponse dans le fichier XML
	        fwrite($xmlHandle, $response . "\n"); // PHP_EOL permet d'ecrire les contenus avec retour à la ligne
	        fclose($xmlHandle);
	        echo "OK:".$filePath; 


			// chargement du fichier xml
			$xml = simplexml_load_file($filePath);
			$array_dw = [];

			// // lecture du fichier xml
			// foreach ($xml->Items as $Items) {
			// 	foreach ($Items->Item as $Item) {
			// 		// création de la classe ;
			// 		$dw = new Document;

			// 		foreach ($Item->Fields as $Fields) {
			// 			foreach ($Fields as $Field => $value) {
			// 				echo $value["FieldName"]." = ".$value->children()."</br>";
			// 				switch ($value["FieldName"]) {
			// 					case 'NUMERO':
			// 						$dw->setNumero($value->children());
			// 						$dw->setTiroir($value->children());
									
			// 						break;
								
			// 					default:
			// 						# code...
			// 						break;
			// 				}
			// 			}

			// 		}
			// 		//echo "</br></br>".$Item["Title"]."</br>";
			// 		die();
			// 	}
			// }


	    } else {
	        echo "Impossible d'ouvrir le fichier XML pour écriture.";
	    }
	} else {
	    echo "Échec de la requête cURL.";
	}
}
Class Document {
	private $Numero;
	private $Tiroir;
	private $Status;

	// getter
	public function getNumero(){
		return $this->Numero;
	}
	public function getTiroir(){
		return $this->Tiroir;
	}
	public function getStatus(){
		return $this->Status;
	}


	// setter
	public function setNumero($numero){
		$this->Numero = $numero;
	}
	public function setTiroir($tiroir){
		$this->Tiroir = $tiroir;
	}
	public function setStatus($status){
		$this->Status = $status;
	}
}

function deconnexion($token){
	// Appel de la requête cURL pour la déconnexion
$logoutUrl = 'https://login-emea.docuware.cloud/5adf2517-2f77-4e19-8b42-9c3da43af7be/Account/Logout?'.$token;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $logoutUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPGET, true); // Utiliser GET pour la déconnexion

$response = curl_exec($curl);

// Vérifier les erreurs cURL
if (curl_errno($curl)) {
    die('Erreur cURL lors de la déconnexion : ' . curl_error($curl));
}

curl_close($curl);

echo "\nDéconnexion effectuée avec succès.";
}
?>