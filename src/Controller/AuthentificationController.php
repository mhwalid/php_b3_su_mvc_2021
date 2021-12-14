<?php
namespace App\Controller;

use App\Auth\Core\UserManager;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Routing\Attribute\Route;
use Doctrine\ORM\EntityManager;

class AuthentificationController extends AbstractController
{   
    #[Route(path: "/login", name: "login", httpMethod: "GET")]
    public function login(){

        echo $this->twig->render('Auth/login.html.twig');
    }

    #[Route(path: "/loginsend", name: "Loginsend", httpMethod: "POST")]
    public function Loginsend(EntityManager $em){
        $userManager = new UserManager();
      
        $user= $em->getRepository(User::class)->findOneBy([
            'email'=>$_POST['email']
        ]);

        if ($userManager->isPasswordValid($user, $_POST['password'])) {
            //add to sesstion
            $userManager->login($user);
        echo $this->twig->render('accueil/accueil.html.twig' ,(array)$user );
        }else {
        
        echo $this->twig->render('accueil/accueil.html.twig');
        }
        
    }

    #[Route(path: "/logout", name: "logout", httpMethod: "POST")]
    public function logout(UserManager $userManager){
        // $userManager = new UserManager();
        $userManager->logout();
        echo $this->twig->render('index/contact.html.twig');
    }

    
}
