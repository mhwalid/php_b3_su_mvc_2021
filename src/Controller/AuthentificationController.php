<?php
namespace App\Controller;


use App\Auth\UserInterface;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Routing\Attribute\Route;
use Doctrine\ORM\EntityManager;

class AuthentificationController extends AbstractController
{   
    #[Route(path: "/login", httpMethod: "GET", name: "login")]
    public function login(){
        echo $this->twig->render('Auth/login.html.twig');
    }

    #[Route(path: "/loginsend", httpMethod: "POST", name: "Loginsend")]
    public function loginSend(EntityManager $em){
        $user= $em->getRepository(User::class)->findOneBy([
            'email'=>$_POST['email']
        ]);

        if ($this->_isPasswordValid($user, $_POST['password'])) {
            echo $this->twig->render('accueil/accueil.html.twig' ,[
                'user' => $user
            ]);
        } else {
            echo $this->twig->render('accueil/accueil.html.twig');
        }
    }

    private function _isPasswordValid(User $user, string $plainPassword): bool
    {
        return password_verify($plainPassword, $user->getPassword());
    }
}