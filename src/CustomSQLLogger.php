<?php


// src/CustomSQLLogger.php

namespace App;

use Doctrine\DBAL\Logging\SQLLogger;

class CustomSQLLogger implements SQLLogger
{
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        echo sprintf("SQL Query: %s\n", $sql);
        if ($params) {
            echo sprintf("Parameters: %s\n", json_encode($params));
        }
    }

    public function stopQuery()
    {
        // You can add logic here if you want to do something when a query stops.
    }
}
