<?php

namespace App\Model\dom;

use App\Model\Model;

class DomDetailModel extends Model
{
    /**
     * affiche les informations correspond au NumDom selectionner et IDDom
     * @param NumDom : Numero d'Ordre de Mission
     * @param IDDOm : ID_demande d'ordre de mission
     */
    public function getDetailDOMselect($NumDOM, $IDDom)
    {
        $SqlDetail = "SELECT Numero_Ordre_Mission, Date_Demande,
                             Sous_Type_Document, 
                             Matricule, Nom_Session_Utilisateur,  
                             Emetteur, Debiteur, Matricule, 
                             Nom, Prenom, 
                             Date_Debut, Heure_Debut, 
                             Date_Fin, Heure_Fin,
                             Nombre_Jour, Motif_Deplacement, 
                             Client,Fiche,Lieu_Intervention,
                             Vehicule_Societe, NumVehicule,
                             Devis, Indemnite_Forfaitaire, 
                             Total_Indemnite_Forfaitaire, Motif_Autres_depense_1,
                             Autres_depense_1, Motif_Autres_depense_2,
                             Autres_depense_2, Motif_Autres_depense_3,
                             Autres_depense_3,Total_Autres_Depenses, 
                             Total_General_Payer, Mode_Paiement, 
                             Piece_Jointe_1, Piece_Jointe_2,
                             idemnity_depl,
                             Doit_indemnite,
                             ID_Demande_Ordre_Mission
                     FROM Demande_ordre_mission
                     WHERE Numero_Ordre_Mission = '" . $NumDOM . "'
                     AND ID_Demande_Ordre_Mission = '" . $IDDom . "'";
        $execSqlDetail = $this->connexion->query($SqlDetail);
        $listDetail = array();
        while ($TabDetail = odbc_fetch_array($execSqlDetail)) {
            $listDetail[] = $TabDetail;
        }
        return $listDetail;
    }
}
