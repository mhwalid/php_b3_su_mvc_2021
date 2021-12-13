<?php
namespace App\Controller;

use App\Auth\Core\UserManager;
use App\Controller\AbstractController;
use App\Routing\Attribute\Route;

class AuthentificationController extends AbstractController
{   
    #[Route(path: "/login", name: "login", httpMethod: "GET")]
    public function login(){

        echo $this->twig->render('Auth/login.html.twig');
    }

    #[Route(path: "/loginsend", name: "Loginsend", httpMethod: "POST")]
    public function Loginsend(){
        $post[]=$_POST;
        echo $this->twig->render('index/contact.html.twig',$post);
    }

    #[Route(path: "/logout", name: "logout", httpMethod: "POST")]
    public function logout(UserManager $userManager){
        // $userManager = new UserManager();
        $userManager->logout();
        echo $this->twig->render('index/contact.html.twig');
    }

    
}