<?php

namespace App\Api\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocument;
use Symfony\Component\Routing\Annotation\Route;

class operationDocumentApi extends Controller
{
    /**
     * @Route("/api/operation-document-fetch-all", name="operation_document_fetch_all")
     *
     * @return void
     */
    public function allOperationDocument()
    {
        /** 
         * @var HistoriqueOperationDocument[] $operationDocuments tableau d'entité 
         */
        $operationDocuments = self::$em->getRepository(HistoriqueOperationDocument::class)->findBy([], ['id' => 'DESC']);

        $results = [];
        foreach ($operationDocuments as $operationDocument) {
            $results[] = [
                'numeroDocument' => $operationDocument->getNumeroDocument(),
                'date'           => $operationDocument->getDateOperation()->format('d-m-Y') . ' ' . strstr($operationDocument->getHeureOperation(), '.', true), // strstr retourne la première occurence de la chaîne avant '.'
                'username'       => $operationDocument->getUtilisateur(),
                'operationType'  => $operationDocument->getIdTypeOperation()->getTypeOperation(),
                'documentType'   => $operationDocument->getIdTypeDocument()->getLibelleDocument(),
                'statut'         => $operationDocument->getStatutOperation(),
                'libelle'        => $operationDocument->getLibelleOperation(),
            ];
        }

        header("Content-type:application/json");

        echo json_encode($results);
    }
}
