<?php

namespace App\Controller;

use App\Controller\AbstractController;
use App\Routing\Attribute\Route;

class RegisterController extends AbstractController
{   
    #[Route(path: "/register", name: "register", httpMethod: "GET")]
    public function register(){
        echo $this->twig->render('Auth/Register.html.twig');
    }


}