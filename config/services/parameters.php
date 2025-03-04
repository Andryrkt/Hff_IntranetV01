<?php

return function (\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) {
    // Chargement des paramètres globaux
    $containerBuilder->setParameter('base_url', $_ENV['BASE_URL'] ?? '/Hffintranet');
    // $containerBuilder->setParameter('admin_email', $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com');
    // $containerBuilder->setParameter('app_name', 'HFF Intranet');
    // $containerBuilder->setParameter('pagination_limit', 10); // Nombre d'éléments par page
};

