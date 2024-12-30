<?php

namespace App\Service\historiqueOperation;

interface HistoriqueOperationInterface
{
    /** 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param int $typeDocumentId id du type de document 
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrer(string $numeroDocument, int $typeOperationId, int $typeDocumentId, string $statutOperation, ?string $libelleOperation = null): void;
}
