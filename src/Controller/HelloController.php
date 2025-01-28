<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HelloController
{
    /**
     * @Route("/hello/{name}", name="hello_name", methods={"GET"})
     */
    public function sayHello(string $name = "World"): Response
    {
        return new Response("Hello, $name!");
    }
}
