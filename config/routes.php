<?php

return [

    [
        'method' => [''],
        'path' => ''
    ],
    [
        'method' => 'GET',
        'path' => '/Hffintranet/',
        'controller' => 'App\\Controller\\badm\\BadmController::formBadm'
    ],
    [
        'method' => 'POST',
        'path' => '/list',
        'controller' => 'App\\Controller\\BadmController::formCompleBadm'
    ],
    [
        'method' => ['GET', 'POST'],
        'path' => '/add',
        'controller' => 'Tutorial\\Fastroute\\Controller\\LinksController:add'
    ],
];
