<?php

use Symfony\Component\Form\Forms;
use App\Service\AccessControlService;
use Symfony\Component\Form\FormRenderer;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension as CsrfCsrfExtension;

return function (ContainerBuilder $containerBuilder) {
    // 1.1) Service “twig.form_renderer_engine”
//      qui reçoit un tableau de thèmes + le service `twig`
$containerBuilder->register('twig.form_renderer_engine', TwigRendererEngine::class)
->setArguments([
    [ 'bootstrap_5_layout.html.twig' ],  // ou votre autre thème
    new Reference('twig'),               // le service twig existant
])
->setPublic(false);

// 1.2) Service “twig.form_renderer”
//      qui construit le FormRenderer basé sur l’engine ci-dessus
$containerBuilder->register('twig.form_renderer', FormRenderer::class)
->setArguments([
    new Reference('twig.form_renderer_engine'),
    // Si vous utilisez un CsrfTokenManager pour les formulaires, 
    // vous pouvez l'ajouter ici en second argument.
])
->setPublic(false);

// 1.3) Service “twig.form_runtime_loader”
//      un FactoryRuntimeLoader qui associe la classe FormRenderer::class
//      à l’instance twig.form_renderer
$containerBuilder->register('twig.form_runtime_loader', FactoryRuntimeLoader::class)
->setArguments([[
    FormRenderer::class => new Reference('twig.form_renderer')
]])
->setPublic(false);

// 1.4) Ajouter ce runtime loader à Twig via un appel de méthode
$containerBuilder->getDefinition('twig') // supposez que "twig" soit déjà défini
->addMethodCall('addRuntimeLoader', [
    new Reference('twig.form_runtime_loader')
]);

/**
* TWIG FORM FACTORY
*/
// 1) Enregistrement
// 1-2) Enregistrement du service "csrf_token_manager"
$containerBuilder->register('csrf_token_manager', CsrfTokenManager::class)
->setPublic(true);
//1-3) Enregistrement du service "validator"
$containerBuilder->register('validator', \Symfony\Component\Validator\Validator\ValidatorInterface::class)
->setPublic(true);

// a) Extension CSRF
$containerBuilder->register('form.extension.csrf', CsrfCsrfExtension::class)
->setArguments([
    new Reference('csrf_token_manager')  // Supposez que vous ayez un service `csrf_token_manager`
])
->setPublic(false);

// b) Extension Validator
$containerBuilder->register('form.extension.validator', ValidatorExtension::class)
->setArguments([
    new Reference('validator') // Supposez que vous ayez un service `validator`
])
->setPublic(false);

// c) Extension Core
$containerBuilder->register('form.extension.core', CoreExtension::class)
->setPublic(false);

// d) Extension HttpFoundation
$containerBuilder->register('form.extension.http_foundation', HttpFoundationExtension::class)
->setPublic(false);

// e) Extension Doctrine
$containerBuilder->register('form.extension.doctrine_orm', DoctrineOrmExtension::class)
->setArguments([
    new Reference('manager_registry') // Supposez que vous ayez `manager_registry`
])
->setPublic(false);


// Service "form.factory_builder"
$containerBuilder->register('form.factory_builder', FormFactoryBuilderInterface::class)
// On utilise la factory statique "Forms::createFormFactoryBuilder()"
->setFactory([Forms::class, 'createFormFactoryBuilder'])
// On ajoute les extensions
->addMethodCall('addExtension', [new Reference('form.extension.csrf')])
->addMethodCall('addExtension', [new Reference('form.extension.validator')])
->addMethodCall('addExtension', [new Reference('form.extension.core')])
->addMethodCall('addExtension', [new Reference('form.extension.http_foundation')])
->addMethodCall('addExtension', [new Reference('form.extension.doctrine_orm')])
->setPublic(false);

// Service "form.factory"
$containerBuilder->register('form.factory', FormFactoryInterface::class)
->setFactory([new Reference('form.factory_builder'), 'getFormFactory'])
->setPublic(true);

/**
* Access control service
*/
$containerBuilder->register('app.access_control_service', AccessControlService::class)
->setArguments([
    // 1) L’EntityManager
    new Reference('entity_manager'), 

    // 2) SessionManagerService
    new Reference('app.session_manager'),
])
->setPublic(true); 
};