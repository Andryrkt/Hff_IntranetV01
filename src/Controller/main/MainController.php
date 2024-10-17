<?php
namespace App\Controller\main;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class MainController extends Controller
{ 
    /**
     * @Route("/magasin_fond", name="magasin_menu")
     */
    public function  magasinMenu(){
        self::$twig->display('main/Magasin.html.twig', []);
    }
    /**
     *  @Route("/menu", name="menu_accueil")
     */
    public function accueil(){
        self::$twig->display('main/home.html.twig', []);
    }
}