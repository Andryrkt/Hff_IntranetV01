<?php

namespace App\Controller;

class MainController extends Controller
{
    public function index()
    {
        $this->twig->display('main/index.html.twig');
    }
}
