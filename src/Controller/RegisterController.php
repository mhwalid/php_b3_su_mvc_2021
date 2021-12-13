<?php

namespace App\Controller;

use App\Auth\Core\UserManager;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Routing\Attribute\Route;
use DateTime;
use Doctrine\ORM\EntityManager;

class RegisterController extends AbstractController
{   
    #[Route(path: "/register", name: "register", httpMethod: "GET")]
    public function register(){
        echo $this->twig->render('Auth/Register.html.twig');
    }

    #[Route(path: "/register/create", name: "create", httpMethod: "POST")]
    public function create(EntityManager $em){

        

        $password = $userManager->cryptPassword($_POST['password']);
        $user = (new User())
            ->setUserName($_POST['UserName'])
            ->setFirstName($_POST['FirstName'])
            ->setName($_POST['Name'])
            ->setEmail($_POST['email'])
            ->setBirthDate(new DateTime())
            ->setPassword($password);


        $em->persist($user);
        $em->flush();

        // check Token in Session
        // var_dump($userManager->getUserToken()); 
        
        echo $this->twig->render('accueil/accueil.html.twig',(array)$userManager);
    }
}