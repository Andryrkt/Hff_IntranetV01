<?php

namespace App\Service\historiqueOperation;

interface HistoriqueOperationInterface
{
    /** 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param bool $succes statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrer(string $numeroDocument, int $typeOperationId, bool $succes, ?string $libelleOperation = null): void;
}
