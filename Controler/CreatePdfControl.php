<?php

use DomModel;


class CreatePdfControl
{
    public $DomModel;
    public function tsyhaiko( 
    $Devis,
    $Prenoms,
    $AllMontant,
    $Code_servINT,
    $dateS,
    $NumDom,
    $servINT,
    $matr,
    $typMiss,

    $Nom,
    $NbJ,
    $dateD,
    $heureD,
    $dateF,
    $heureF,
    $motif,
    $Client,
    $fiche,
    $lieu,
    $vehicule,
    $numvehicul,
    $idemn,
    $totalIdemn,
    $motifdep01,
    $montdep01,
    $motifdep02,
    $montdep02,
    $motifdep03,
    $montdep03,
    $totaldep,
    $libmodepaie,
    $mode,
    $codeAg_servDB,
    $CategoriePers,
    $Site,
    $Idemn_depl,
    $MailUser,
    $idemnDoit, 
    $filename01,
    $filetemp01,
    $filename02,
    $filetemp02,
    $dateSystem,
    $usersession,
    $DateDebut,
    $DateFin,
    $modeDB,
    $valModemob,
    $LibelleCodeAg_ServDB)
    {
        $this->DomModel->genererPDF(
            $Devis,
            $Prenoms,
            $AllMontant,
            $Code_servINT,
            $dateS,
            $NumDom,
            $servINT,
            $matr,
            $typMiss,

            $Nom,
            $NbJ,
            $dateD,
            $heureD,
            $dateF,
            $heureF,
            $motif,
            $Client,
            $fiche,
            $lieu,
            $vehicule,
            $numvehicul,
            $idemn,
            $totalIdemn,
            $motifdep01,
            $montdep01,
            $motifdep02,
            $montdep02,
            $motifdep03,
            $montdep03,
            $totaldep,
            $libmodepaie,
            $mode,
            $codeAg_servDB,
            $CategoriePers,
            $Site,
            $Idemn_depl,
            $MailUser,
            $idemnDoit,
            

        );
        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
        move_uploaded_file($filetemp01, $Upload_file);
        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
        move_uploaded_file($filetemp02, $Upload_file02);
        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
        if (!empty($filename02)) {
            //echo 'fichier02';
            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
        } else {
            $this->DomModel->genererFusion1($FichierDom, $filename01);
            //echo 'echo non';
        }


        $this->DomModel->InsertDom(
            $NumDom,
            $dateSystem,
            $typMiss,

            $matr,
            $usersession,
            $codeAg_servDB,
            $DateDebut,
            $heureD,
            $DateFin,
            $heureF,
            $NbJ,
            $motif,
            $Client,
            $fiche,
            $lieu,
            $vehicule,
            $idemn,
            $totalIdemn,
            $motifdep01,
            $montdep01,
            $motifdep02,
            $montdep02,
            $motifdep03,
            $montdep03,
            $totaldep,
            $AllMontant,
            $modeDB,
            $valModemob,
            $Nom,
            $Prenoms,
            $Devis,
            $filename01,
            $filename02,
            $usersession,
            $LibelleCodeAg_ServDB,
            $numvehicul,
            $idemnDoit,
            $CategoriePers,
            $Site,
            $Idemn_depl
        );
    }
}