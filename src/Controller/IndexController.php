<?php

namespace App\Controller;

use App\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class IndexController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/", httpMethod: "GET", name: "accueil")]
    public function accueil()
    {
        echo $this->twig->render('accueil/accueil.html.twig');
    }
}
