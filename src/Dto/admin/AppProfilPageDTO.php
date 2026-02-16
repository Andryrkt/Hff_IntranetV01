<?php

namespace App\Dto\admin;

use App\Entity\admin\historisation\pageConsultation\PageHff;

class AppProfilPageDTO
{
    public ?PageHff $page = null;
    public bool $peutVoir = false;
    public bool $peutAjouter = false;
    public bool $peutModifier = false;
    public bool $peutSupprimer = false;
}
