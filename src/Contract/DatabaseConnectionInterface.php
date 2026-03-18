<?php

namespace App\Contract;

interface DatabaseConnectionInterface
{
    public function connect();
    public function executeQuery(string $query);
    public function fetchResults($result);
    public function close();
}
