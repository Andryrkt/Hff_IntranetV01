<?php

namespace App\Controller\Traits;

trait DomAjaxTrait
{
    
    /**
     * selection catgégorie dans l'ajax 
     * @Route("/selectCateg")
     */
    public function selectCatg()
    {
        $this->SessionStart();


        $valeurSelect = $_POST['typeMission'];
        $codeAg = $_POST['CodeAg'];
        if ($codeAg !== '50') {
            $AgenceCode = 'STD';
        } else {
            $AgenceCode = '50';
        }
        $InforCatge = $this->DomModel->CategPers($valeurSelect, $AgenceCode);
        $response = "<label for='CategPers' class='label-form' id='labCategPers'> Catégorie:</label>";
        $response .= "<select id='categPers' class='form-select' name='categPers'>";
        foreach ($InforCatge as $info) {
            $categ = $info['Catg'];
            $info = iconv('Windows-1252', 'UTF-8', $categ);

            $response .= "<option value='$info'>$info</option>";
        }
        $response .= "</select>";

        echo $response;
    }

    /**
     * selection categorie Rental 
     * @Route("/selectCatgeRental", name="")
     */
    public function selectCategRental()
    {
        $this->SessionStart();

        $ValCodeserv = $_POST['CodeRental'];
        $CatgeRental = $this->DomModel->catgeRental($ValCodeserv);
        $RentalCatg = "<label for='CategRental' class='label-form' id='labCategRental'> Catégorie:</label>";
        $RentalCatg .= "<select id='categRental' class='form-select' name='categRental' >";
        foreach ($CatgeRental as $Catg) {
            $categ = $Catg['Catg'];
            $Catge50 = iconv('Windows-1252', 'UTF-8', $categ);

            $RentalCatg .= "<option value='$Catge50'>$Catge50</option>";
        }
        $RentalCatg .= "</select>";

        echo $RentalCatg;
    }


    /**
     * selection des sites (regions) correspondant aux catégorie selectionner 
     * @Route("/selectIdem", name="dom_selectSiteRental")
     */
    public function selectSiteRental()
    {
        $this->SessionStart();

        $CatgPersSelect = $_POST['CategPers'];
        $TypeMiss = $_POST['TypeMiss'];

        $MutSiteRental = $this->DomModel->SelectSite($TypeMiss, $CatgPersSelect);

        $response1 = "<label for='SiteRental' class='label-form' id='labSiteRental'> Site:</label>";
        $response1 .= "<select id='SiteRental' class='form-select' name='SiteRental'>";
        foreach ($MutSiteRental as $Site) {
            $Site = $Site['Destination'];
            $info = iconv('Windows-1252', 'UTF-8', $Site);

            $response1 .= "<option value='$info'>$info</option>";
        }
        $response1 .= "</select>";

        echo $response1;
    }

    /**
     * afficher Prix selon selection Sites 
     * @Route("/selectPrixRental", name="selectPrixRental")
     */
    public function SelectPrixRental()
    {
        $this->SessionStart();

        $typeMiss = $_POST['typeMiss'];
        $categ = $_POST['categ'];
        $sitesel = $_POST['siteselect'];
        $codeserv = $_POST['codeser'];
        $count = $this->DomModel->SiRentalCatg($categ);
        $nb_count = intval($count);

        if ($nb_count === 0) {
            $agserv = 'STD';
            $Prix = $this->DomModel->SelectMUTPrixRental($typeMiss, $categ, $sitesel, $agserv);
            //echo $agserv;
            echo  $Prix[0]['Montant_idemnite'];

            // print_r($Prix);
        } else {
            $agserv = '50';
            $Prix = $this->DomModel->SelectMUTPrixRental($typeMiss, $categ, $sitesel, $agserv);
            //echo $agserv;
            echo  $Prix[0]['Montant_idemnite'];
        }
    }

}