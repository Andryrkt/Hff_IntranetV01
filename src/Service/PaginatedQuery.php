<?php

namespace App\Service;

use App\Model\Connexion;
use Pagerfanta\Adapter\AdapterInterface;

class PaginatedQuery  implements AdapterInterface
{

    private Connexion $con;
    private string $query;
    private string $countQuery;
    /**
     * @param Connexion $con
     * @param string $query Requête permettant de récupérer X résultat
     * @param string $countQuery Requête permettant de compter le nombre de résultat total
     */
    public function __construct(Connexion $con, string $query, string  $countQuery)
    {
        $this->con = $con;
        $this->query = $query;
        $this->countQuery = $countQuery;
    }

    public function getNbresults()
    {
        return $this->con->query($this->countQuery);
    }

    public function getSlice()
    {
    }
}
