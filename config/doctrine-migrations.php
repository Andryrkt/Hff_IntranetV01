<?php

// config/doctrine-migrations.php
return [
    'name' => 'My Project Migrations',
    'migrations_namespace' => 'App\Migrations',
    'table_name' => 'doctrine_migration_versions',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'all_or_nothing' => true,
    'check_database_platform' => true,
];
