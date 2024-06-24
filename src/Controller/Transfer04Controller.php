<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class Transfer04Controller extends Controller
{
    /**
     * @Route("/transfer")
     *
     * @return void
     */
    public function transfer()
    {
        $data = $this->transfer04->transef();
        //dd($data);

        $allTransfersToBd = [];
        foreach ($data as $key => $value) {
            $transferToBd = [];
           $transferToBd["numero_demande_dit"] = $value["NumeroDemandeIntervention"];
            $transferToBd["agence_service_emmeteur"] = $value["IDAgence"] .'-'. $value["IDService"];
            $transferToBd["utilisateur_demandeur"] = $value["UtilisateurDemandeur"];
            $transferToBd["ID_Materiel"] = $value["NumeroMateriel"];
            $transferToBd["objet_demande"] = $value["ObjetDemande"];
            $transferToBd["piece_joint1"] = $value["FichierJoint1"];
            $transferToBd["piece_joint2"] = $value["FichierJoint2"];
            $transferToBd["piece_joint"] = $value["FichierJoint3"];
            $transferToBd["observations"] = $value["Observations"];
            $transferToBd["detail_demande"] = $value["DetailDemande"];
            $transferToBd["date_demande"] = $value["DateDemande"];
            $transferToBd["heure_demande"] = $this->convertSecondsToHoursAndMinutes($value["HeureDemande"]);
            $transferToBd["id_statut_demande"] = 1;
            $transferToBd["date_validation"] = $value["DateValidation"];
            $transferToBd["heure_validation"] = $this->convertSecondsToHoursAndMinutes($value["HeureValidation"]);
            $transferToBd["date_cloture"] = $value["DateCloture"];
            $transferToBd["heure_cloture"] = $this->convertSecondsToHoursAndMinutes($value["HeureCloture"]);
            $transferToBd["internet_externe"] = $value["InterneExterne"];
            $transferToBd["numero_client"] = $value["NumeroClient"];
            $transferToBd["nom_client"] = $value["LibelleClient"];
            $transferToBd["agence_service_debiteur"] = $value["IDAgenceDebiteur"] . '-' . $value["IDServiceDebiteur"];
            $transferToBd["KM_machine"] = $value["KilometrageMachine"];
            $transferToBd["Heure_machine"] = $value["HeureMachine"];
            $transferToBd["categorie_demande"] = $value["IDCategorieATEAPP"];
            $transferToBd["demande_devis"] = $value["DemandeDevis"];
            $transferToBd["date_fin_souhaite"] = $value["DateFinSouhaitee"];
            $transferToBd["numero_oR"] = $value["NumeroOR"];
            $transferToBd["date_or"] = $value["DateOR"];
            $transferToBd["observation_direction_technique"] = $value["ObservationDirectionTechinque"];
            $transferToBd["observation_devis"] = $value["ObservationDevis"];
            $transferToBd["numero_devis_rattache"] = $value["NumeroDevisRattache"];
            $transferToBd["date_devis_rattache"] = $value["DateDevisRattache"];
            $transferToBd["date_soumission_devis"] = $value["DateSoumissionDevis"];
            $transferToBd["devis_valide"] = $value["DevisValide"];
            $transferToBd["date_validation_devis"] = $value["DateValidationDevis"];
            $transferToBd["id_service_intervenant"] = $value["IDServiceIntervenant"];
            $transferToBd["date_devis_fin_probable"] = $value["DateDevisFinProbable"];
            $transferToBd["date_fin_estimation_travaux"] = $value["DateFinEstimationTravaux"];
            $transferToBd["code_section"] = $value["CodeSection"];
            $transferToBd["mas_ate"] = $value["MASATE"];
            $transferToBd["code_ate"] = $value["codeate"];
            $allTransfersToBd[] = $transferToBd;
        }
       

        foreach ($allTransfersToBd as $key => $value) {
            # code...
            $this->transfer04->insert('demande_intervention', $value);
        }

        
        /*$transferToBd = [
         
        
             "codeSociete" => "",
           
            
             "" => "",
             "" => "",
             "" => "",
              => "",
             "" => "",
             "numeroTel" => "",
           
            "heureOR" => "",
            "datePrevueTravaux" => "",
            "" => "",
            "idNiveauUrgence" => "",
         
            "livraisonPartiel" => "",
 
            "mailDemandeur" => "",
           
         
            
       
            "idStatutDemande" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "x" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "secteur" => "",
            "utilisateurIntervenan" => ""
        ];*/
    }


    /**
 * Convertit un nombre de secondes en format h:min.
 *
 * @param int $seconds Le nombre de secondes à convertir.
 * @return string Une chaîne formatée en h:min.
 */
private function convertSecondsToHoursAndMinutes($seconds) {
    if($seconds !== null ) {

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return sprintf('%02d:%02d', $hours, $minutes);
    } else {
        return null;
    }
}
}