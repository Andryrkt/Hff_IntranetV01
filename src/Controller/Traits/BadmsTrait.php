<?php

namespace App\Controller\Traits;

trait BadmsTrait
{
    private function alertRedirection(string $message, string $chemin = "/Hffintranet/formBadm")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
    }
}