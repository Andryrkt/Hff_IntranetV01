<?php

return [
    [
        'method' => 'GET',
        'path' => '/Hffintranet/',
        'controller' => 'App\\Controller\\BadmController:formBadm'
    ],
    [
        'method' => 'POST',
        'path' => '/list',
        'controller' => 'App\\Controller\\BadmController:formCompleBadm'
    ],
    [
        'method' => ['GET', 'POST'],
        'path' => '/add',
        'controller' => 'Tutorial\\Fastroute\\Controller\\LinksController:add'
    ],
];
