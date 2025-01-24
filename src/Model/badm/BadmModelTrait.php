<?php

namespace App\Model\badm;

trait BadmModelTrait
{
    /**
     * sql server
     */
    public function recupTypeMouvement(): array
    {
        $statement  = "SELECT Description FROM Type_Mouvement";
        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }

    public function recupIdtypeMouvemnet($codeMouvemnet)
    {
        $statement  = "SELECT ID_Type_Mouvement  FROM Type_Mouvement WHERE Description = '" . $codeMouvemnet . "'";
        $execTypeDoc = $this->connexion->query($statement);

        $donner = odbc_fetch_array($execTypeDoc);
        return $donner;
    }
}
