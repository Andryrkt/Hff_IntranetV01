<?php

use App\Twig\DeleteWordExtension;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

return function (ContainerBuilder $containerBuilder) {
    $baseUrl = '/' . trim($_ENV['BASE_URL'], '/');

    //1) AppExtension
    $containerBuilder->register('app.app_extension', \App\Twig\AppExtension::class)
    ->setArguments([
        new Reference('http_foundation.session'),
        new Reference('request_stack'),
        new Reference('entity_manager'),
    ]);

    //2) DeletewordExtension
    $containerBuilder->register('app.delete_word_extension', DeleteWordExtension::class);

    //3) AssetExtension
    // 3.1) On déclare un paramètre pour le chemin public
    $containerBuilder->setParameter('asset.public_path', $baseUrl.'/public');

    // 3.2) Enregistrer la EmptyVersionStrategy
    $containerBuilder->register('asset.version_strategy.empty', EmptyVersionStrategy::class)
    ->setPublic(false);

    // 3.3) Enregistrer le PathPackage
    //    PathPackage::__construct(string $basePath, VersionStrategyInterface $versionStrategy, ContextInterface $context = null)
    $containerBuilder->register('asset.path_package', PathPackage::class)
    ->setArguments([
        '%asset.public_path%',                                // le chemin
        new Reference('asset.version_strategy.empty'),        // la stratégie
        // Optionnel : un troisième argument pour le contexte (si nécessaire)
    ])
    ->setPublic(false);

    // 3.4) Enregistrer "Packages"
    //    Packages::__construct(PackageInterface $defaultPackage, array $packages = [])
    //    On utilise le PathPackage comme "package" principal ou par défaut
    $containerBuilder->register('asset.packages', Packages::class)
    ->setArguments([
        new Reference('asset.path_package'), // defaultPackage
        [],                                   // ou un tableau de packages nommés
    ])
    ->setPublic(true);
    //3.5)
    $containerBuilder->register('twig.extension.asset', \Symfony\Bridge\Twig\Extension\AssetExtension::class)
    ->setArguments([
        new Reference('asset.packages'),
    ])
    ->setPublic(false);
};
