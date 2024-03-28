<?php

namespace App\Controller;

class MainController extends Controller
{
    public function index()
    {
        $anarana = 'Hasina';
        $this->twig->display('main/index.html.twig', ['anarana' => $anarana]);
    }
}
