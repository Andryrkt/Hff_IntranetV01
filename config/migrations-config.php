<?php

// migrations-config.php

return [
    'migrations_paths' => [
        'Migrations' => dirname(__DIR__) . '/migrations',
    ],
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'version_column_length' => 191,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],
    'all_or_nothing' => true,
    'check_database_platform' => true,
];

