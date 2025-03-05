26/11/2024
1/ le numero or 16411845 s'affiche dans la liste des o complet à livrer alors qu'ils sont déjà tous livré (Okey)
2/ idem pour le numero or 16409965 dans le base de donner test (Okey)
3/ le numero or 16412766 est envoyer en ors soumis à validation, le problème est que sur la page de garde du pdf générer les montants sont null et le compentatire et le numero d'intervention sont vide (okey)
=> le stiv_pos= 'CP' donc ce numero or ne peut pas être envoyer en soumission

27/11/2024
1/ rado peut crée une dit alors qu'il n'a pas l'autorisation d'accéder au page de création dit (okey)
2/ le numero or 51302514 est envoyer en ors soumis à validation, le problème est que sur la page de garde du pdf générer les montants sont null et le compentatire et le numero d'intervention sont vide
=> stiv_pos = 'EC' à verifier pourquoi le donner ne s'affiche pas sur le pdf
numeroDit : DIT24119994
numeroOr: 51302404

private function conditionPiece(string $indexCriteria, array $criteria): ?string
    {   
        if (!empty($criteria[$indexCriteria])) {
if($criteria[$indexCriteria] === "PIECES MAGASIN"){
$piece = " AND slor_constp in ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','LUB','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO')";
            } else if($criteria[$indexCriteria] === "LUB") {
$piece = " AND slor_constp in ('LUB', 'JOV')";
            } else if($criteria[$indexCriteria] === "ACHATS LOCAUX") {
$piece = " AND (slor_constp in ('ALI','BOI','CAR','CEN','FAT','FBU','HAB','INF','MIN','OUT','ZST') ";
            }else if($criteria[$indexCriteria] === "TOUTS PIECES") {
$piece = null;
}
} else {
$piece = " AND slor_constp in ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','LUB','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO')";
}

        return $piece;
    }

06/02/2025
MIGRATION DES DIT INTERNE
manque de fichier "Messagerie Henri Fraise et Fils - Fwd\_ Devis n° 4DE011344.pdf" pour deuxième pièce joint dans le dit DIT25010315 (qui n'est pas encore générer dans doxcuware)
